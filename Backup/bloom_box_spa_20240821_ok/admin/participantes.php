<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php'; // ya incluye db.php y sesión
require_admin(); // exige sesión admin

// Parámetros
$pagina    = max(1, (int)($_GET['pagina'] ?? 1));
$porPagina = 10;
$offset    = ($pagina - 1) * $porPagina;
$busqueda  = trim((string)($_GET['busqueda'] ?? ''));
$filtro    = (string)($_GET['filtro'] ?? '');

// Filtros WHERE + params
$whereParts = [];
$params = [];

if ($filtro === 'ganadores') {
    $whereParts[] = 'ganador = 1';
}
if ($busqueda !== '') {
    $whereParts[] = '(nombre LIKE :q OR email LIKE :q OR telefono LIKE :q)';
    $params[':q'] = "%{$busqueda}%";
}
$whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Si hay búsqueda o filtro, desactivamos paginación (como tu versión original)
if ($busqueda !== '' || $filtro === 'ganadores') {
    $sql = "
        SELECT id, nombre, email, telefono, fecha_registro, ganador
        FROM participantes
        {$whereSql}
        ORDER BY fecha_registro DESC
    ";
    $st = pdo()->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
    $st->execute();
    $participantes = $st->fetchAll();
    $totalPaginas = 1;
} else {
    // Total para paginación
    $sqlTotal = "SELECT COUNT(*) total FROM participantes {$whereSql}";
    $st = pdo()->prepare($sqlTotal);
    foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
    $st->execute();
    $total = (int)($st->fetch()['total'] ?? 0);
    $totalPaginas = max(1, (int)ceil($total / $porPagina));

    // Página actual
    $sql = "
        SELECT id, nombre, email, telefono, fecha_registro, ganador
        FROM participantes
        {$whereSql}
        ORDER BY fecha_registro DESC
        LIMIT :offset, :limit
    ";
    $st = pdo()->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue($k, $v, PDO::PARAM_STR);
    $st->bindValue(':offset', $offset, PDO::PARAM_INT);
    $st->bindValue(':limit',  $porPagina, PDO::PARAM_INT);
    $st->execute();
    $participantes = $st->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Participantes - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Base para que todo lo relativo funcione dentro de /admin -->
  <base href="/bloom_box_spa/admin/">
  <link rel="stylesheet" href="/bloom_box_spa/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .filters{display:flex;gap:1rem;margin-bottom:1rem}
    .filters a{padding:.5rem 1rem;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:var(--dark)}
    .filters a.active{background:var(--primary);color:#fff;border-color:var(--primary)}
    .badge{padding:.2rem .5rem;border-radius:12px;font-size:.8rem;font-weight:700}
    .badge-success{background:#4caf50;color:#fff}
    .badge-secondary{background:#6c757d;color:#fff}
    .search-form{margin-bottom:1rem;display:flex;gap:1rem}
    .search-form input{flex:1;padding:.8rem;border:1px solid #ddd;border-radius:4px}
    .pagination{display:flex;justify-content:center;margin-top:2rem;gap:.5rem}
    .pagination a{padding:.5rem 1rem;border:1px solid #ddd;border-radius:4px;text-decoration:none;color:var(--primary)}
    .pagination a.active{background:var(--primary);color:#fff}
    .actions{display:flex;gap:.5rem}
    .btn-small{padding:.3rem .8rem;font-size:.9rem}
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
          <li><a href="ganadores.php">Ganadores</a></li>
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
      <h2>Gestión de Participantes</h2>
      <p>Administra los participantes de los sorteos</p>
    </div>

    <div class="filters">
      <a href="participantes.php" class="<?= $filtro === '' ? 'active' : '' ?>">Todos</a>
      <a href="participantes.php?filtro=ganadores" class="<?= $filtro === 'ganadores' ? 'active' : '' ?>">Solo Ganadores</a>
    </div>

    <form method="get" class="search-form">
      <input type="text" name="busqueda" placeholder="Buscar por nombre, email o teléfono..." value="<?= esc($busqueda) ?>">
      <button type="submit" class="btn">Buscar</button>
      <?php if ($busqueda !== '' || $filtro !== ''): ?>
        <a href="participantes.php" class="btn">Limpiar</a>
      <?php endif; ?>
    </form>

    <table class="admin-table">
      <thead>
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Fecha Registro</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      <?php if ($participantes): ?>
        <?php foreach ($participantes as $p): ?>
          <tr>
            <td><?= (int)$p['id'] ?></td>
            <td><?= esc($p['nombre']) ?></td>
            <td><?= esc($p['email']) ?></td>
            <td><?= esc($p['telefono']) ?></td>
            <td><?= esc(date('d/m/Y H:i', strtotime($p['fecha_registro']))) ?></td>
            <td>
              <?php if ((int)$p['ganador'] === 1): ?>
                <span class="badge badge-success">Ganador</span>
              <?php else: ?>
                <span class="badge badge-secondary">Participante</span>
              <?php endif; ?>
            </td>
            <td class="actions">
              <a href="ver_participante.php?id=<?= (int)$p['id'] ?>" class="btn btn-small">Ver</a>
              <form action="eliminar_participante.php" method="post" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button type="submit" class="btn btn-small btn-primary" onclick="return confirm('¿Estás seguro de eliminar este participante?')">Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center;">No hay participantes registrados</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <?php if ($totalPaginas > 1 && $busqueda === '' && $filtro === ''): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
          <a href="participantes.php?pagina=<?= $i ?>" class="<?= $pagina === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
</body>
</html>
