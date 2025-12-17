<?php

/**
 * Controlador para cambiar estado de capacitaciones
 * Archivo: backend/controller/admin/revision-capacitaciones/cambiar_estado.php
 * 
 * Maneja el workflow de estados: en_espera → en_revision → aprobado
 * Permite también retrocesos manuales según la lógica de negocio
 */

// Incluir configuraciones necesarias
require_once '../../../config/database_courses.php';
require_once '../../../config/database.php';
require_once '../../../config/session_config.php';
require_once '../../../../pages/common/functions.php';


// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Verificar que el usuario esté autenticado
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

try {
    // Obtener usuario actual
    $usuarioActual = obtenerUsuarioActual();

    if (!$usuarioActual) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener datos del usuario'
        ]);
        exit;
    }

    // Obtener y validar datos de entrada
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $capacitacionId = isset($input['id']) ? (int)$input['id'] : null;
    $nuevoEstado = isset($input['estado']) ? trim($input['estado']) : null;

    // Validar parámetros obligatorios
    if (!$capacitacionId || !$nuevoEstado) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Faltan parámetros obligatorios: id y estado'
        ]);
        exit;
    }

    // Estados permitidos y mapeo a IDs
    $estadosPermitidos = [
        'en_espera' => 2,
        'en_revision' => 3,
        'aprobado' => 4
    ];

    // Mapeo inverso para obtener nombre por ID
    $estadosPorId = [
        2 => 'en espera',
        3 => 'en revisión',
        4 => 'aprobado'
    ];

    if (!array_key_exists($nuevoEstado, $estadosPermitidos)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Estado no válido. Estados permitidos: ' . implode(', ', array_keys($estadosPermitidos))
        ]);
        exit;
    }

    $nuevoEstadoId = $estadosPermitidos[$nuevoEstado];

    // Verificar que la capacitación existe y no está eliminada
    $sqlVerificar = "
        SELECT c.id, c.estado_id, c.equipo_id, ec.nombre as estado_nombre
        FROM capacitaciones c
        INNER JOIN estados_capacitacion ec ON c.estado_id = ec.id
        WHERE c.id = :capacitacion_id 
        AND c.esta_eliminada = 0
    ";

    $stmtVerificar = $pdoCourses->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $capacitacion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Capacitación no encontrada'
        ]);
        exit;
    }

    $estadoActualId = $capacitacion['estado_id'];
    $estadoActualNombre = $capacitacion['estado_nombre'];

    // Verificar si el cambio es necesario
    if ($estadoActualId === $nuevoEstadoId) {
        echo json_encode([
            'success' => true,
            'message' => 'La capacitación ya se encuentra en el estado solicitado',
            'estado_actual' => $nuevoEstado
        ]);
        exit;
    }

    // Mapeo de estados para mensajes descriptivos
    $estadosTexto = [
        'en_espera' => 'En Espera',
        'en_revision' => 'En Revisión',
        'aprobado' => 'Aprobado'
    ];

    // Mapeo para estados de BD (con espacios)
    $estadosBDTexto = [
        'en espera' => 'En Espera',
        'en revisión' => 'En Revisión',
        'aprobado' => 'Aprobado',
        'borrador' => 'Borrador',
        'cerrado' => 'Cerrado'
    ];

    // Iniciar transacción para asegurar consistencia
    $pdoCourses->beginTransaction();

    try {
        // Actualizar estado de la capacitación
        $sqlActualizar = "
            UPDATE capacitaciones 
            SET estado_id = :nuevo_estado_id
            WHERE id = :capacitacion_id
        ";

        $stmtActualizar = $pdoCourses->prepare($sqlActualizar);
        $stmtActualizar->bindParam(':nuevo_estado_id', $nuevoEstadoId, PDO::PARAM_INT);
        $stmtActualizar->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);

        if (!$stmtActualizar->execute()) {
            throw new Exception('Error al actualizar el estado de la capacitación');
        }

        // Verificar que se actualizó correctamente
        if ($stmtActualizar->rowCount() === 0) {
            throw new Exception('No se pudo actualizar el estado de la capacitación');
        }

        // Registrar el cambio en log de estados (si existe tabla de auditoría)
        $sqlVerificarLog = "SHOW TABLES LIKE 'estados_log'";
        $resultLog = $pdoCourses->query($sqlVerificarLog);

        if ($resultLog && $resultLog->rowCount() > 0) {
            $sqlLog = "
                INSERT INTO estados_log (capacitacion_id, estado_anterior_id, estado_nuevo_id, usuario_id, fecha_cambio)
                VALUES (:capacitacion_id, :estado_anterior_id, :estado_nuevo_id, :usuario_id, NOW())
            ";

            $stmtLog = $pdoCourses->prepare($sqlLog);
            $stmtLog->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);
            $stmtLog->bindParam(':estado_anterior_id', $estadoActualId, PDO::PARAM_INT);
            $stmtLog->bindParam(':estado_nuevo_id', $nuevoEstadoId, PDO::PARAM_INT);
            $stmtLog->bindParam(':usuario_id', $usuarioActual['id'], PDO::PARAM_INT);
            $stmtLog->execute();
        }

        // Confirmar transacción
        $pdoCourses->commit();
        /* $idEquipo = $usuarioActual['id_equipo'];
        if ($idEquipo != 10 || $idEquipo != 11) {
            $sqlConsutl = "SELECT * FROM `capacitaciones` WHERE id = :capacitacion_id ";
            $stmtConsutl = $pdoCourses->prepare($sqlConsutl);
            $stmtConsutl->bindParam(':capacitacion_id', $capacitacionId, PDO::PARAM_INT);

            $stmtConsutl->execute();


            foreach ($stmtConsutl as $fila) {
                $titulo = $fila['nombre'];
                echo $titulo;
            }

            echo $titulo;
            $obj = $pdo;

            sendAll($obj, "EnviarCapacitacion_a_comunicacion", array(
                "rela_equipo" => "11,10"

            ), array(
                "{capacitacion}" => $titulo
            ));
        } */
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => sprintf(
                'Estado cambiado exitosamente de "%s" a "%s"',
                $estadosBDTexto[$estadoActualNombre] ?? ucfirst($estadoActualNombre),
                $estadosTexto[$nuevoEstado] ?? ucfirst($nuevoEstado)
            ),
            'estado_anterior' => $estadoActualNombre,
            'estado_nuevo' => $estadosPorId[$nuevoEstadoId],
            'estado_nuevo_frontend' => $nuevoEstado, // Para que el frontend use este valor
            'capacitacion_id' => $capacitacionId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Rollback en caso de error
        $pdoCourses->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Log del error (opcional)
    error_log("Error en cambiar_estado.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} finally {
    // Las conexiones PDO se cierran automáticamente
}
