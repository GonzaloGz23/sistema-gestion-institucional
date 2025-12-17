<?php
/**
 * Controlador para obtener categorías generales
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Incluir configuraciones
    require_once '../../../config/database_courses.php';
    require_once '../../../config/session_config.php';
    
    // Verificar autenticación
    if (!verificarUsuarioAutenticado()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario no autenticado'
        ]);
        exit;
    }
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        exit;
    }
    
    // Obtener categorías generales
    $query = "SELECT id, nombre FROM categorias_generales ORDER BY nombre ASC";
    $stmt = $pdoCourses->prepare($query);
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Convertir IDs a enteros
    $categoriasProcesadas = array_map(function($categoria) {
        return [
            'id' => (int)$categoria['id'],
            'nombre' => $categoria['nombre']
        ];
    }, $categorias);
    
    echo json_encode([
        'success' => true,
        'categorias' => $categoriasProcesadas,
        'total' => count($categoriasProcesadas)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_generales.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage()
    ]);
}
?>
