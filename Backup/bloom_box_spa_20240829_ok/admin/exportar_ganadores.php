<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$db = pdo();

$sql = "
  SELECT
    g.fecha,
    pa.nombre,
    pa.email,
    pa.telefono,
    pr.premio
  FROM ganadores g
  JOIN participantes pa ON pa.id = g.participante_id
  JOIN premios pr       ON pr.id = g.premio_id
  ORDER BY g.fecha DESC
";
$rows = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$formato = $_GET['formato'] ?? 'excel';

if ($formato === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="ganadores_bloom_spa_' . date('Y-m-d') . '.xls"');

    echo "<table border='1'>";
    echo "<tr>
            <th>Fecha</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Premio</th>
          </tr>";
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . esc($r['fecha'])    . '</td>';
        echo '<td>' . esc($r['nombre'])   . '</td>';
        echo '<td>' . esc($r['email'])    . '</td>';
        echo '<td>' . esc($r['telefono']) . '</td>';
        echo '<td>' . esc($r['premio'])   . '</td>';
        echo '</tr>';
    }
    echo "</table>";
    exit;
}

if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ganadores_bloom_spa_' . date('Y-m-d') . '.csv"');

    // BOM para que Excel detecte UTF-8
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['fecha','nombre','email','telefono','premio']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['fecha'], $r['nombre'], $r['email'], $r['telefono'], $r['premio']]);
    }
    fclose($out);
    exit;
}

// (Opcional) PDF real: usar Dompdf/TCPDF
header('Location: ganadores.php');
exit;
