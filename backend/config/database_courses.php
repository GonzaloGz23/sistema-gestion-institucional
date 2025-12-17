<?php
// Configuración para la segunda base de datos (Cursos)
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Detectar entorno
$esLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

// Configuración de la base de datos de cursos
if ($esLocal) {
    define('COURSES_DB_HOST', 'localhost');
    define('COURSES_DB_NAME', 'sistema_cursos');
    define('COURSES_DB_USER', 'root');
    define('COURSES_DB_PASS', '');
} else {
    define('COURSES_DB_HOST', 'localhost');
    define('COURSES_DB_NAME', 'production_courses_db_name');
    define('COURSES_DB_USER', 'production_courses_db_user');
    define('COURSES_DB_PASS', 'your_secure_password_here');
}

// Opciones de conexión
$coursesOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $coursesDsn = "mysql:host=" . COURSES_DB_HOST . ";dbname=" . COURSES_DB_NAME . ";charset=utf8mb4";
    $pdoCourses = new PDO($coursesDsn, COURSES_DB_USER, COURSES_DB_PASS, $coursesOptions);
    $pdoCourses->exec("SET time_zone = '-03:00'");
} catch (PDOException $e) {
    die("❌ Error de conexión a base de datos de cursos: " . $e->getMessage());
}
?>