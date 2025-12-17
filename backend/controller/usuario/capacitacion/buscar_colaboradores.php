<?php
require_once "../../../config/database.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$id = isset($_GET['idUser']) ? trim($_GET['idUser']) : '';

$query = "SELECT id_empleado, nombre, apellido FROM empleados WHERE id_empleado != $id AND estado = 'habilitado' AND borrado = 0";
if ($search !== '') {
    $query .= " AND nombre LIKE :search_nombre OR apellido LIKE :search_apellido";
}

try {
    $stmt = $pdo->prepare($query);
    if ($search !== '') {
        $searchParam = "%$search%";
        $stmt->bindParam(':search_nombre', $searchParam);
        $stmt->bindParam(':search_apellido', $searchParam);
    }
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    echo json_encode($usuarios);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>