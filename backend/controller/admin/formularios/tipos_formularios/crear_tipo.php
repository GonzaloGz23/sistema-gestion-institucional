<?php

require_once '../../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once '../../../../config/session_config.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();
$iduser = $usuarioActual['id'];
header('Content-Type: application/json');

if (!empty($_POST['tipo_formulario']) && !empty($_POST['estado'])) {
    $tipo_formulario = $_POST['tipo_formulario'];
    $estado = $_POST['estado'];
    $descripcion = !empty($_POST['descripcion']) ? $_POST['descripcion'] : '';



    try {

        $stmt = $pdo->prepare("INSERT INTO `tipos_formularios` (`nombre`, `descripcion`, `id_empleados`,  `estados`) VALUES (:nombre, :descripcion, :id_empleados, :estados)");

        $stmt->bindParam(':nombre', $tipo_formulario);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':id_empleados', $iduser);
        $stmt->bindParam(':estados', $estado);

        $stmt->execute();


        echo json_encode(['success' => true, 'message' => 'Se ha guardado con exito']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);

    }
   
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o formato incorrecto.']);
    exit;
}
