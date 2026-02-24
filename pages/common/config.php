<?php
// Detectar si el entorno es local
$isLocal = in_array($_SERVER["HTTP_HOST"], ["localhost", "127.0.0.1"]);

// Definir rutas dinámicas según entorno
if ($isLocal) {
    // Entorno local
    define("BASE_URL", "/sistema-gestion-institucional/dist/");
    define("ROOT_PATH", "/sistema-gestion-institucional/");
    define("ROOT_DIR", dirname(__DIR__, 2));
} else {
    // Entorno de producción (ajustá las rutas reales)
    define("BASE_URL", "/sistema-gestion-institucional/dist/"); // o la ruta completa si es en subdirectorio
    define("ROOT_PATH", "/sistema-gestion-institucional/"); // raíz del dominio
    define("ROOT_DIR", dirname(__DIR__, 2));
}
?>
