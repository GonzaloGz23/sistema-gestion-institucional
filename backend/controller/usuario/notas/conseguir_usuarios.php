<?php
require_once "../../../config/database.php";
// Consulta para obtener los usuarios visibles
$query = "SELECT id_empleado, nombre, apellido FROM empleados WHERE estado = 'habilitado'";

$stmt = $pdo->query($query);
$users = $stmt->fetchAll();

// Convertir a JSON y enviar la respuesta
echo json_encode($users);
?>