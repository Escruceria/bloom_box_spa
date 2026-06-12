<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

require_csrf();

// Debe existir participante en sesión
$pid   = (int)($_SESSION['participant_id'] ?? 0);
$name  = $_SESSION['participant_name']  ?? null;
$email = $_SESSION['participant_email'] ?? null;
$phone = $_SESSION['participant_phone'] ?? null;

if ($pid <= 0 || !$name || !$email || !$phone) {
    json_response(['ok'=>false,'msg'=>'Sesión de participante no encontrada. Regístrate de nuevo.'], 401);
}

// Limitar a 1 intento por sesión/participante (ajusta a tu regla)
if (!empty($_SESSION['already_played'])) {
    json_response(['ok'=>false,'msg'=>'Ya jugaste. ¡Gracias por participar!'], 409);
}

// Validar box_id
$boxId = (int)($_POST['box_id'] ?? 0);
if ($boxId < 1 || $boxId > 12) {
    json_response(['ok'=>false,'msg'=>'Selección inválida'], 400);
}

/**
 * LÓGICA DE PREMIO
 * Opción A (simple): aleatorio verdadero desde la tabla premios.
 * Opción B (determinista por caja): mapear por índice para dar sensación de “cada caja tiene algo”.
 * Abajo uso B con fallback a aleatorio si no hay suficientes premios.
 */
$premios = obtenerPremios(); // id, texto
if (!$premios) {
    json_response(['ok'=>false,'msg'=>'No hay premios configurados'], 500);
}

// Determinista: caja 1 -> premio[0], caja 2 -> premio[1], ... con wrap
$idx = ($boxId - 1) % count($premios);
$premioId   = (int)$premios[$idx]['id'];
$premioText = (string)$premios[$idx]['texto'];

// Registrar ganador en tu tabla (modo compatible con tu dump)
$ok = registrarGanador($name, $email, $phone, $premioId);
if (!$ok) {
    json_response(['ok'=>false,'msg'=>'No fue posible registrar el ganador'], 500);
}

// (Opcional) podrías registrar intentos si quieres, pero tu tabla `intentos` referencia `imagenes` (1..6).
// Si deseas, agrega una tabla `intentos_juego` simple, o condiciona a boxId<=6 para guardar en `intentos`.

$_SESSION['already_played'] = true;

json_response([
    'ok' => true,
    'prize' => $premioText,
    'box' => $boxId
]);
