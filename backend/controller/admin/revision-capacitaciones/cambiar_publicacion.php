<?php
/**
 * Endpoint para cambiar el estado de publicación de una capacitación
 * Cambia el campo esta_publicada entre 0 (no publicada) y 1 (publicada)
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
    if (!isset($data['id_capacitacion']) || !isset($data['esta_publicada'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Parámetros incompletos. Se requiere id_capacitacion y esta_publicada'
        ]);
        exit;
    }

    $id_capacitacion = intval($data['id_capacitacion']);
    $esta_publicada = intval($data['esta_publicada']); // 0 o 1

    // Validar que esta_publicada sea 0 o 1
    if ($esta_publicada !== 0 && $esta_publicada !== 1) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'El valor de esta_publicada debe ser 0 o 1'
        ]);
        exit;
    }

    // Verificar que la capacitación existe
    $stmt = $pdoCourses->prepare("
        SELECT id, esta_publicada, equipo_id 
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

    // Actualizar el estado de publicación
    $stmtUpdate = $pdoCourses->prepare("
        UPDATE capacitaciones 
        SET esta_publicada = ? 
        WHERE id = ?
    ");
    $stmtUpdate->execute([$esta_publicada, $id_capacitacion]);

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $esta_publicada === 1 
            ? 'Capacitación publicada correctamente' 
            : 'Capacitación despublicada correctamente',
        'id_capacitacion' => $id_capacitacion,
        'esta_publicada' => $esta_publicada,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al cambiar estado de publicación: ' . $e->getMessage()
    ]);
}
