<?php
require_once "../../../config/database.php";


header('Content-Type: application/json'); // Indica que la respuesta será JSON

try {
    // Tu consulta para obtener los grupos
    $tipoGrupo = $pdo->prepare("SELECT `id_grupo`, `nombre_grupo` FROM `grupos` WHERE `borrado` = 'no' AND `habilitado` = 'si'");
    $tipoGrupo->execute();

    $grupos = $tipoGrupo->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($grupos); // Codifica el resultado como JSON y lo imprime
} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    http_response_code(500); // Código de estado HTTP para error interno del servidor
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    // Otros errores
    http_response_code(500);
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
}
?>