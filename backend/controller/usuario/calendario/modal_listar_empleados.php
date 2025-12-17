<?php
require_once '../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$idEmpleadoLogueado = $_SESSION['usuario']['id'] ?? null;

try {
    $sql = "
        SELECT id_empleado, CONCAT(nombre, ' ', apellido) AS nombre
        FROM empleados
        WHERE estado = 'habilitado' 
          AND borrado = 0
    ";

    $params = [];

    if ($idEmpleadoLogueado) {
        $sql .= " AND id_empleado != :idEmpleado";
        $params[':idEmpleado'] = $idEmpleadoLogueado;
    }

    $sql .= " ORDER BY nombre ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($empleados);
} catch (PDOException $e) {
    echo json_encode([]);
}
