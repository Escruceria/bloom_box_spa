<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$db = pdo();
$id = (int)($_GET['id'] ?? ($_POST['id'] ?? 0));
if ($id <= 0) { http_response_code(400); echo "ID inválido"; exit; }

$flash = ['type'=>'', 'msg'=>''];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  if (function_exists('require_csrf')) require_csrf();

  $premio      = trim((string)($_POST['premio'] ?? ''));
  $descripcion = trim((string)($_POST['descripcion'] ?? ''));
  $activo      = isset($_POST['activo']) ? 1 : 0;

  if ($premio === '') {
    $flash = ['type'=>'error','msg'=>'El campo premio es obligatorio.'];
  } else {
    $ok = $db->prepare("
      UPDATE premios
      SET premio = :premio,
          descripcion = :descripcion,
          activo = :activo,
          fecha_actualizacion = NOW()
      WHERE id = :id
    ")->execute([
      ':premio'      => $premio,
      ':descripcion' => $descripcion,
      ':activo'      => $activo,
      ':id'          => $id,
    ]);

    if ($ok) {
      $flash = ['type'=>'success','msg'=>'Cambios guardados correctamente.'];
    } else {
      $flash = ['type'=>'error','msg'=>'No fue posible guardar los cambios.'];
    }
  }
}

// Traer datos del premio (usa columnas reales: premio, descripcion, activo)
$st = $db->prepare("SELECT id, premio, descripcion, activo FROM premios WHERE id = :id LIMIT 1");
$st->execute([':id'=>$id]);
$pr = $st->fetch(PDO::FETCH_ASSOC);
if (!$pr) { http_response_code(404); echo "Premio no encontrado"; exit; }

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar premio - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .card{background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.06);padding:18px;margin:12px 0}
    .row{display:flex;gap:12px;align-items:center}
    .flash{margin:12px 0;padding:10px 14px;border-radius:8px;border:1px solid transparent}
    .flash.success{background:#eafaf0;border-color:#bde5c8;color:#0f8a3e}
    .flash.error{background:#fdecec;border-color:#f5b5b5;color:#c1272d}
    .muted{color:#478a57;background:#e8f7ea;border-radius:8px;padding:6px 10px;display:inline-block}
    .form-group{margin-bottom:12px}
    .form-group label{display:block;font-weight:700;margin-bottom:6px}
    .form-group input[type=text],
    .form-group textarea{width:100%;padding:.8rem;border:1px solid #ddd;border-radius:8px}
  </style>
</head>
<body>

<header>
  <div class="container">
    <div class="header-content">
      <div class="logo">
        <a class="brand" href="../index.php" aria-label="Bloom Box Spa">
          <picture>
            <source media="(min-width: 992px)" srcset="/images/logo_circular.png">
            <img src="/images/logo_horizontal.png" alt="Bloom Box Spa">
          </picture>
        </a>
        <span>Admin</span>
      </div>
      <nav>
        <ul>
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="premios.php" class="active">Premios</a></li>
          <li><a href="participantes.php">Participantes</a></li>
          <li><a href="ganadores.php">Ganadores</a></li>
          <li><a href="../index.php">Volver al sitio</a></li>
          <li>
            <form action="logout.php" method="post" style="display:inline;">
              <?= csrf_field() ?>
              <button class="btn" type="submit">Cerrar sesión</button>
            </form>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<section class="admin-section">
  <div class="container">
    <div class="section-title">
      <h2>Editar premio</h2>
      <p>Actualiza la información del premio</p>
    </div>

    <?php if ($flash['msg'] !== ''): ?>
      <div class="flash <?= h($flash['type']) ?>"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <div class="card">
      <form action="editar_premio.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$pr['id'] ?>">

        <div class="form-group">
          <label for="premio">Premio *</label>
          <input type="text" id="premio" name="premio" required value="<?= h($pr['premio']) ?>">
        </div>

        <div class="form-group">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" rows="6"><?= h($pr['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-group row">
          <label style="display:flex;align-items:center;gap:8px;">
            <input type="checkbox" name="activo" value="1" <?= ((int)$pr['activo']===1?'checked':'') ?>>
            Activo
          </label>
          <span class="muted"><?= ((int)$pr['activo']===1?'Activo':'Inactivo') ?></span>
        </div>

        <div class="row" style="margin-top:10px">
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
          <a class="btn" href="premios.php">Volver</a>
        </div>
      </form>
    </div>
  </div>
</section>
</body>
</html>
