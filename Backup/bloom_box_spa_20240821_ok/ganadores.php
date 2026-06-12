<?php
include 'includes/db.php';

$ganadores = obtenerGanadores(10);

if (count($ganadores) > 0) {
    foreach ($ganadores as $ganador) {
        echo "<tr>
                <td>{$ganador['fecha']}</td>
                <td>{$ganador['nombre']}</td>
                <td>{$ganador['email']}</td>
                <td>{$ganador['telefono']}</td>
                <td>{$ganador['premio']}</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align: center;'>No hay ganadores aún</td></tr>";
}
?>