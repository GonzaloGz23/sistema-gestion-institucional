<?php
// zona horaria correcta
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Detectar si estamos en entorno local
$esLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

// Configuración según el entorno
if ($esLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'sistema_institucional');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Configuración para producción
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'production_db_name');
    define('DB_USER', 'production_db_user');
    define('DB_PASS', 'your_secure_password_here');
}

// Opciones de conexión
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    // Corregir zona horaria de MySQL para esta conexión
    $pdo->exec("SET time_zone = '-03:00'");
} catch (PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}
