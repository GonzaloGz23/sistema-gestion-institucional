<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');
$id_equipox = $usuarioActual->id_equipo;

$id = isset($_GET['idUser']) ? trim($_GET['idUser']) : '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$usuariosQuery = "SELECT
    e.id_empleado,
    e.nombre,
    e.apellido,
    e.id_equipo,
    e.estado,
    eq.alias AS alias_equipo
FROM
    empleados e
JOIN
    equipos eq ON e.id_equipo = eq.id_equipo
WHERE
    e.id_empleado != :idUser AND e.estado = 'habilitado' AND e.borrado = 0";

if (!empty($searchTerm)) {
    $usuariosQuery .= " AND (e.nombre LIKE :searchTerm OR e.apellido LIKE :searchTerm OR eq.alias LIKE :searchTerm)";
}

$stmt = $pdo->prepare($usuariosQuery);
$stmt->bindParam(':idUser', $id, PDO::PARAM_INT);

if (!empty($searchTerm)) {
    $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
}

$stmt->execute();
$contenidousuario = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($contenidousuario);
?>