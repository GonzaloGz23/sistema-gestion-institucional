<?php
/**
 * Endpoint para cambiar el estado de destacado de una capacitación
 * Cambia el campo es_destacado entre 0 (no destacada) y 1 (destacada)
 */

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database_courses.php';
require_once __DIR__ . '/../../../config/usuario_actual.php';

// Verificar autenticación
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'No autenticado'
    ]);
    exit;
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

try {
    // Leer datos JSON del cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }

    // Validar parámetros requeridos
    if (!isset($data['id_capacitacion']) || !isset($data['es_destacado'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Parámetros incompletos. Se requiere id_capacitacion y es_destacado'
        ]);
        exit;
    }

    $id_capacitacion = intval($data['id_capacitacion']);
    $es_destacado = intval($data['es_destacado']); // 0 o 1

    // Validar que es_destacado sea 0 o 1
    if ($es_destacado !== 0 && $es_destacado !== 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El valor de es_destacado debe ser 0 o 1'
        ]);
        exit;
    }

    // Verificar que la capacitación existe
    $stmt = $pdoCourses->prepare("
        SELECT id, es_destacado, equipo_id 
        FROM capacitaciones 
        WHERE id = ? AND esta_eliminada = 0
    ");
    $stmt->execute([$id_capacitacion]);
    $capacitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Capacitación no encontrada o fue eliminada'
        ]);
        exit;
    }

    // Actualizar el estado de destacado
    $stmtUpdate = $pdoCourses->prepare("
        UPDATE capacitaciones 
        SET es_destacado = ? 
        WHERE id = ?
    ");
    $stmtUpdate->execute([$es_destacado, $id_capacitacion]);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $es_destacado === 1 
            ? 'Capacitación marcada como destacada correctamente' 
            : 'Capacitación desmarcada como destacada correctamente',
        'id_capacitacion' => $id_capacitacion,
        'es_destacado' => $es_destacado,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al cambiar estado de destacado: ' . $e->getMessage()
    ]);
}
