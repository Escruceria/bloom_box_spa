<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

try {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
    exit;
  }
  if (function_exists('require_csrf')) require_csrf();

  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) { throw new RuntimeException('ID inválido'); }

  $db = pdo();

  // Leer estado actual
  $st = $db->prepare('SELECT activo FROM premios WHERE id=:id');
  $st->execute([':id'=>$id]);
  $activo = $st->fetchColumn();
  if ($activo === false) { throw new RuntimeException('Premio no existe'); }

  // Toggle
  $nuevo = ((int)$activo === 1) ? 0 : 1;

  $ok = $db->prepare('UPDATE premios SET activo=:a, fecha_actualizacion=NOW() WHERE id=:id')
           ->execute([':a'=>$nuevo, ':id'=>$id]);

  $payload = ['ok'=>$ok ? true : false, 'activo'=>$nuevo];

  // ¿Es una petición AJAX (o pide JSON)?
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  $xReq   = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
  $isAjax = (strpos($accept, 'application/json') !== false) || $xReq === 'xmlhttprequest';

  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
  }

  // Si no es AJAX, volvemos a premios con flash
  $_SESSION['flash'] = [
    'type' => $ok ? 'success' : 'error',
    'msg'  => $ok ? ($nuevo ? 'Premio activado.' : 'Premio inactivado.')
                  : 'No fue posible actualizar el premio.'
  ];
  header('Location: premios.php');
  exit;

} catch (Throwable $e) {
  $msg = (defined('IS_DEVELOPMENT') && IS_DEVELOPMENT) ? $e->getMessage() : 'Error del servidor';
  $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
  $isAjax = strpos($accept, 'application/json') !== false;

  if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>$msg]);
  } else {
    $_SESSION['flash'] = ['type'=>'error','msg'=>$msg];
    header('Location: premios.php');
  }
  exit;
}
