<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
require_csrf();          // asegura que viene del <form>
logout_user();           // limpia sesión de forma segura
header('Location: login.php');
exit;