<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');
$id = isset($_GET['idUser']) ? trim($_GET['idUser']) : '';

$idCarpeta = $_GET['idCarpeta'];

$query = "SELECT cc.id_colaborador, cc.nombre_colaborador, cc.apellido_colaborador,
    eq.alias AS alias_equipo FROM colaborador_carpetas cc JOIN
    empleados emp ON cc.id_colaborador = emp.id_empleado
JOIN
    equipos eq ON emp.id_equipo = eq.id_equipo WHERE cc.id_carpeta = :idCarpeta AND cc.id_colaborador != $id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':idCarpeta', $idCarpeta, PDO::PARAM_INT);
$stmt->execute();
$colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($colaboradores);
?>