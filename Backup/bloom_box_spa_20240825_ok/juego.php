<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
// ini_set('display_errors', '0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']); exit;
}

if (function_exists('require_csrf')) { require_csrf(); }

$in  = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// ⚠️ Acepta español o inglés
$pid  = (int)($in['participante_id'] ?? $in['participant_id'] ?? ($_SESSION['participant_id'] ?? 0));
$caja = (int)($in['imagen_id']      ?? $in['gift_id']        ?? 0);

if ($pid <= 0 || $caja <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']); exit;
}

// Validar participante
$st = pdo()->prepare("SELECT id, ganador FROM participantes WHERE id=:id LIMIT 1");
$st->execute([':id'=>$pid]);
$p = $st->fetch();
if (!$p) { echo json_encode(['ok'=>false,'msg'=>'Participante no existe']); exit; }
if ((int)$p['ganador'] === 1) { echo json_encode(['ok'=>false,'msg'=>'Solo puedes jugar una vez']); exit; }

// Elegir premio activo
$premio = pdo()->query("SELECT id, premio FROM premios WHERE activo=1 ORDER BY RAND() LIMIT 1")->fetch();
if (!$premio) { echo json_encode(['ok'=>false,'msg'=>'No hay premios activos']); exit; }

try {
  $db = pdo();
  $db->beginTransaction();

  $db->prepare("INSERT INTO ganadores (participante_id, premio_id) VALUES (:pid,:pr)")
     ->execute([':pid'=>$pid, ':pr'=>(int)$premio['id']]);

  $db->prepare("UPDATE participantes SET ganador=1 WHERE id=:id")
     ->execute([':id'=>$pid]);

  $db->prepare("INSERT INTO intentos (participante_id, premio_id, imagen_id, acertado, ip)
                VALUES (:pid,:pr,:img,1,:ip)")
     ->execute([
        ':pid'=>$pid, ':pr'=>(int)$premio['id'],
        ':img'=>$caja, ':ip'=>($_SERVER['REMOTE_ADDR'] ?? null)
     ]);

  $db->commit();
  echo json_encode(['ok'=>true,'prize'=>['id'=>(int)$premio['id'], 'premio'=>$premio['premio']]]);
} catch (Throwable $e) {
  if ($db->inTransaction()) $db->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'No fue posible asignar el premio']); exit;
}
?>