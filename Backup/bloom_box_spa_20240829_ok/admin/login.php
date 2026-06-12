<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

// Si ya está logueado como admin, manda al panel
if (!empty($_SESSION['user']['is_admin'])) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    rate_limit('admin_login_'.($_SERVER['REMOTE_ADDR'] ?? 'x'), 10, 300);

    $login = trim($_POST['login'] ?? $_POST['username'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($login === '' || $pass === '') {
        $error = 'Usuario y contraseña son obligatorios.';
    } else {
        $st = pdo()->prepare("SELECT login, pswd, active, priv_admin FROM sec_users WHERE login=:l LIMIT 1");
        $st->execute([':l'=>$login]);
        $u = $st->fetch();

        if ($u && $u['active'] === 'Y') {
            $stored = $u['pswd'];
            $ok = false;

            if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
                $ok = password_verify($pass, $stored);
            } else {
                if (md5($pass) === $stored) {
                    $ok = true;
                    $newHash = password_hash($pass, PASSWORD_BCRYPT);
                    pdo()->prepare("UPDATE sec_users SET pswd=:h WHERE login=:l")->execute([':h'=>$newHash, ':l'=>$login]);
                }
            }

            if ($ok && $u['priv_admin'] === 'Y') {
                login_user($u['login'], true);
                header('Location: index.php');
                exit;
            }
        }
        $error = $error ?? 'Credenciales inválidas o usuario sin permisos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login Admin - Bloom Box Spa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?>/">
  <!-- Usa una sola hoja de estilos (versionada para forzar refresco de caché) -->
  <link rel="stylesheet" href="/css/style.css?v=6">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="login-container">
    <div class="logo">
      <a class="brand" href="../index.php" aria-label="Bloom Box Spa">
        <img src="/images/logo_circular.png" alt="Bloom Box Spa">
      </a>
    </div>

    <h1 class="login-title">Bloom Box Spa Admin</h1>
    <p class="login-subtitle">Acceso al panel de administración</p>

    <?php if ($error): ?>
      <div class="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form" action="">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="login">Usuario</label>
        <input type="text" id="login" name="login" required>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;">Iniciar Sesión</button>
    </form>

    <a class="back-link" href="../index.php">← Volver al sitio principal</a>
  </div>
</body>
</html>