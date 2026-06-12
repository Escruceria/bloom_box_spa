<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/functions.php';

/* ─────────────────────────────────────────────────────────
   Cabeceras JSON + anti-caché (evita respuestas en caché)
   ───────────────────────────────────────────────────────── */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

/* Permitir preflight OPTIONS (algunos navegadores lo envían) */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header('Allow: POST, OPTIONS');
  http_response_code(204);
  exit;
}

/* Solo POST para el registro */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido (usa POST)']);
  exit;
}

/* CSRF (si está definido en includes/functions.php) 
   Nota: requiere que el formulario envíe el campo hidden "csrf" */
if (function_exists('require_csrf')) { require_csrf(); }

/* Aceptar JSON o x-www-form-urlencoded */
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true);
if (!is_array($in)) { $in = $_POST; }

/* Normalización de inputs (compatibles con tu BD) */
$nombre   = trim((string)($in['nombre']   ?? $in['name']  ?? ''));
$email    = trim((string)($in['email']    ?? ''));
$telefono = preg_replace('/\D+/', '', (string)($in['telefono'] ?? $in['phone'] ?? ''));

/* Validaciones server-side */
if ($nombre === '' || $email === '' || $telefono === '') {
  echo json_encode(['ok'=>false,'msg'=>'Todos los campos son obligatorios']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['ok'=>false,'msg'=>'Email inválido']); exit;
}
if (strlen($telefono) < 7) {
  echo json_encode(['ok'=>false,'msg'=>'Teléfono inválido']); exit;
}

try {
  $db = pdo();

  /* 1) ¿Ya existe por email o teléfono?  (sin transacción aún) */
  $st = $db->prepare("SELECT id, ganador FROM participantes WHERE email=:e OR telefono=:t LIMIT 1");
  $st->execute([':e'=>$email, ':t'=>$telefono]);
  if ($row = $st->fetch()) {
    $_SESSION['participant_id'] = (int)$row['id'];

    // Si YA fue ganador, bloquear
    if ((int)$row['ganador'] === 1) {
      echo json_encode([
        'ok'  => false,
        'msg' => 'Ya fuiste favorecido con un regalo. Solo puedes jugar una vez.'
      ]);
      exit;
    }

    // Ya existe y NO ha ganado → puede jugar
    echo json_encode(['ok'=>true,'participant_id'=>(int)$row['id'], 'dup'=>true]);
    exit;
  }

  /* 2) Nuevo participante → ahora sí usamos transacción */
  $db->beginTransaction();

  // Insertar en participantes (según tu esquema)
  $st = $db->prepare("INSERT INTO participantes
    (nombre, telefono, email, fecha_registro, ganador, canal_registro, ip_registro)
    VALUES (:n,:t,:e,NOW(),0,'web',:ip)");
  $st->execute([
    ':n'=>$nombre,
    ':t'=>$telefono,
    ':e'=>$email,
    ':ip'=>($_SERVER['REMOTE_ADDR'] ?? null)
  ]);
  $pid = (int)$db->lastInsertId();

  // Upsert en clientes
  $st2 = $db->prepare("INSERT INTO clientes (nombre,email,telefono)
                       VALUES (:n,:e,:t)
                       ON DUPLICATE KEY UPDATE
                         nombre=VALUES(nombre),
                         telefono=VALUES(telefono),
                         fecha_actualizacion=CURRENT_TIMESTAMP");
  $st2->execute([':n'=>$nombre, ':e'=>$email, ':t'=>$telefono]);

  // Sesión de participante
  $_SESSION['participant_id'] = $pid;

  $db->commit();

  echo json_encode(['ok'=>true,'participant_id'=>$pid]);
  exit;

} catch (Throwable $e) {
  if (!empty($db) && $db->inTransaction()) $db->rollBack();

  // Puedes descomentar para inspección local:
  // error_log('REGISTRO ERROR: '.$e->getMessage());

  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'No fue posible registrarte.']);
  exit;
}
