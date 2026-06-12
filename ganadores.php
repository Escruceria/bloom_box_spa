<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$rows = obtenerGanadores(10); // trae con JOIN a participantes y premios

if (!$rows) {
    echo '<tr><td colspan="5" style="text-align:center;">Aún no hay ganadores</td></tr>';
    return;
}

foreach ($rows as $r) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($r['fecha']))) . '</td>';
    echo '<td>' . htmlspecialchars($r['nombre'])   . '</td>';
    echo '<td>' . htmlspecialchars($r['email'])    . '</td>';
    echo '<td>' . htmlspecialchars($r['telefono']) . '</td>';
    echo '<td>' . htmlspecialchars($r['premio'] ?? '—') . '</td>';
    echo '</tr>';
}
?>