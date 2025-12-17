<?php
// Incluir configuración de sesión y validar usuario
require_once '../../../config/session_config.php';
require_once '../../../config/database.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();

header('Content-Type: application/json');

try {
    // Obtener empleados solo de la entidad del usuario (seguridad)
    $stmt = $pdo->prepare("
        SELECT e.id_empleado, e.nombre, e.apellido, e.dni, e.usuario, e.id_rol, e.id_equipo, e.id_edificio, e.estado, 
               eq.alias AS equipo, ed.alias AS edificio
        FROM empleados e
        LEFT JOIN equipos eq ON e.id_equipo = eq.id_equipo
        LEFT JOIN edificios ed ON e.id_edificio = ed.id_edificio
        WHERE e.borrado = 0 AND e.id_entidad = :id_entidad
        ORDER BY e.apellido ASC, e.nombre ASC
    ");
    
    $stmt->bindValue(':id_entidad', $usuarioActual['id_entidad'], PDO::PARAM_INT);
    
    $stmt->execute();
    $empleados = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $empleados]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener los empleados", "debug" => $e->getMessage()]);
}
