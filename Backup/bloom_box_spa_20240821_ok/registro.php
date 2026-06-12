<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

require_csrf();
rate_limit('signup_'.($_SERVER['REMOTE_ADDR'] ?? 'x'), 5, 60);

try {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $pid = flujoRegistroParticipante($name, $email, $phone);

    // Flag para que el front muestre la sección del juego si se navega por HTML
    $_SESSION['can_play'] = true;

    // ¿Es AJAX?
    $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
              || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

    if ($isAjax) {
        json_response([
            'ok' => true,
            'msg' => 'Registro exitoso.',
            'participant_id' => $pid,
            'participant_name' => $_SESSION['participant_name'],
        ]);
    } else {
        header('Location: /#game');
        exit;
    }
} catch (Throwable $e) {
    $msg = $e->getMessage() ?: 'No se pudo registrar';
    $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest')
              || (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json'));

    if ($isAjax) {
        json_response(['ok'=>false,'msg'=>$msg], 400);
    } else {
        // Simple: vuelve a inicio con mensaje (puedes estilizarlo)
        header('Location: /?error='.urlencode($msg).'#giveaway');
        exit;
    }
}
