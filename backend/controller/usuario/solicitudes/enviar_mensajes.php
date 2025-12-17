<?php
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once '../../../config/database.php';
require_once 'helpers_solicitudes.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

header('Content-Type: application/json');

// Validaciones básicas
if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada. Iniciá sesión nuevamente.']);
    exit;
}

$id_emisor = $_SESSION['usuario']['id'];
$id_equipo = $_SESSION['usuario']['id_equipo'];
$id_solicitud = $_POST['id_solicitud'] ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$id_solicitud || (!$mensaje && empty($_FILES['archivo']['name'][0]))) {
    echo json_encode(['success' => false, 'error' => 'El mensaje o archivo es obligatorio.']);
    exit;
}

try {
    $fechaCreacion = date('Y-m-d H:i:s');
    $pdo->beginTransaction();

    // Insertar mensaje en la tabla de seguimiento
    $stmt = $pdo->prepare("
    INSERT INTO solicitudes_mensajes (id_solicitud, id_emisor, id_emisor_equipo, mensaje, creado_en)
    VALUES (?, ?, ?, ?, ?)
");
    $stmt->execute([$id_solicitud, $id_emisor, $id_equipo, $mensaje, $fechaCreacion]);

    $idMensaje = $pdo->lastInsertId();

    // Procesar archivos si se enviaron
    $archivosSubidos = [];

    if (!empty($_FILES['archivo']['name'][0])) {
        $config = json_decode(file_get_contents('../../../config/config_archivos.json'), true);
        $extensiones = $config['extensiones_permitidas'] ?? ['pdf', 'jpg', 'png'];
        $tamanoMaximo = $config['_maximo_bytes'] ?? (10 * 1024 * 1024);
        $tiposMime = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        // Asociaciones para archivos
        $asociaciones = [
            [
                'id_solicitud' => $id_solicitud,
                'id_mensaje' => $idMensaje
            ]
        ];

        $archivosSubidos = guardarArchivosAdjuntos($pdo, $_FILES['archivo'], $extensiones, $tamanoMaximo, $tiposMime, $asociaciones);

    }

    $pdo->commit();
    
    // ============================================================================
    // RESPUESTA INMEDIATA AL CLIENTE
    // ============================================================================
    
    // Enviar respuesta inmediata al cliente
    echo json_encode([
        'success' => true,
        'id_mensaje' => $idMensaje,
        'archivos' => $archivosSubidos
    ]);
    
    // Finalizar la respuesta HTTP para liberar al cliente
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        // Fallback para servidores que no soportan fastcgi_finish_request
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();
    }
    
    // ============================================================================
    // PROCESAMIENTO ASÍNCRONO DE NOTIFICACIONES
    // ============================================================================
    
    try {
        require_once '../../../../pages/common/functions.php';
        
        // Obtener datos de la solicitud
        $stmtSolicitud = $pdo->prepare("
            SELECT s.asunto, s.id_equipo_emisor, eq.alias as equipoEmisor
            FROM solicitudes s
            INNER JOIN equipos eq ON eq.id_equipo = s.id_equipo_emisor
            WHERE s.id_solicitud = ?
        ");
        $stmtSolicitud->execute([$id_solicitud]);
        $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);
        
        // Obtener equipos destinatarios
        $stmtDestinatarios = $pdo->prepare("
            SELECT sd.id_equipo, eq.alias
            FROM solicitudes_destinatarios sd
            INNER JOIN equipos eq ON eq.id_equipo = sd.id_equipo
            WHERE sd.id_solicitud = ?
        ");
        $stmtDestinatarios->execute([$id_solicitud]);
        $destinatarios = $stmtDestinatarios->fetchAll(PDO::FETCH_ASSOC);
        
        if ($solicitud) {
            // Determinar si el usuario es emisor o receptor
            if ($id_equipo == $solicitud['id_equipo_emisor']) {
                // Usuario es EMISOR - notificar a equipos destinatarios
                $tema = 'MensajeChatEnviada';
                
                foreach ($destinatarios as $destinatario) {
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
                            '{$destinatario['alias']}' as equipoReceptor,
                            '{$solicitud['asunto']}' as asunto
                        FROM `firebase_app_tokens` d
                        inner join empleados e on e.id_empleado=d.rela_usuario
                        inner join firebase_app_msg ff on ff.tema=:tema
                        where d.activo=1 AND d.rela_equipo in({$destinatario['id_equipo']})
                    ";
                    
                    sendAllTitle($pdo, $tema, null, [
                        "title_cols" => ["equipoReceptor", "asunto"],
                        "body_cols" => ["equipoReceptor", "asunto"]
                    ], $sqlCustom);
                    
                }
            } else {
                // Usuario es RECEPTOR - notificar al equipo emisor
                $tema = 'MensajeChatRecibida';
                
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
                        '{$solicitud['equipoEmisor']}' as equipoEmisor,
                        '{$solicitud['asunto']}' as asunto
                    FROM `firebase_app_tokens` d
                    inner join empleados e on e.id_empleado=d.rela_usuario
                    inner join firebase_app_msg ff on ff.tema=:tema
                    where d.activo=1 AND d.rela_equipo in({$solicitud['id_equipo_emisor']})
                ";
                
                sendAllTitle($pdo, $tema, null, [
                    "title_cols" => ["equipoEmisor", "asunto"],
                    "body_cols" => ["equipoEmisor", "asunto"]
                ], $sqlCustom);
            }
        }
        
    } catch (Exception $notifError) {
        // Log silencioso de errores de notificaciones
        error_log("Error en notificaciones asíncronas: " . $notifError->getMessage());
    }

} catch (Exception $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al enviar mensaje: ' . $e->getMessage()]);
}
