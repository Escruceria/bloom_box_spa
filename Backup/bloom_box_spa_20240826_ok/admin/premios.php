<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin(); // bloquea si no es admin

$okMsg = $errMsg = null;

/** Detecta dinámicamente si la tabla premios usa la columna 'premio' o 'nombre' */
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

// Crear premio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_premio'])) {
    require_csrf();
    rate_limit('create_prize_'.($_SERVER['REMOTE_ADDR'] ?? 'x'), 5, 60);

    $nombre = trim((string)($_POST['nombre'] ?? ''));
    $descripcion = trim((string)($_POST['descripcion'] ?? ''));
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($nombre === '') {
        $errMsg = 'El nombre del premio es obligatorio.';
    } else {
        $col = premioNombreCol();
        $sql = "INSERT INTO premios (`$col`, descripcion, activo) VALUES (:n, :d, :a)";
        $st = pdo()->prepare($sql);
        $ok = $st->execute([':n'=>$nombre, ':d'=>$descripcion, ':a'=>$activo]);
        $ok ? $okMsg = 'Premio creado correctamente.' : $errMsg = 'Error al crear el premio.';
    }
}

// Listar premios usando la columna que exista (premio | nombre)
$col  = premioNombreCol();
$st   = pdo()->prepare("SELECT id, `$col` AS premio, descripcion, activo
                        FROM premios
                        ORDER BY id DESC");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Premios - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Hace que rutas relativas funcionen dentro de /admin -->
  <base href="/bloom_box_spa/admin/">
  <link rel="stylesheet" href="/bloom_box_spa/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .premio-form{background:#f9f9f9;padding:2rem;border-radius:10px;margin-bottom:2rem}
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
    .status-badge{padding:.3rem .8rem;border-radius:12px;font-size:.8rem;font-weight:700}
    .status-active{background:#4caf50;color:#fff}
    .status-inactive{background:#f44336;color:#fff}
    .alert{padding:1rem;border-radius:6px;margin-bottom:1rem}
    .alert-ok{background:#d4edda;color:#155724}
    .alert-err{background:#f8d7da;color:#721c24}
    .actions{display:flex;gap:.5rem}
    .btn-small{padding:.3rem .8rem;font-size:.9rem}
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
      <h2>Gestión de Premios</h2>
      <p>Administra los premios disponibles para los sorteos</p>
    </div>

    <?php if ($okMsg): ?>
      <div class="alert alert-ok"><?= esc($okMsg) ?></div>
    <?php endif; ?>
    <?php if ($errMsg): ?>
      <div class="alert alert-err"><?= esc($errMsg) ?></div>
    <?php endif; ?>

    <div class="premio-form">
      <h3>Crear Nuevo Premio</h3>
      <form method="post" action="">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="nombre">Nombre del Premio *</label>
          <input type="text" id="nombre" name="nombre" required maxlength="100">
        </div>
        <div class="form-group">
          <label for="descripcion">Descripción</label>
          <textarea id="descripcion" name="descripcion" maxlength="500"></textarea>
        </div>
        <div class="form-group">
          <div class="checkbox-group">
            <label class="switch">
              <input type="checkbox" id="activo" name="activo" checked>
              <span class="slider"></span>
            </label>
            <label for="activo">Premio activo</label>
          </div>
        </div>
        <button type="submit" name="crear_premio" class="btn">Crear Premio</button>
      </form>
    </div>

    <h3>Premios Existentes</h3>
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= esc($r['premio']) ?></td>
            <td><?= esc($r['descripcion'] ?? '') ?></td>
            <td>
              <?php if ((int)$r['activo'] === 1): ?>
                <span class="status-badge status-active">Activo</span>
              <?php else: ?>
                <span class="status-badge status-inactive">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="actions">
              <a href="editar_premio.php?id=<?= (int)$r['id'] ?>" class="btn btn-small">Editar</a>
              <form action="eliminar_premio.php" method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="btn btn-small btn-primary" onclick="return confirm('¿Estás seguro de eliminar este premio?')">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" style="text-align:center;">No hay premios registrados</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
</body>
</html>
