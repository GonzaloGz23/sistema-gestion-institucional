<?php
require_once "../../../config/database.php";
require_once "../../../config/usuario_actual.php";

$id_usuariox = $usuarioActual->id;


$id_capacitacion = isset($_GET['idUserx']) ? trim($_GET['idUserx']) : '';



$collabQuery = "SELECT
    cc.visible,
    emp.id_empleado AS id_colaborador,
    cc.certificacion,
    e.id_equipo AS id_equip,
    e.alias AS equipo_alias,
    emp.nombre AS empleado_nombre,
    emp.apellido AS empleado_apellido
FROM colaborador_capacitacion cc
JOIN equipos e ON cc.id_equipo = e.id_equipo
JOIN empleados emp ON e.id_equipo = emp.id_equipo
WHERE cc.id_equipo IS NOT NULL AND cc.id_capacitacion = $id_capacitacion AND cc.visible = 'no' AND emp.id_empleado != $id_usuariox AND emp.`borrado` = 0 AND emp.`estado` = 'habilitado'

UNION ALL

SELECT
    c.visible,
    u.id_empleado AS id_colaborador,
    c.certificacion,
    u.id_equipo AS id_equip,
    eq.alias AS equipo_alias,
    u.nombre AS empleado_nombre,
    u.apellido AS empleado_apellido
FROM colaborador_capacitacion c
LEFT JOIN empleados u ON c.id_colaborador = u.id_empleado
LEFT JOIN equipos eq ON u.id_equipo = eq.id_equipo
WHERE c.id_equipo IS NULL AND c.id_colaborador IS NOT NULL AND c.id_capacitacion = $id_capacitacion AND c.visible = 'no' AND u.id_empleado != $id_usuariox AND u.`borrado` = 0 AND u.`estado` = 'habilitado';";

$stmtCollab = $pdo->prepare($collabQuery);
//$stmtCollab->bindParam(':id_capacitacion', $id_capacitacion, PDO::PARAM_INT);
$stmtCollab->execute();
$empleados = $stmtCollab->fetchAll();

// Procesar cada nota
foreach ($empleados as &$nota) {
  

}

// Convertir a JSON y enviar la respuesta
echo json_encode($empleados);
?>
