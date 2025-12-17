<?php
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();

// Obtener el término de búsqueda de la URL
$termino_busqueda = isset($_GET['termino']) ? $_GET['termino'] : '';

// Consulta preparada para evitar inyecciones SQL - Solo usuarios de la misma entidad
$query = "SELECT id_empleado, nombre, apellido 
          FROM empleados 
          WHERE (nombre LIKE :termino OR apellido LIKE :termino) 
          AND id_entidad = :id_entidad 
          AND borrado = 0 
          AND estado = 'habilitado'";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':termino', '%' . $termino_busqueda . '%', PDO::PARAM_STR);
$stmt->bindValue(':id_entidad', $usuarioActual['id_entidad'], PDO::PARAM_INT);
$stmt->execute();

// Obtener los resultados
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enviar la respuesta JSON
echo json_encode($resultados);
?>