<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

logout_user();                 // limpia $_SESSION, cookie de sesión y cierra
header('Location: login.php'); // vuelve al login de admin
exit;
