<?php
require_once "../../../config/database.php";

if (isset($_GET['noteId'])) {
    $noteId = intval($_GET['noteId']);

    $query = "SELECT c.id_usuario, u.nombre, u.apellido
              FROM collaboradores c
              INNER JOIN empleados u ON c.id_usuario = u.id_empleado
              WHERE c.id_nota = :noteId";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':noteId', $noteId, PDO::PARAM_INT);
    $stmt->execute();
    $collaborators = $stmt->fetchAll();

    if ($collaborators !== false && count($collaborators) > 0) {
        echo json_encode($collaborators);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>