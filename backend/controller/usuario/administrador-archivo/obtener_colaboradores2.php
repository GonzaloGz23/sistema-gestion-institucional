<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

$idCarpeta = $_GET['idCarpeta'];
$tipo = $_GET['tipo'];

if($tipo == 'archivo') {
    $query = "SELECT cc.id_colaborador, cc.nombre_colaborador, cc.apellido_colaborador FROM colaborador_carpetas cc WHERE cc.id_archivo = :idCarpeta";

}
    if($tipo == 'carpeta') {
$query = "SELECT cc.id_colaborador, cc.nombre_colaborador, cc.apellido_colaborador FROM colaborador_carpetas cc WHERE cc.id_carpeta = :idCarpeta";
    }
$stmt = $pdo->prepare($query);
$stmt->bindParam(':idCarpeta', $idCarpeta, PDO::PARAM_INT);
$stmt->execute();
$colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($colaboradores);
?>