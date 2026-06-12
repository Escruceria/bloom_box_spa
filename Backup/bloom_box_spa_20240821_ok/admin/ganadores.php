<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php'; // ya incluye db.php y maneja la sesión
require_admin(); // exige sesión admin

// ---- Parámetros
$pagina     = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina  = 10;
$offset     = ($pagina - 1) * $porPagina;
$busqueda   = trim((string)($_GET['busqueda'] ?? ''));

// ---- Base de consultas (compatibles con ambos esquemas)
// - ganadores puede tener (nombre,email,telefono,premio_id)
// - o referencias (participante_id, premio_id)
$fromJoin = "
  FROM ganadores g
  JOIN participantes p ON p.id = g.participante_id
  JOIN premios pr       ON pr.id = g.premio_id
";

$where = '';
$params = [];

if ($busqueda !== '') {
    $where = " WHERE
      p.nombre LIKE :q OR
      p.email  LIKE :q OR
      pr.premio LIKE :q
    ";
    $params[':q'] = "%{$busqueda}%";
}

// ---- Total (para paginación)
$sqlTotal = "SELECT COUNT(*) AS total {$fromJoin} {$where}";
$st = pdo()->prepare($sqlTotal);
foreach ($params as $k=>$v) $st->bindValue($k, $v, PDO::PARAM_STR);
$st->execute();
$total = (int)($st->fetch()['total'] ?? 0);
$totalPaginas = max(1, (int)ceil($total / $porPagina));

// ---- Datos página actual
$sqlPage = "
  SELECT
    g.id,
    g.fecha,
    p.nombre   AS nombre,
    p.email    AS email,
    p.telefono AS telefono,
    pr.premio  AS premio
  {$fromJoin}
  {$where}
  ORDER BY g.fecha DESC
  LIMIT :offset, :limit
";
$st = pdo()->prepare($sqlPage);
foreach ($params as $k=>$v) $st->bindValue($k, $v, PDO::PARAM_STR);
$st->bindValue(':offset', $offset, PDO::PARAM_INT);
$st->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
$st->execute();
$ganadores = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Ganadores - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Base para que los enlaces relativos funcionen dentro de /admin -->
  <base href="/bloom_box_spa/admin/">
  <link rel="stylesheet" href="/bloom_box_spa/css/style.css">
  <style>
    .search-form{margin-bottom:2rem;display:flex;gap:1rem}
    .search-form input{flex:1;padding:.8rem;border:1px solid #ddd;border-radius:4px}
    .search-form button{padding:.8rem 1.5rem}
    .pagination{display:flex;justify-content:center;margin-top:2rem;gap:.5rem}
    .pagination a{padding:.5rem 1rem;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:var(--primary)}
    .pagination a.active{background:var(--primary);color:#fff}
    .actions{display:flex;gap:.5rem}
    .btn-small{padding:.3rem .8rem;font-size:.9rem}
    .export-buttons{margin-bottom:1rem;display:flex;gap:1rem}
  </style>
</head>
<body>
<header>
  <div class="container">
    <div class="header-content">
      <div class="logo">
        <a href="/bloom_box_spa/admin/index.php" class="brand" aria-label="Bloom Box Spa Admin">
          <picture>
            <source media="(min-width: 768px)" srcset="/bloom_box_spa/images/logo_horizontal.png">
            <img src="/bloom_box_spa/images/logo_circular.png" alt="Bloom Box Spa Admin">
          </picture>
        </a>
      </div>
      <nav>
        <ul>
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="participantes.php">Participantes</a></li>
          <li><a href="premios.php">Premios</a></li>
          <li><a href="../index.php">Volver al sitio</a></li>
          <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
      </nav>
    </div>
  </div>
</header>

<section class="admin-section">
  <div class="container">
    <div class="section-title">
      <h2>Gestión de Ganadores</h2>
      <p>Administra los ganadores de los sorteos</p>
    </div>

    <div class="export-buttons">
      <a href="exportar_ganadores.php?formato=excel" class="btn btn-primary">Exportar a Excel</a>
      <a href="exportar_ganadores.php?formato=csv" class="btn">Exportar a CSV</a>
    </div>

    <form method="GET" class="search-form">
      <input type="text" name="busqueda" placeholder="Buscar por nombre, email o premio..." value="<?= esc($busqueda) ?>">
      <button type="submit" class="btn">Buscar</button>
      <?php if ($busqueda !== ''): ?>
        <a href="ganadores.php" class="btn">Limpiar</a>
      <?php endif; ?>
    </form>

    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th>Premio</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($ganadores): ?>
        <?php foreach ($ganadores as $g): ?>
          <tr>
            <td><?= (int)$g['id'] ?></td>
            <td><?= esc(date('d/m/Y H:i', strtotime($g['fecha']))) ?></td>
            <td><?= esc($g['nombre']) ?></td>
            <td><?= esc($g['email']) ?></td>
            <td><?= esc($g['telefono']) ?></td>
            <td><?= esc($g['premio'] ?? '—') ?></td>
            <td class="actions">
              <a href="ver_ganador.php?id=<?= (int)$g['id'] ?>" class="btn btn-small">Ver</a>
              <form action="eliminar_ganador.php" method="POST" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                <button type="submit" class="btn btn-small btn-primary" onclick="return confirm('¿Eliminar este ganador?')">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No hay ganadores registrados</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <?php if ($totalPaginas > 1): ?>
      <div class="pagination">
        <?php for ($i=1; $i <= $totalPaginas; $i++): ?>
          <a href="ganadores.php?pagina=<?= $i ?><?= $busqueda!=='' ? '&busqueda='.urlencode($busqueda) : '' ?>"
             class="<?= $pagina===$i ? 'active' : '' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
</body>
</html>
