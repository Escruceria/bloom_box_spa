<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin(); // bloquea si no es admin

// --- Estadísticas
$cntPart = (int)(pdo()->query("SELECT COUNT(*) c FROM participantes")->fetch()['c'] ?? 0);
$cntGan  = (int)(pdo()->query("SELECT COUNT(*) c FROM ganadores")->fetch()['c'] ?? 0);
$cntPrem = (int)(pdo()->query("SELECT COUNT(*) c FROM premios WHERE activo = 1")->fetch()['c'] ?? 0);

// --- Últimos ganadores (usa la función del functions.php)
$ultimos = obtenerGanadores(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel de Administración - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Hace que los enlaces/recursos relativos en /admin/ apunten a /bloom_box_spa/admin/ -->
  <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2rem}
    .stat-item{background:#f7f7f7;padding:1rem;border-radius:12px;text-align:center}
    .stat-item h3{margin:.2rem 0 .6rem}
    .stat-item p{font-size:2rem;font-weight:700;color:var(--primary)}
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
          <li><a href="../index.php">Volver al sitio</a></li>
          <li><a href="premios.php">Premios</a></li>
          <li><a href="participantes.php">Participantes</a></li>
          <li><a href="ganadores.php">Ganadores</a></li>
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
      <h2>Panel de Administración</h2>
      <p>Gestión de participantes, premios y ganadores</p>
    </div>

    <div class="stats">
      <div class="stat-item">
        <h3>Total de Participantes</h3>
        <p><?= $cntPart ?></p>
      </div>
      <div class="stat-item">
        <h3>Total de Ganadores</h3>
        <p><?= $cntGan ?></p>
      </div>
      <div class="stat-item">
        <h3>Premios Disponibles</h3>
        <p><?= $cntPrem ?></p>
      </div>
    </div>

    <h3>Últimos Ganadores</h3>
    <table class="admin-table">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Premio</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($ultimos): ?>
        <?php foreach ($ultimos as $g): ?>
          <tr>
            <td><?= esc($g['fecha']) ?></td>
            <td><?= esc($g['nombre']) ?></td>
            <td><?= esc($g['email']) ?></td>
            <td><?= esc($g['telefono']) ?></td>
            <td><?= esc($g['premio'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" style="text-align:center;">No hay ganadores aún</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
</body>
</html>
