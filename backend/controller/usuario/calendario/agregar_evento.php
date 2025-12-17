<?php
require_once '../../../config/database.php';
require_once '../../../../pages/common/functions.php';

try {
    // Validaciones obligatorias
    if (
        empty($_POST['title']) ||
        empty($_POST['description']) ||
        empty($_POST['tipo-evento']) ||
        empty($_POST['color']) ||
        empty($_POST['start']) ||
        empty($_POST['end']) ||
        empty($_POST['id_creador']) // Nuevo obligatorio
    ) {
        throw new Exception("Faltan datos obligatorios.");
    }

    $titulo = trim($_POST['title']);
    $descripcion = trim($_POST['description']);
    $tipoEvento = $_POST['tipo-evento'];
    $color = $_POST['color'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $idCreador = (int) $_POST['id_creador'];
    $idEquipoCreador = isset($_POST['id_equipo_creador']) && $_POST['id_equipo_creador'] !== '' 
        ? (int) $_POST['id_equipo_creador'] 
        : null;

    // Validar tipo de evento permitido
    $tiposValidos = ['Institucional', 'Equipo', 'Individual', 'Personalizado'];
    if (!in_array($tipoEvento, $tiposValidos)) {
        throw new Exception("Tipo de evento no válido.");
    }

    // Validar fechas
    $inicio = new DateTime($start);
    $fin = new DateTime($end);

    if ($inicio >= $fin) {
        throw new Exception("La fecha de inicio debe ser anterior a la de fin.");
    }

    $fechaCreacion = date('Y-m-d H:i:s');

    // Insertar evento
    $sql = "INSERT INTO eventos (titulo, descripcion, tipo_evento, color, start, end, fecha_creacion, id_creador, id_equipo_creador, borrado)
            VALUES (:titulo, :descripcion, :tipo_evento, :color, :start, :end, :fecha_creacion, :id_creador, :id_equipo_creador, 0)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':tipo_evento' => $tipoEvento,
        ':color' => $color,
        ':start' => $inicio->format('Y-m-d H:i:s'),
        ':end' => $fin->format('Y-m-d H:i:s'),
        ':fecha_creacion' => $fechaCreacion,
        ':id_creador' => $idCreador,
        ':id_equipo_creador' => $idEquipoCreador
    ]);

    $idEvento = $pdo->lastInsertId();

    // Insertar asignaciones SOLO si es personalizado
    if ($tipoEvento === 'Personalizado') {
        if (!isset($_POST['personalizadoTipo'])) {
            throw new Exception("Debe seleccionar si el evento es para empleados o equipos.");
        }

        $tipoAsignacion = $_POST['personalizadoTipo'];

        if ($tipoAsignacion === 'empleados') {
            if (empty($_POST['empleadosSeleccionados'])) {
                throw new Exception("No se seleccionaron empleados.");
            }
            $empleados = array_unique($_POST['empleadosSeleccionados']); // Evitar duplicados

            foreach ($empleados as $idEmpleado) {
                $stmtAsignacion = $pdo->prepare("
                    INSERT INTO eventos_asignaciones (id_evento, id_empleado)
                    VALUES (:id_evento, :id_empleado)
                ");
                $stmtAsignacion->execute([
                    ':id_evento' => $idEvento,
                    ':id_empleado' => $idEmpleado
                ]);
            }

        } elseif ($tipoAsignacion === 'equipos') {
            if (empty($_POST['equiposSeleccionados'])) {
                throw new Exception("No se seleccionaron equipos.");
            }
            $equipos = array_unique($_POST['equiposSeleccionados']); // Evitar duplicados

            foreach ($equipos as $idEquipo) {
                $stmtAsignacion = $pdo->prepare("
                    INSERT INTO eventos_asignaciones (id_evento, id_equipo)
                    VALUES (:id_evento, :id_equipo)
                ");
                $stmtAsignacion->execute([
                    ':id_evento' => $idEvento,
                    ':id_equipo' => $idEquipo
                ]);
            }

        } else {
            throw new Exception("Tipo de asignación inválido.");
        }
    }

    // ENVIAR NOTIFICACIONES (solo si no es evento Individual)
    if ($tipoEvento !== 'Individual') {
        try {
            // Obtener información del creador
            $stmtCreador = $pdo->prepare("SELECT nombre, apellido FROM empleados WHERE id_empleado = ?");
            $stmtCreador->execute([$idCreador]);
            $creador = $stmtCreador->fetch(PDO::FETCH_ASSOC);
            $nombreCreador = $creador ? $creador['nombre'] . ' ' . $creador['apellido'] : 'Usuario';
            
            // Formatear fecha del evento
            $fechaEvento = $inicio->format('d/m/Y H:i');
            
            // Definir esquema de datos para reemplazar variables en el template
            $dataSchema = [
                "title_cols" => ["titulo_evento"],
                "body_cols" => ["nombre_creador", "tipo_evento", "fecha_evento"]
            ];
            
            if ($tipoEvento === 'Institucional') {
                // Notificar a todos los empleados activos (excluyendo al creador)
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
                        '" . addslashes($titulo) . "' as titulo_evento,
                        '" . addslashes($nombreCreador) . "' as nombre_creador,
                        '" . addslashes($tipoEvento) . "' as tipo_evento,
                        '" . addslashes($fechaEvento) . "' as fecha_evento
                    FROM firebase_app_tokens d
                    INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                    INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                    WHERE d.activo = 1 
                    AND e.estado = 'habilitado' 
                    AND e.borrado = 0
                    AND d.rela_usuario != " . intval($idCreador) . "
                ";
                
                sendAllTitle($pdo, 'CalendarioNuevoEvento', [], $dataSchema, $sqlCustom);
                
            } elseif ($tipoEvento === 'Equipo' && $idEquipoCreador) {
                // Notificar solo a miembros del equipo (excluyendo al creador)
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
                        '" . addslashes($titulo) . "' as titulo_evento,
                        '" . addslashes($nombreCreador) . "' as nombre_creador,
                        '" . addslashes($tipoEvento) . "' as tipo_evento,
                        '" . addslashes($fechaEvento) . "' as fecha_evento
                    FROM firebase_app_tokens d
                    INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                    INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                    WHERE d.activo = 1 
                    AND d.rela_equipo = " . intval($idEquipoCreador) . "
                    AND e.estado = 'habilitado' 
                    AND e.borrado = 0
                    AND d.rela_usuario != " . intval($idCreador) . "
                ";
                
                sendAllTitle($pdo, 'CalendarioNuevoEvento', [], $dataSchema, $sqlCustom);
                
            } elseif ($tipoEvento === 'Personalizado') {
                // Notificar a empleados o equipos asignados específicamente
                if (isset($tipoAsignacion) && $tipoAsignacion === 'empleados' && !empty($empleados)) {
                    $empleadosIds = implode(',', array_map('intval', $empleados));
                    
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
                            '" . addslashes($titulo) . "' as titulo_evento,
                            '" . addslashes($nombreCreador) . "' as nombre_creador,
                            '" . addslashes($tipoEvento) . "' as tipo_evento,
                            '" . addslashes($fechaEvento) . "' as fecha_evento
                        FROM firebase_app_tokens d
                        INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                        INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                        WHERE d.activo = 1 
                        AND d.rela_usuario IN ($empleadosIds)
                        AND e.estado = 'habilitado' 
                        AND e.borrado = 0
                        AND d.rela_usuario != " . intval($idCreador) . "
                    ";
                    
                    sendAllTitle($pdo, 'CalendarioNuevoEvento', [], $dataSchema, $sqlCustom);
                    
                } elseif (isset($tipoAsignacion) && $tipoAsignacion === 'equipos' && !empty($equipos)) {
                    $equiposIds = implode(',', array_map('intval', $equipos));
                    
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
                            '" . addslashes($titulo) . "' as titulo_evento,
                            '" . addslashes($nombreCreador) . "' as nombre_creador,
                            '" . addslashes($tipoEvento) . "' as tipo_evento,
                            '" . addslashes($fechaEvento) . "' as fecha_evento
                        FROM firebase_app_tokens d
                        INNER JOIN empleados e ON e.id_empleado = d.rela_usuario
                        INNER JOIN firebase_app_msg ff ON ff.tema = :tema
                        WHERE d.activo = 1 
                        AND d.rela_equipo IN ($equiposIds)
                        AND e.estado = 'habilitado' 
                        AND e.borrado = 0
                        AND d.rela_usuario != " . intval($idCreador) . "
                    ";
                    
                    sendAllTitle($pdo, 'CalendarioNuevoEvento', [], $dataSchema, $sqlCustom);
                }
            }
            
        } catch (Exception $notifError) {
            // Log silencioso de errores de notificaciones para no interrumpir el flujo principal
            error_log("Error en notificación de evento: " . $notifError->getMessage());
        }
    }

    // Redirigir
    header("Location: ../../../../pages/user/calendario.php");
    exit;

} catch (Exception $e) {
    echo "Error al guardar el evento: " . $e->getMessage();
}
?>
