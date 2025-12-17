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
    // Obtener espacios solo de la entidad del usuario (seguridad)
    $stmt = $pdo->prepare("
        SELECT e.id_espacio, e.alias, e.detalle, e.estado, ed.id_edificio, ed.alias AS edificio
        FROM espacios_reservables e
        JOIN edificios ed ON e.id_edificio = ed.id_edificio
        WHERE e.borrado = 0 AND ed.id_entidad = :id_entidad
        ORDER BY e.alias ASC
    ");
    
    $stmt->bindValue(':id_entidad', $usuarioActual['id_entidad'], PDO::PARAM_INT);

    $stmt->execute();
    $espacios = $stmt->fetchAll();

    echo json_encode(["success" => true, "data" => $espacios]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al obtener los espacios reservables", "debug" => $e->getMessage()]);
}
