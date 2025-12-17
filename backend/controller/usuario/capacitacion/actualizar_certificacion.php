<?php
require_once "../../../config/database.php";

// Asegurarse de que la solicitud sea POST y el Content-Type sea application/json
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    http_response_code(400);
    echo json_encode(['error' => 'Solicitud incorrecta']);
    exit();
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if ($data === null || !isset($data['id_capacitacion']) || !isset($data['certificacion']) || (!isset($data['id_colaborador']) && !isset($data['id_equipo']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit();
}

$id_capacitacion = $data['id_capacitacion'];
$id_colaborador = isset($data['id_colaborador']) ? $data['id_colaborador'] : null;
$id_equipo = isset($data['id_equipo']) ? $data['id_equipo'] : null;
$certificacion = $data['certificacion'];

try {
    $sql = "UPDATE colaborador_capacitacion SET certificacion = :certificacion WHERE id_capacitacion = :id_capacitacion AND id_colaborador = :id_colaborador";
   // if ($id_colaborador !== null) {
      //  $sql .= " ";
    //} //else {
      //  $sql .= " AND id_equipo = :id_equipo";
    //}

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':certificacion', $certificacion);
    $stmt->bindParam(':id_capacitacion', $id_capacitacion, PDO::PARAM_INT);
 //   if ($id_colaborador !== null) {
        $stmt->bindParam(':id_colaborador', $id_colaborador, PDO::PARAM_INT);
   // } else {
     //   $stmt->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
    //}

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar la certificación en la base de datos']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>