<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin(); // exige sesión admin

// Detecta si la tabla usa 'premio' o 'nombre' para el campo del título
function premioNombreCol(): string {
    static $col = null;
    if ($col !== null) return $col;
    try {
        pdo()->query("SELECT `premio` FROM premios LIMIT 0");
        $col = 'premio';
    } catch (Throwable $e) {
        $col = 'nombre';
    }
    return $col;
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: premios.php');
    exit;
}

$okMsg = $errMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_premio'])) {
    require_csrf();

    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($nombre === '') {
        $errMsg = 'El nombre del premio es obligatorio.';
    } else {
        $col = premioNombreCol();
        $st = pdo()->prepare("UPDATE premios SET `$col`=:n, descripcion=:d, activo=:a WHERE id=:id");
        $ok = $st->execute([':n'=>$nombre, ':d'=>$descripcion, ':a'=>$activo, ':id'=>$id]);
        if ($ok) {
            header('Location: premios.php');
            exit;
        } else {
            $errMsg = 'No fue posible actualizar el premio.';
        }
    }
}

// Cargar datos del premio
$st = pdo()->prepare("SELECT id, COALESCE(premio, nombre) AS premio, descripcion, activo FROM premios WHERE id=:id LIMIT 1");
$st->execute([':id'=>$id]);
$row = $st->fetch();
if (!$row) {
    header('Location: premios.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Premio - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="/bloom_box_spa/admin/">
  <link rel="stylesheet" href="/bloom_box_spa/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .premio-form{background:#f9f9f9;padding:2rem;border-radius:10px;margin:2rem auto;max-width:760px}
    .form-group{margin-bottom:1rem}
    .form-group label{display:block;margin-bottom:.5rem;font-weight:500}
    .form-group input[type="text"], .form-group textarea{width:100%;padding:.8rem;border:1px solid #ddd;border-radius:4px;font-size:1rem}
    .form-group textarea{min-height:100px;resize:vertical}
    .checkbox-group{display:flex;align-items:center;gap:.5rem}
    .switch{position:relative;display:inline-block;width:60px;height:34px}
    .switch input{opacity:0;width:0;height:0}
    .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#ccc;transition:.4s;border-radius:34px}
    .slider:before{position:absolute;content:"";height:26px;width:26px;left:4px;bottom:4px;background:#fff;transition:.4s;border-radius:50%}
    input:checked + .slider{background:var(--primary)}
    input:checked + .slider:before{transform:translateX(26px)}
    .alert{padding:1rem;border-radius:6px;margin-bottom:1rem}
    .alert-err{background:#f8d7da;color:#721c24}
  </style>
</head>
<body>
<header>
  <div class="container">
    <div class="header-content">
      <div class="logo">
        <a class="brand" href="../index.php" aria-label="Bloom Box Spa">
          <picture>
            <source media="(min-width: 992px)" srcset="/bloom_box_spa/images/logo_circular.png">
            <img src="/bloom_box_spa/images/logo_horizontal.png" alt="Bloom Box Spa">
          </picture>
        </a>
        <span>Admin</span>
      </div>
      <nav>
        <ul>
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="participantes.php">Participantes</a></li>
          <li><a href="ganadores.php">Ganadores</a></li>
          <li><a href="premios.php">Premios</a></li>
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
      <h2>Editar Premio</h2>
      <p>Modifica la información del premio seleccionado</p>
    </div>

    <?php if ($errMsg): ?>
      <div class="alert alert-err"><?= esc($errMsg) ?></div>
    <?php endif; ?>

    <div class="premio-form">
      <form method="post" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

        <div class="form-group">
          <label for="nombre">Nombre del Premio *</label>
          <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?= esc($row['premio']) ?>">
        </div>

        <div class="form-group">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" maxlength="500"><?= esc($row['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <div class="checkbox-group">
            <label class="switch">
              <input type="checkbox" id="activo" name="activo" <?= ((int)$row['activo'] === 1 ? 'checked' : '') ?>>
              <span class="slider"></span>
            </label>
            <label for="activo">Premio activo</label>
          </div>
        </div>

        <button type="submit" name="guardar_premio" class="btn btn-primary">Guardar Cambios</button>
        <a class="btn" href="premios.php">Cancelar</a>
      </form>
    </div>
  </div>
</section>
</body>
</html>
