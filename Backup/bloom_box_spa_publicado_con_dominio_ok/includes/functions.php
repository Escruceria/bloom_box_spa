<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// --- Sesión global segura (ruta al subdirectorio del proyecto)
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'path'     => '/',        // <- raíz del dominio (válido para todo el sitio)
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $secure,
    ]);
    session_start();
}

/* ============================
   Utilidades generales
   ============================ */

function esc(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* --- CSRF --- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="'.esc(csrf_token()).'">';
}
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
        if (!$ok) {
            http_response_code(419);
            exit('Token CSRF inválido');
        }
    }
}

/* --- Rate limit simple por sesión/ip/acción --- */
function rate_limit(string $key, int $limit, int $windowSeconds): void {
    $bucketKey = 'rl_' . $key;
    $now = time();
    $_SESSION[$bucketKey] = array_filter($_SESSION[$bucketKey] ?? [], fn($t) => $t > $now - $windowSeconds);
    if (count($_SESSION[$bucketKey]) >= $limit) {
        http_response_code(429);
        exit('Demasiadas solicitudes, intenta más tarde.');
    }
    $_SESSION[$bucketKey][] = $now;
}

/* --- Respuesta JSON --- */
function json_response(array $data, int $status=200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/* ============================
   Autenticación / Sesiones
   ============================ */

function login_user(string $login, bool $isAdmin): void {
    session_regenerate_id(true);
    $_SESSION['user'] = ['login'=>$login, 'is_admin'=>$isAdmin];
}
function logout_user(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}
function require_admin(): void {
    $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
              || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

    if (empty($_SESSION['user']['is_admin'])) {
        if ($isAjax) {
            http_response_code(403);
            exit('Acceso restringido.');
        }
        // Redirige al login del admin
        header('Location: login.php');   // ✅ relativo al directorio /admin
        exit;
    }
}

/* =============================================
   Dominio: Participantes / Premios / Ganadores
   (Compatibles con tu dump bloom_box_spa.sql)
   ========================================== */

/**
 * Verifica si existe participante por email o teléfono.
 * Devuelve array con id si existe; false si no.
 */
function participanteExiste(string $email, string $telefono) {
    $sql = "SELECT id, nombre, email, telefono
            FROM participantes
            WHERE email = :e OR telefono = :t
            LIMIT 1";
    $st = pdo()->prepare($sql);
    $st->execute([':e'=>$email, ':t'=>$telefono]);
    $row = $st->fetch();
    return $row ?: false;
}

/** Inserta participante y retorna su ID */
function registrarParticipante(string $nombre, string $email, string $telefono) {
    $sql = "INSERT INTO participantes (nombre, telefono, email, fecha_registro, ganador, canal_registro, ip_registro)
            VALUES (:n, :t, :e, NOW(), 0, 'web', :ip)";
    $st = pdo()->prepare($sql);
    $ok = $st->execute([
        ':n'=>$nombre,
        ':t'=>$telefono,
        ':e'=>$email,
        ':ip'=>($_SERVER['REMOTE_ADDR'] ?? null),
    ]);
    return $ok ? (int)pdo()->lastInsertId() : false;
}

/**
 * Lista de ganadores (JOIN con premios).
 * En tu dump: ganadores.premio_id -> premios.id y el nombre está en premios.premio.
 */
function obtenerGanadores(int $limite = 10): array {
    $db = pdo(); // o tu conectarDB()

    $sql = "SELECT g.fecha,
                   pa.nombre,      -- participantes
                   pa.email,
                   pa.telefono,
                   pr.premio AS premio  -- premios
            FROM ganadores g
            JOIN participantes pa ON pa.id = g.participante_id
            JOIN premios pr       ON pr.id = g.premio_id
            ORDER BY g.fecha DESC
            LIMIT :limite";

    $st = $db->prepare($sql);
    $st->bindValue(':limite', $limite, PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Registra ganador directamente con datos (según tu tabla ganadores del dump).
 */
function registrarGanador(string $nombre, string $email, string $telefono, int $idPremio): bool {
    $sql = "INSERT INTO ganadores (fecha, email, nombre, telefono, premio_id)
            VALUES (NOW(), :e, :n, :t, :p)";
    $st = pdo()->prepare($sql);
    return $st->execute([':e'=>$email, ':n'=>$nombre, ':t'=>$telefono, ':p'=>$idPremio]);
}

/**
 * Registra ganador por participante_id (compatibilidad con tu código previo).
 * Busca datos en participantes y luego inserta en ganadores con el esquema actual.
 */
function registrarGanadorPorParticipanteId(int $participanteId, int $idPremio): bool {
    $st = pdo()->prepare("SELECT nombre, email, telefono FROM participantes WHERE id = :id");
    $st->execute([':id'=>$participanteId]);
    $p = $st->fetch();
    if (!$p) return false;
    return registrarGanador($p['nombre'], $p['email'], $p['telefono'], $idPremio);
}

/** Trae todos los premios (id, texto) */
function obtenerPremios(): array {
    $sql = "SELECT id, premio AS texto FROM premios ORDER BY id ASC";
    return pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/** Retorna un id de premio aleatorio (útil para pruebas/juego) */
function asignarPremioAleatorio(): ?int {
    $premios = obtenerPremios();
    if (!$premios) return null;
    $pick = $premios[array_rand($premios)];
    return (int)$pick['id'];
}

/* ===========================================
   Flujo de registro + sesión de participante
   ========================================== */

/**
 * Registra (si no existe), guarda datos en sesión y retorna el ID.
 * Lanza excepciones para que el caller decida si responde JSON/HTML.
 */
function flujoRegistroParticipante(string $nombre, string $email, string $telefono): int {
    // Validaciones básicas server-side
    if ($nombre === '' || $email === '' || $telefono === '') {
        throw new RuntimeException('Todos los campos son obligatorios.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException('Correo inválido.');
    }
    if (!preg_match('/^[0-9+\-\s]{7,20}$/', $telefono)) {
        throw new RuntimeException('Teléfono inválido.');
    }

    // Duplicados por email o teléfono
    if (participanteExiste($email, $telefono)) {
        throw new RuntimeException('Ya existe un participante con ese correo o teléfono.');
    }

    // Insertar
    $pid = registrarParticipante($nombre, $email, $telefono);
    if (!$pid) {
        throw new RuntimeException('No fue posible registrar el participante.');
    }

    // Sesión
    $_SESSION['participant_id']    = $pid;
    $_SESSION['participant_name']  = $nombre;
    $_SESSION['participant_email'] = $email;
    $_SESSION['participant_phone'] = $telefono;

    return $pid;
}