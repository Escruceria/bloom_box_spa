<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "ID inválido"; exit; }

$db = pdo();

// Participante
$st = $db->prepare("
  SELECT id, nombre, email, telefono, fecha_registro, ganador, canal_registro, ip_registro
  FROM participantes
  WHERE id = :id
  LIMIT 1
");
$st->execute([':id'=>$id]);
$p = $st->fetch(PDO::FETCH_ASSOC);
if (!$p) { http_response_code(404); echo "Participante no encontrado"; exit; }

// Premios ganados
$st = $db->prepare("
  SELECT g.id, g.fecha, pr.premio
  FROM ganadores g
  LEFT JOIN premios pr ON pr.id = g.premio_id
  WHERE g.participante_id = :id
  ORDER BY g.fecha DESC
");
$st->execute([':id'=>$id]);
$wins = $st->fetchAll(PDO::FETCH_ASSOC);

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ver Participante - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/">
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .card{background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.06);padding:18px;margin:12px 0}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .lbl{font-weight:700;color:#222;margin-bottom:4px}
    .badge{padding:4px 10px;border-radius:12px;font-size:.85rem}
    .on{background:#e7f8ec;color:#0f8a3e}
    .off{background:#fdecec;color:#c1272d}
    table.admin-table th, table.admin-table td{padding:.6rem;border-bottom:1px solid #eee}
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
          <li><a href="participantes.php" class="active">Participantes</a></li>
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
      <h2>Participante #<?= (int)$p['id'] ?></h2>
      <p>Detalle del registro y premios ganados</p>
    </div>

    <div class="card">
      <div class="grid">
        <div>
          <div class="lbl">Nombre</div>
          <div><?= h($p['nombre']) ?></div>
        </div>
        <div>
          <div class="lbl">Email</div>
          <div><?= h($p['email']) ?></div>
        </div>
        <div>
          <div class="lbl">Teléfono</div>
          <div><?= h($p['telefono']) ?></div>
        </div>
        <div>
          <div class="lbl">Fecha registro</div>
          <div><?= h($p['fecha_registro']) ?></div>
        </div>
        <div>
          <div class="lbl">Estado</div>
          <div>
            <span class="badge <?= ((int)$p['ganador']===1?'on':'off') ?>">
              <?= ((int)$p['ganador']===1?'Ganador':'Participante') ?>
            </span>
          </div>
        </div>
        <div>
          <div class="lbl">Canal</div>
          <div><?= h($p['canal_registro'] ?? 'web') ?></div>
        </div>
        <div>
          <div class="lbl">IP</div>
          <div><?= h($p['ip_registro'] ?? '—') ?></div>
        </div>
      </div>

      <div style="margin-top:14px;display:flex;gap:8px">
        <a class="btn" href="participantes.php">← Volver</a>

        <form action="eliminar_participante.php" method="post"
              onsubmit="return confirm('¿Eliminar este participante?');">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button type="submit" class="btn btn-primary">Eliminar</button>
        </form>
      </div>
    </div>

    <div class="card">
      <h3 style="margin:0 0 10px">Premios ganados</h3>
      <table class="admin-table" style="width:100%;">
        <thead>
          <tr><th>ID</th><th>Fecha</th><th>Premio</th></tr>
        </thead>
        <tbody>
        <?php if (!$wins): ?>
          <tr><td colspan="3" style="text-align:center;color:#666">No registra premios.</td></tr>
        <?php else: foreach ($wins as $w): ?>
          <tr>
            <td><?= (int)$w['id'] ?></td>
            <td><?= h($w['fecha']) ?></td>
            <td><?= h($w['premio'] ?? '—') ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</section>
</body>
</html>
