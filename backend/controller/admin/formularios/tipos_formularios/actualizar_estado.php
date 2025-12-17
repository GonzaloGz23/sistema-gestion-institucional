<?php

// Incluir configuración centralizada de sesiones
require_once '../../../../config/session_config.php';
require_once '../../../../config/database.php';

// Verificar autenticación antes de continuar
if (!verificarUsuarioAutenticado()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$iduser = $_SESSION['usuario']['id'];
header('Content-Type: application/json');

if (!empty($_POST['id']) && !empty($_POST['estado'])) {

    $idFormulario = $_POST['id'];
    $nuevoEstado = $_POST['estado'];
    
    try {
        $stmt = $pdo->prepare("UPDATE `tipos_formularios` SET `estados`=:estado  WHERE `id_tipos_formularios` =:id");
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $idFormulario);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Se ha guardado con exito']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);

    }
   
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o formato incorrecto.']);
    exit;
}
