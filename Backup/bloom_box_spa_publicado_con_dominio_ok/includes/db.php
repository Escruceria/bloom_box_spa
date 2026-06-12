<?php
declare(strict_types=1);

/* Config DB — con guardas para evitar redefinir constantes */
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'bloom_box_spa');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '553051922428536000');

/* Conexión PDO única (singleton) — segura ante múltiples includes */
if (!function_exists('pdo')) {
    function pdo(): PDO {
        static $db = null;
        if ($db instanceof PDO) return $db;
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $db = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $db;
    }
}

/* Alias de compatibilidad con código viejo */
if (!function_exists('conectarDB')) {
    function conectarDB(): PDO { return pdo(); }
}
