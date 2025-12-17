<?php
require_once "../../../config/database.php";

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!empty($id) && is_numeric($id)) {
    $query = "SELECT `id_capacitacion`, `fecha-inicio`, `fecha-fin`, `modalidad`, `link`, `lugar`, `temas`, `requerimientos`, `obligacion` FROM `capacitacion` WHERE `id_capacitacion` = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $capacitacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($capacitacion as &$nota) {
        $id_capacitacion = $nota["id_capacitacion"];
        $lugar_capacitacion = $nota["lugar"];

        $materialQuery = "SELECT `id_material`, `ruta_material`, `nombre_material` FROM `materiales` WHERE `id_capacitacion` = :id_capac";
        $stmtMaterial = $pdo->prepare($materialQuery);
        $stmtMaterial->bindParam(':id_capac', $id_capacitacion, PDO::PARAM_INT);
        $stmtMaterial->execute();
        $nota["materiales"] = $stmtMaterial->fetchAll(PDO::FETCH_ASSOC);

        $equipoLista = "SELECT DISTINCT `id_edificio`, `alias`, `direccion`, `id_entidad`, `estado`, `borrado` FROM `edificios` WHERE `borrado` = 0 AND `estado` = 'habilitado' AND `direccion` != '$lugar_capacitacion'";
        $stmtEquipo = $pdo->prepare($equipoLista);
        $stmtEquipo->execute();
        $nota["edificio"] = $stmtEquipo->fetchAll(PDO::FETCH_ASSOC);

    }

    echo json_encode($capacitacion);
} else {
    echo json_encode([]); // Devolver un array vacío si no se proporciona un ID válido
}
?>