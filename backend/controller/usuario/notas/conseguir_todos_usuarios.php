<?php
require_once "../../../config/database.php";
$id = isset($_GET['idUser']) ? trim($_GET['idUser']) : '';



// Consulta para obtener los usuarios
$query = "SELECT id_empleado, nombre, apellido FROM empleados WHERE id_empleado != $id";

try {
    $stmt = $pdo->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtener los resultados como un array asociativo

    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode([]); // Devolver un array vacío si no hay resultados
    }
} catch (PDOException $e) {
    echo json_encode([]); // Devolver un array vacío en caso de error
}
?>