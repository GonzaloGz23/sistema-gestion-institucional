<?php
/**
 * Controlador para obtener subcategorías por categoría específica
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
    $categoriaEspecificaId = filter_input(INPUT_GET, 'categoria_especifica_id', FILTER_VALIDATE_INT);
    
    if (!$categoriaEspecificaId || $categoriaEspecificaId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID de categoría específica inválido'
        ]);
        exit;
    }
    
    // Verificar que la categoría específica existe
    $verificarQuery = "SELECT COUNT(*) as count FROM categorias_especificas WHERE id = :id";
    $stmtVerificar = $pdoCourses->prepare($verificarQuery);
    $stmtVerificar->bindParam(':id', $categoriaEspecificaId, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $existe = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($existe['count'] == 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Categoría específica no encontrada'
        ]);
        exit;
    }
    
    // Obtener subcategorías
    $query = "
        SELECT id, nombre 
        FROM subcategorias 
        WHERE categoria_especifica_id = :categoria_especifica_id 
        ORDER BY nombre ASC
    ";
    $stmt = $pdoCourses->prepare($query);
    $stmt->bindParam(':categoria_especifica_id', $categoriaEspecificaId, PDO::PARAM_INT);
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
        'categoria_especifica_id' => $categoriaEspecificaId
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_subcategorias.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'debug' => $e->getMessage()
    ]);
}
?>
