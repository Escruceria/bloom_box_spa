<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo "Método no permitido"; exit;
    }
    if (function_exists('require_csrf')) { require_csrf(); }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { http_response_code(400); echo "ID inválido"; exit; }

    $db = pdo();

    // Trae email/teléfono por si necesitas lógica adicional
    $st = $db->prepare("SELECT id, email, telefono FROM participantes WHERE id=:id");
    $st->execute([':id'=>$id]);
    $p = $st->fetch(PDO::FETCH_ASSOC);
    if (!$p) {
        header('Location: participantes.php?msg=notfound'); exit;
    }

    // BLOQUEO: si tiene registros en ganadores, no eliminar (evita romper integridad)
    $st = $db->prepare("SELECT COUNT(*) FROM ganadores WHERE participante_id = :id");
    $st->execute([':id'=>$id]);
    $hasWins = (int)$st->fetchColumn() > 0;

    if ($hasWins) {
        header('Location: participantes.php?msg=no-delete-winner'); exit;
    }

    // Elimina
    $ok = $db->prepare("DELETE FROM participantes WHERE id = :id")->execute([':id'=>$id]);
    if (!$ok) {
        header('Location: participantes.php?msg=delete-failed'); exit;
    }

    header('Location: participantes.php?msg=deleted'); exit;

} catch (Throwable $e) {
    // error_log($e);
    header('Location: participantes.php?msg=error'); exit;
}
