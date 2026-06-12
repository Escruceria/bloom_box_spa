<?php
// admin/eliminar_premio.php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok'=>false,'msg'=>'Método no permitido']); exit;
    }
    if (function_exists('require_csrf')) { require_csrf(); }

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'toggle'; // toggle | delete
    if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }

    $db = pdo();

    if ($action === 'delete') {
        // Si existe FK en ganadores, esta acción fallará: mejor usar toggle.
        $ok = $db->prepare("DELETE FROM premios WHERE id = :id")->execute([':id'=>$id]);
        if (!$ok) throw new RuntimeException('No se pudo eliminar (¿referenciado en ganadores?).');
        echo json_encode(['ok'=>true, 'deleted'=>true]); exit;
    }

    // TOGGLE: activo 1→0 o 0→1
    $st = $db->prepare("SELECT activo FROM premios WHERE id = :id");
    $st->execute([':id'=>$id]);
    $cur = $st->fetchColumn();
    if ($cur === false) { http_response_code(404); echo json_encode(['ok'=>false,'msg'=>'Premio no encontrado']); exit; }

    $nuevo = ((int)$cur === 1) ? 0 : 1;
    $ok = $db->prepare("UPDATE premios SET activo = :a WHERE id = :id")->execute([':a'=>$nuevo, ':id'=>$id]);
    if (!$ok) throw new RuntimeException('No se pudo actualizar el estado');

    echo json_encode(['ok'=>true, 'activo'=>$nuevo]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'Error del servidor']); // error_log($e);
}
