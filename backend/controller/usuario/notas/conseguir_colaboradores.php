<?php
require_once "../../../config/database.php";
$id = isset($_GET['idUser']) ? trim($_GET['idUser']) : '';

// Obtener el ID de la nota de la URL, con validación
$id_nota = isset($_GET['id_nota']) ? intval($_GET['id_nota']) : 0;

// Consulta para colaboradores actuales (query)
$query = "SELECT u.id_empleado,u.nombre, u.apellido 
          FROM collaboradores c
          JOIN empleados u ON c.id_usuario = u.id_empleado
          WHERE c.id_nota = :id_nota";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
$stmt->execute();
$currentCollaborators = $stmt->fetchAll();

// Consulta para colaboradores disponibles (query2)
$query2 = "SELECT u.id_empleado,u.nombre, u.apellido
           FROM empleados u
           WHERE u.id_empleado NOT IN (
               SELECT c.id_usuario
               FROM collaboradores c
               WHERE c.id_nota = :id_notas 
           ) AND u.id_empleado != $id";

$stmt2 = $pdo->prepare($query2);
$stmt2->bindParam(':id_notas', $id_nota, PDO::PARAM_INT);
$stmt2->execute();
$availableCollaborators = $stmt2->fetchAll();

// Enviar la respuesta JSON con ambas consultas
echo json_encode([
    'currentCollaborators' => $currentCollaborators,
    'availableCollaborators' => $availableCollaborators
]);
?>