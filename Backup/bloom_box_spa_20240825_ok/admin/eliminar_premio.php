<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_admin(); // exige sesión admin

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: premios.php');
    exit;
}
require_csrf();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: premios.php');
    exit;
}

try {
    pdo()->beginTransaction();

    // ¿Está referenciado por algún ganador?
    $st = pdo()->prepare("SELECT COUNT(*) c FROM ganadores WHERE (premio_id = :id OR premio_id = :id)");
    $st->execute([':id' => $id]);
    $refs = (int)($st->fetch()['c'] ?? 0);

    if ($refs > 0) {
        // Si está referenciado, lo marcamos como inactivo (más seguro que borrar)
        $st2 = pdo()->prepare("UPDATE premios SET activo = 0 WHERE id = :id");
        $st2->execute([':id' => $id]);
        pdo()->commit();
        header('Location: premios.php'); // podrías pasar ?msg=inactivado
        exit;
    }

    // Si no está referenciado, se puede eliminar
    $del = pdo()->prepare("DELETE FROM premios WHERE id = :id");
    $del->execute([':id' => $id]);

    pdo()->commit();
    header('Location: premios.php');
    exit;
} catch (Throwable $e) {
    if (pdo()->inTransaction()) pdo()->rollBack();
    // Fallback: marca inactivo por seguridad
    try {
        $st2 = pdo()->prepare("UPDATE premios SET activo = 0 WHERE id = :id");
        $st2->execute([':id' => $id]);
    } catch (Throwable $e2) { /* ignorar */ }
    header('Location: premios.php');
    exit;
}
