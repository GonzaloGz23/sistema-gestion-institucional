<?php
// Incluir configuración centralizada de sesiones
require_once 'backend/config/session_config.php';

// Incluir conexión a la base de datos
require_once 'backend/config/database.php'; // Define $pdo

// Verificar si la licencia está activa
function licenciaActiva($pdo) {
    $stmt = $pdo->prepare("SELECT activa FROM licencias WHERE id_licencia = 1 LIMIT 1");
    $stmt->execute();
    $resultado = $stmt->fetch();
    return $resultado && $resultado['activa'] == 1;
}

// Verificar si existe alguna entidad registrada
function existeEntidad($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as cantidad FROM entidades");
    $stmt->execute();
    $resultado = $stmt->fetch();
    return $resultado && $resultado['cantidad'] > 0;
}

// Redirección según el estado de sesión y sistema
if (verificarUsuarioAutenticado()) {
        header("Location: pages/index.php");
        exit;
} else {
    if (!licenciaActiva($pdo)) {
        header("Location: pages/activacion/activacion.php");
        exit;
    }

    if (!existeEntidad($pdo)) {
        header("Location: pages/activacion/creacionEntidad.php");
        exit;
    }

    header("Location: pages/login/login.php");
    exit;
}
?>
