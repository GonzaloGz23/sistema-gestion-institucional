<?php
include '../../../../backend/config/database.php';

// Verificar que se recibió el ID de la solicitud
if (!isset($_POST['id_solicitud_rh']) || empty($_POST['id_solicitud_rh'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de solicitud requerido']);
    exit;
}

$idSolicitud = $_POST['id_solicitud_rh'];

try {
    // Marcar la solicitud como vista
    $stmt = $pdo->prepare("UPDATE rrhh_solicitudes SET vista = 1 WHERE id_solicitud_rh = ?");
    $resultado = $stmt->execute([$idSolicitud]);
    
    if ($resultado) {
        // Obtener el ID del formulario para contar las solicitudes restantes
        $stmtForm = $pdo->prepare("SELECT id_formulario FROM rrhh_solicitudes WHERE id_solicitud_rh = ?");
        $stmtForm->execute([$idSolicitud]);
        $formulario = $stmtForm->fetch();
        
        if ($formulario) {
            // Contar solicitudes nuevas restantes para este formulario
            $stmtCount = $pdo->prepare("
                SELECT COUNT(*) as total_nuevas
                FROM rrhh_solicitudes 
                WHERE id_formulario = ? 
                AND (vista = 0 OR vista IS NULL)
                AND (borrado = 0 OR borrado IS NULL)
            ");
            $stmtCount->execute([$formulario['id_formulario']]);
            $nuevasRestantes = $stmtCount->fetch()['total_nuevas'];
            
            echo json_encode([
                'success' => true, 
                'message' => 'Solicitud marcada como vista',
                'nuevas_restantes' => $nuevasRestantes,
                'id_formulario' => $formulario['id_formulario']
            ]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Solicitud marcada como vista']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al marcar como vista']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>