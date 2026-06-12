<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
if (function_exists('require_admin')) require_admin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Parámetros
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 10;
$offset    = ($pagina - 1) * $porPagina;
$busqueda  = trim((string)($_GET['busqueda'] ?? ''));

// FROM + JOIN (tomamos nombre/email/teléfono desde participantes)
$fromJoin = "
  FROM ganadores g
  LEFT JOIN participantes p ON p.id = g.participante_id
  LEFT JOIN premios pr      ON pr.id = g.premio_id
";

// WHERE con placeholders DISTINTOS (evita HY093)
$where  = '';
$params = [];
if ($busqueda !== '') {
  $like = '%'.$busqueda.'%';
  $where = "WHERE p.nombre LIKE :q1 OR p.email LIKE :q2 OR p.telefono LIKE :q3 OR pr.premio LIKE :q4";
  $params = [':q1'=>$like, ':q2'=>$like, ':q3'=>$like, ':q4'=>$like];
}

// Conteo total
$sqlCount = "SELECT COUNT(*) {$fromJoin} {$where}";
$st = pdo()->prepare($sqlCount);
foreach ($params as $k=>$v) { $st->bindValue($k, $v, PDO::PARAM_STR); }
$st->execute();
$total = (int)$st->fetchColumn();
$totalPaginas = max(1, (int)ceil($total / $porPagina));

// Consulta principal
$sql = "
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
  LIMIT :limit OFFSET :offset
";
$st = pdo()->prepare($sql);
foreach ($params as $k=>$v) { $st->bindValue($k, $v, PDO::PARAM_STR); }
$st->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
$st->bindValue(':offset', $offset,    PDO::PARAM_INT);
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Ganadores - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/">
  <link rel="stylesheet" href="/css/style.css">
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
    table.admin-table{width:100%;border-collapse:collapse}
    table.admin-table th, table.admin-table td{padding:.6rem;border-bottom:1px solid #eee;text-align:left}
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
          <li><a href="participantes.php">Participantes</a></li>
          <li><a href="premios.php">Premios</a></li>
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
      <h2>Gestión de Ganadores</h2>
      <p>Administra los ganadores de los sorteos</p>
    </div>

    <div class="export-buttons">
      <a href="exportar_ganadores.php?formato=excel" class="btn btn-primary">Exportar a Excel</a>
      <a href="exportar_ganadores.php?formato=csv" class="btn">Exportar a CSV</a>
    </div>

    <form method="GET" class="search-form" action="ganadores.php">
      <input type="text" name="busqueda" placeholder="Buscar por nombre, email, teléfono o premio..." value="<?= h($busqueda) ?>">
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
        </tr>
      </thead>
      <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $g): ?>
          <tr>
            <td><?= (int)$g['id'] ?></td>
            <td><?= h(date('d/m/Y H:i', strtotime($g['fecha']))) ?></td>
            <td><?= h($g['nombre'] ?? '—') ?></td>
            <td><?= h($g['email'] ?? '—') ?></td>
            <td><?= h($g['telefono'] ?? '—') ?></td>
            <td><?= h($g['premio'] ?? '—') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" style="text-align:center;">No hay ganadores que coincidan con la búsqueda.</td></tr>
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
