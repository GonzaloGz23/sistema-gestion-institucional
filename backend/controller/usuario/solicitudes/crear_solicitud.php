<?php
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once '../../../config/database.php';
require_once 'helpers_solicitudes.php';
// INCLUIR FUNCIONES DE NOTIFICACIÓN - Necesario para enviar notificaciones push
require_once '../../../../pages/common/functions.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

header('Content-Type: application/json');

// Usar función helper para obtener datos del usuario
$usuarioActual = obtenerUsuarioActual();
if (!$usuarioActual) {
    echo json_encode(['success' => false, 'error' => 'Sesión expirada. Iniciá sesión nuevamente.']);
    exit;
}

$id_emisor = $usuarioActual['id'];
$id_equipo_emisor = $usuarioActual['id_equipo'];

$asunto = trim($_POST['asunto'] ?? '');
$contenido = trim($_POST['contenido'] ?? '');
$enviarATodos = isset($_POST['enviar_a_todos']) && $_POST['enviar_a_todos'] == '1';
$privado = $_POST['privado'];


$equipos = $_POST['equipos'] ?? [];
$archivos = $_FILES['archivo'] ?? null;

if (!$asunto || !$contenido) {
    echo json_encode(['success' => false, 'error' => 'Faltan el asunto o el contenido.']);
    exit;
}

if (!$enviarATodos && (!is_array($equipos) || count($equipos) === 0)) {
    echo json_encode(['success' => false, 'error' => 'Seleccioná al menos un equipo o marcá "Enviar a todos".']);
    exit;
}

try {
    validarDNIDesdeBD($pdo, $id_emisor);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

$fechaCreacion = date('Y-m-d H:i:s');
$transaccionActiva = false;

try {
    $pdo->beginTransaction();
    $transaccionActiva = true;

    // Obtener equipos destinatarios
    if ($enviarATodos) {
        $stmt = $pdo->prepare("SELECT id_equipo FROM equipos WHERE estado = 'habilitado' AND borrado = 0 AND id_equipo != ?");
        $stmt->execute([$id_equipo_emisor]);
        $equiposDestino = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $equiposDestino = array_map('intval', $equipos);
    }
  

     $stmtSolicitud = $pdo->prepare("
        INSERT INTO solicitudes (
            id_emisor, id_equipo_emisor, asunto, creada_en, estado, borrado_emisor, borrado_receptor, privado
        ) VALUES (?, ?, ?, ?, 'pendiente', 0, 0, '$privado')
    ");

  
    $stmtMensaje = $pdo->prepare("
        INSERT INTO solicitudes_mensajes (
            id_solicitud, id_emisor, id_emisor_equipo, mensaje, creado_en, borrado
        ) VALUES (?, ?, ?, ?, ?, 0)
    ");

    $stmtDestinatario = $pdo->prepare("
        INSERT INTO solicitudes_destinatarios (id_solicitud, id_equipo) VALUES (?, ?)
    ");

    // Cargar configuración de archivos
    $configPath = '../../../config/config_archivos.json';
    if (!file_exists($configPath)) {
        throw new Exception('No se encontró el archivo de configuración de archivos.');
    }

    $config = json_decode(file_get_contents($configPath), true);
    $extensionesPermitidas = $config['extensiones_permitidas'] ?? [];
    $tiposMimePermitidos = $config['tipos_mime_permitidos'] ?? [];
    $tamanoMaximo = $config['tamano_maximo_bytes'] ?? (10 * 1024 * 1024);

    $idsCreados = [];
    $asociaciones = [];

    // Crear solicitudes y mensajes
    foreach ($equiposDestino as $id_destino) {
        $stmtSolicitud->execute([$id_emisor, $id_equipo_emisor, $asunto, $fechaCreacion]);
        $idSolicitud = $pdo->lastInsertId();

        // Asociar destinatario
        $stmtDestinatario->execute([$idSolicitud, $id_destino]);

        // Crear mensaje inicial
        $stmtMensaje->execute([$idSolicitud, $id_emisor, $id_equipo_emisor, $contenido, $fechaCreacion]);
        $idMensajeInicial = $pdo->lastInsertId();

        $asociaciones[] = [
            'id_solicitud' => $idSolicitud,
            'id_mensaje'   => $idMensajeInicial
        ];

        $idsCreados[] = $idSolicitud;
    }

    // ✅ Procesar archivos UNA VEZ para todas las solicitudes
    if ($archivos && !empty($archivos['name'][0])) {
        guardarArchivosAdjuntos(
            $pdo,
            $archivos,
            $extensionesPermitidas,
            $tamanoMaximo,
            $tiposMimePermitidos,
            $asociaciones
        );
    }

    $pdo->commit();
    
    // ============================================================================
    // OPTIMIZACIÓN: RESPUESTA INMEDIATA AL CLIENTE
    // ============================================================================
    // Responder inmediatamente al usuario para desbloquear la interfaz
    echo json_encode(['success' => true, 'cantidad' => count($idsCreados), 'ids' => $idsCreados]);
    
    // Cerrar la conexión HTTP para que el cliente reciba la respuesta inmediatamente
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
    // PROCESAMIENTO ASÍNCRONO: ENVIAR NOTIFICACIONES EN BACKGROUND
    // ============================================================================
    // A partir de aquí, el usuario ya recibió la respuesta y puede continuar trabajando
    try {
        // OBTENER DATOS DEL EQUIPO EMISOR - Necesarios para personalizar la notificación
        $stmtEmisor = $pdo->prepare("
            SELECT eq.alias 
            FROM equipos eq 
            WHERE eq.id_equipo = ?
        ");
        $stmtEmisor->execute([$id_equipo_emisor]);
        $datosEmisor = $stmtEmisor->fetch(PDO::FETCH_ASSOC);
        
        if ($datosEmisor) {
            // ENVIAR NOTIFICACIÓN A CADA EQUIPO DESTINATARIO
            foreach ($equiposDestino as $id_equipo_destino) {
                // PREPARAR CONDICIONES WHERE PARA FILTRAR POR EQUIPO
                // sendAllTitle() usa la tabla firebase_app_tokens que tiene rela_equipo
                $whereConditions = [
                    'rela_equipo' => $id_equipo_destino  // Filtrar solo empleados de este equipo
                ];
                
                // PREPARAR ESQUEMA DE DATOS PARA REEMPLAZAR VARIABLES
                // sendAllTitle() espera title_cols y body_cols con los nombres de las columnas
                $dataSchema = [
                    'title_cols' => ['asunto'],           // Columnas para el título: {\$asunto}
                    'body_cols' => ['equipoEmisor']       // Columnas para el cuerpo: {\$equipoEmisor}
                ];
                
                // CONSTRUIR QUERY PERSONALIZADA QUE INCLUYE LAS VARIABLES DINÁMICAS
                // Esta query se une con firebase_app_tokens y firebase_app_msg
                $customQuery = "
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
                        '" . addslashes($asunto) . "' as asunto,
                        '" . addslashes($datosEmisor['alias']) . "' as equipoEmisor
                    FROM firebase_app_tokens d
                    INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                    INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                    WHERE d.activo = 1 
                    AND d.rela_equipo = " . intval($id_equipo_destino) . "
                    AND e.estado = 'habilitado' 
                    AND e.borrado = 0
                ";
                
                // ENVIAR NOTIFICACIÓN USANDO EL SISTEMA EXISTENTE
                // Parámetros: ($pdo, $tema, $where, $dataSchema, $sql)
                sendAllTitle(
                    $pdo,                    // 1. Conexión a la base de datos
                    'SolicitudNueva',        // 2. Tema del template en firebase_app_msg
                    $whereConditions,        // 3. Condiciones WHERE (array)
                    $dataSchema,             // 4. Esquema con title_cols y body_cols
                    $customQuery             // 5. Query personalizada (opcional)
                );
            }
        }
    } catch (Exception $notifError) {
        // SI FALLA LA NOTIFICACIÓN, NO AFECTA EL RESULTADO PRINCIPAL
        // Solo registramos el error en logs del servidor (las solicitudes ya se crearon exitosamente)
        error_log("Error enviando notificaciones: " . $notifError->getMessage());
    }

} catch (Exception $e) {
    if ($transaccionActiva) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Error al crear solicitud: ' . $e->getMessage()]);
}
