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

    // Acepta tanto "username" como "login" (por si el form usa uno u otro)
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

            // Hash moderno
            if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$argon2')) {
                $ok = password_verify($pass, $stored);
            } else {
                // Compatibilidad: MD5 heredado (p.ej. "admin" de ScriptCase) + migración
                if (md5($pass) === $stored) {
                    $ok = true;
                    $newHash = password_hash($pass, PASSWORD_BCRYPT);
                    pdo()->prepare("UPDATE sec_users SET pswd=:h WHERE login=:l")->execute([':h'=>$newHash, ':l'=>$login]);
                }
            }

            if ($ok && $u['priv_admin'] === 'Y') {
                // Sesión segura
                login_user($u['login'], true); // guarda ['user'] con is_admin=true
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
  <!-- Base para resolver rutas relativas dentro de /admin -->
  <base href="/bloom_box_spa/admin/">
  <link rel="stylesheet" href="/bloom_box_spa/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .login-container{max-width:400px;margin:5rem auto;padding:2rem;background:#fff;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.1)}
    .login-form{margin-top:1.5rem}
    .form-group{margin-bottom:1.2rem}
    .form-group label{display:block;margin-bottom:.5rem;font-weight:500}
    .form-group input{width:100%;padding:.8rem;border:1px solid #ddd;border-radius:6px;font-size:1rem}
    .alert{background:#f8d7da;color:#721c24;padding:1rem;border-radius:6px;margin-bottom:1rem}
  </style>
</head>
<body>
  <div class="login-container">
    <div style="text-align:center;margin-bottom:1rem;">
      <div class="logo" style="justify-content:center;">
        <i class="fas fa-spa" style="color:var(--primary);"></i>
        <span>Bloom Box Spa Admin</span>
      </div>
      <p>Acceso al panel de administración</p>
    </div>

    <?php if ($error): ?>
      <div class="alert"><?= esc($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form" action="">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="login">Usuario</label>
        <!-- name="login" (soporta también "username" en el PHP por compatibilidad) -->
        <input type="text" id="login" name="login" required>
      </div>
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn" style="width:100%;">Iniciar Sesión</button>
    </form>

    <div style="margin-top:1.5rem;text-align:center;">
      <a href="../index.php">← Volver al sitio principal</a>
    </div>
  </div>
</body>
</html>