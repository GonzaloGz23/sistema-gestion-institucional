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
header('Content-Type: application/json');

// Validaciones de entrada
if (!isset($_POST['id_rol']) || !isset($_POST['empleados']) || !is_array($_POST['empleados'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o formato incorrecto.']);
    exit;
}

$idRol = (int) $_POST['id_rol'];
$empleados = $_POST['empleados'];

try {
    $stmt = $pdo->prepare("UPDATE empleados SET id_rol = :idRol WHERE id_empleado = :idEmpleado");

    foreach ($empleados as $idEmp) {
        $stmt->execute([
            ':idRol' => $idRol,
            ':idEmpleado' => (int)$idEmp
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Rol asignado a los empleados seleccionados.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al asignar rol.',
        'debug' => $e->getMessage()
    ]);
}
