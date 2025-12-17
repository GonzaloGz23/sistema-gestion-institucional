<?php
/**
 * Controlador para obtener categorías específicas por categoría general
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
    
    // Obtener y validar parámetro
    $categoriaGeneralId = filter_input(INPUT_GET, 'categoria_general_id', FILTER_VALIDATE_INT);
    
    if (!$categoriaGeneralId || $categoriaGeneralId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de categoría general inválido'
        ]);
        exit;
    }
    
    // Verificar que la categoría general existe
    $verificarQuery = "SELECT COUNT(*) as count FROM categorias_generales WHERE id = :id";
    $stmtVerificar = $pdoCourses->prepare($verificarQuery);
    $stmtVerificar->bindParam(':id', $categoriaGeneralId, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $existe = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($existe['count'] == 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Categoría general no encontrada'
        ]);
        exit;
    }
    
    // Obtener categorías específicas
    $query = "
        SELECT id, nombre 
        FROM categorias_especificas 
        WHERE categoria_general_id = :categoria_general_id 
        ORDER BY nombre ASC
    ";
    $stmt = $pdoCourses->prepare($query);
    $stmt->bindParam(':categoria_general_id', $categoriaGeneralId, PDO::PARAM_INT);
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
        'total' => count($categoriasProcesadas),
        'categoria_general_id' => $categoriaGeneralId
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_especificas.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage()
    ]);
}
?>
