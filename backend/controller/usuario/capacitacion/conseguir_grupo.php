<?php
require_once "../../../config/database.php";

$id = isset($_GET['idUserx']) ? trim($_GET['idUserx']) : '';



// Consulta para obtener las notas
$query = "SELECT `id_grupo`, `nombre_grupo`, `fecha_creacion`, `tipo_grupo`, `id_creador`, `borrado`, `habilitado` FROM `grupos` WHERE `id_creador` = $id AND `borrado`= 'no' ORDER BY `id_grupo` DESC;";

$stmt = $pdo->query($query);
$grupos = $stmt->fetchAll();

// Procesar cada nota
foreach ($grupos as &$nota) {
    if (!empty($nota["fecha_creacion"])) {
        $nota["fecha_creacion"] = date("Y-m-d H:i:s", strtotime($nota["fecha_creacion"]));
    }
  

    $id_grupo = $nota["id_grupo"];

    // Consulta para obtener los colaboradores
   $collabQuery = "SELECT c.id_grupo_empleado, c.id_grupo, c.id_empleado, e.alias, u.nombre, u.apellido FROM  r_grupos_empleados c
LEFT JOIN empleados u ON c.id_empleado = u.id_empleado
LEFT JOIN equipos e ON u.id_equipo = e.id_equipo
WHERE c.id_grupo = :id_grup";

    $stmtCollab = $pdo->prepare($collabQuery);
    $stmtCollab->bindParam(':id_grup', $id_grupo, PDO::PARAM_INT);
    $stmtCollab->execute();
    $nota["colaboradores"] = $stmtCollab->fetchAll();


}

// Convertir a JSON y enviar la respuesta
echo json_encode($grupos);
?>
