<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

$id_usuariox = $usuarioActual->id;


$checkEquipox = "SELECT `id_equipo` FROM `empleados` WHERE `id_empleado` = ?";
$stmtCheckEquipox = $pdo->prepare($checkEquipox);
$stmtCheckEquipox->execute([$id_usuariox]);
$id_equipo_actual = $stmtCheckEquipox->fetchColumn();



try {
    $query = "SELECT `id_equipo`, `alias` FROM `equipos` WHERE estado = 'habilitado' AND borrado = 0 AND id_equipo != $id_equipo_actual";
    $stmt = $pdo->query($query);
    $equipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($equipos);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>