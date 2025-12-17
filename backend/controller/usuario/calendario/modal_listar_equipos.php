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

$idEquipoLogueado = $_SESSION['usuario']['id_equipo'] ?? null;

try {
    $sql = "
        SELECT id_equipo, alias
        FROM equipos
        WHERE estado = 'habilitado' 
          AND borrado = 0
    ";

    $params = [];

    if ($idEquipoLogueado) {
        $sql .= " AND id_equipo != :idEquipo";
        $params[':idEquipo'] = $idEquipoLogueado;
    }

    $sql .= " ORDER BY alias ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($equipos);
} catch (PDOException $e) {
    echo json_encode([]);
}
