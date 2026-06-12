<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
// ini_set('display_errors','0'); // opcional en prod

// 1) Permitir preflight si llega OPTIONS (evita 405 visibles en el navegador)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header('Allow: POST, OPTIONS');
  http_response_code(204);
  exit;
}

// 2) Solo POST (pero ya manejamos OPTIONS arriba)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido (usa POST)']);
  exit;
}

// (Opcional) CSRF si lo tienes en functions.php
if (function_exists('require_csrf')) { require_csrf(); }

// 3) Acepta JSON o x-www-form-urlencoded
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in)) { $in = $_POST; }

// NOMBRES EN ESPAÑOL (compatibles con tu BD)
$nombre   = trim((string)($in['nombre']   ?? $in['name']  ?? ''));
$email    = trim((string)($in['email']    ?? ''));
$telefono = preg_replace('/\D+/', '', (string)($in['telefono'] ?? $in['phone'] ?? ''));

if ($nombre === '' || $email === '' || $telefono === '') {
  echo json_encode(['ok'=>false,'msg'=>'Todos los campos son obligatorios']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['ok'=>false,'msg'=>'Email inválido']); exit;
}

try {
  $db = pdo();
  $db->beginTransaction();

  // ¿Ya existe por email o teléfono?
  $st = $db->prepare("SELECT id FROM participantes WHERE email=:e OR telefono=:t LIMIT 1");
  $st->execute([':e'=>$email, ':t'=>$telefono]);
  if ($row = $st->fetch()) {
    $_SESSION['participant_id'] = (int)$row['id'];
    $db->rollBack();
    echo json_encode(['ok'=>true,'participant_id'=>(int)$row['id'], 'dup'=>true]); exit;
  }

  // Insertar en participantes (columnas de tu dump)
  $st = $db->prepare("INSERT INTO participantes
    (nombre, telefono, email, fecha_registro, ganador, canal_registro, ip_registro)
    VALUES (:n,:t,:e,NOW(),0,'web',:ip)");
  $st->execute([
    ':n'=>$nombre, ':t'=>$telefono, ':e'=>$email,
    ':ip'=>($_SERVER['REMOTE_ADDR'] ?? null)
  ]);
  $pid = (int)$db->lastInsertId();

  // Upsert en clientes
  $db->prepare("INSERT INTO clientes (nombre,email,telefono)
                VALUES (:n,:e,:t)
                ON DUPLICATE KEY UPDATE
                  nombre=VALUES(nombre),
                  telefono=VALUES(telefono),
                  fecha_actualizacion=CURRENT_TIMESTAMP")
     ->execute([':n'=>$nombre, ':e'=>$email, ':t'=>$telefono]);

  $_SESSION['participant_id'] = $pid;
  $db->commit();

  echo json_encode(['ok'=>true,'participant_id'=>$pid]); exit;
} catch (Throwable $e) {
  if (!empty($db) && $db->inTransaction()) $db->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'No fue posible registrarte.']); exit;
}
?>