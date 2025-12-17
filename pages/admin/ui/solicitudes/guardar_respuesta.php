<?php
include '../../../../backend/config/database.php';
// Incluir funciones de notificaciones
require_once '../../../../pages/common/functions.php';

$idSolicitudRH = $_POST['id_solicitud_rh'] ?? null;
$respuesta = trim($_POST['respuesta'] ?? '');

if (!$idSolicitudRH || !$respuesta) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

try {
    // 1. Actualizar la respuesta en la base de datos
    $stmt = $pdo->prepare("
        UPDATE rrhh_solicitudes 
        SET respuesta = ? 
        WHERE id_solicitud_rh = ?
    ");
    $stmt->execute([$respuesta, $idSolicitudRH]);

    // 2. Enviar notificación al solicitante original
    try {
        // Obtener datos del solicitante y formulario
        $stmtNotif = $pdo->prepare("
            SELECT 
                rs.id_empleado,
                f.nombre as formulario
            FROM rrhh_solicitudes rs
            INNER JOIN formularios f ON rs.id_formulario = f.id_formularios
            WHERE rs.id_solicitud_rh = ?
        ");
        $stmtNotif->execute([$idSolicitudRH]);
        $datosNotif = $stmtNotif->fetch();
        
        if ($datosNotif) {
            // Configurar destinatario: solo el solicitante original
            $where = [
                "rela_usuario" => $datosNotif['id_empleado']
            ];
            
            // Configurar datos para reemplazar en el template
            $dataSchema = [
                "body_cols" => ["formulario"]
            ];
            
            // SQL personalizada para el solicitante específico
            $sqlCustom = "
                SELECT 
                    d.token_equipo,
                    e.nombre,
                    e.apellido,
                    ff.titulo_notificacion,
                    ff.cuerpo_notificacion,
                    ff.imagen_notificacion,
                    ff.link_ref,
                    d.rela_usuario,
                    d.firebase_app_tokensid,
                    '{$datosNotif['formulario']}' as formulario

                FROM firebase_app_tokens d
                INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                WHERE d.activo = 1 
                AND d.rela_usuario = {$datosNotif['id_empleado']}
            ";
            
            // Enviar notificación
            $resultadoNotif = sendAllTitle($pdo, 'RRHHRespuestaRecibida', $where, $dataSchema, $sqlCustom);
        }
    } catch (Exception $notifError) {
        // Log del error pero no fallar la operación principal
        error_log("Error enviando notificación de respuesta RRHH: " . $notifError->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Respuesta guardada correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la respuesta: ' . $e->getMessage()]);
}
