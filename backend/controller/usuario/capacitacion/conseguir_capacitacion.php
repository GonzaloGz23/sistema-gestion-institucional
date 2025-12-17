<?php
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();

// Validar y sanitizar parámetros de entrada
$id = isset($_GET['idUserx']) ? intval(trim($_GET['idUserx'])) : 0;

// Validar que el ID sea válido y que sea el usuario actual (seguridad)
if ($id <= 0 || $id !== $usuarioActual['id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}



// Consulta preparada para obtener las capacitaciones (más segura)
$query = "SELECT `id_capacitacion`, `fecha-inicio`, `fecha-fin`, `modalidad`, `link`, `lugar`, `temas`, `requerimientos`, 
          `id_empleado`, `fecha_creacion`, `borrar`, `estado`, `obligacion` 
          FROM `capacitacion` 
          WHERE `id_empleado` = :id_empleado AND `borrar` = 'no' 
          ORDER BY `id_capacitacion` DESC";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':id_empleado', $id, PDO::PARAM_INT);
$stmt->execute();
$Capacitacion = $stmt->fetchAll();

// Procesar cada nota
foreach ($Capacitacion as &$nota) {
    if (!empty($nota["fecha-inicio"])) {
        $nota["fecha-inicio"] = date("Y-m-d H:i:s", strtotime($nota["fecha-inicio"]));
    }
    if (!empty($nota["fecha-fin"])) {
        $nota["fecha-fin"] = date("Y-m-d H:i:s", strtotime($nota["fecha-fin"]));
    }

    $id_capacitacion = $nota["id_capacitacion"];
    // Consulta para obtener los colaboradores
   $collabQuery = "SELECT
c.visible,
    CASE
     WHEN c.id_equipo IS NOT NULL THEN e.alias
        ELSE NULL
    END AS equipo_alias,
    CASE
        WHEN c.id_colaborador IS NOT NULL THEN u.nombre
        ELSE NULL
    END AS empleado_nombre,
    CASE
        WHEN c.id_colaborador IS NOT NULL THEN u.apellido
        ELSE NULL
    END AS empleado_apellido
FROM colaborador_capacitacion c
LEFT JOIN equipos e ON c.id_equipo = e.id_equipo
LEFT JOIN empleados u ON c.id_colaborador = u.id_empleado
WHERE (c.id_equipo IS NOT NULL OR c.id_colaborador IS NOT NULL) AND c.id_capacitacion = :id_capacitacion AND c.visible = 'si'";

    $stmtCollab = $pdo->prepare($collabQuery);
    $stmtCollab->bindParam(':id_capacitacion', $id_capacitacion, PDO::PARAM_INT);
    $stmtCollab->execute();
    $nota["colaboradores"] = $stmtCollab->fetchAll();



$materialQuery = "SELECT `id_material`, `ruta_material`, `nombre_material`, `tamano_material`, `id_capacitacion` FROM `materiales` WHERE `id_capacitacion` = :id_capac";
$stmtMaterial = $pdo->prepare($materialQuery);
$stmtMaterial->bindParam(':id_capac', $id_capacitacion, PDO::PARAM_INT);
$stmtMaterial->execute();
$nota["materiales"] = $stmtMaterial->fetchAll();


}

// Convertir a JSON y enviar la respuesta
echo json_encode($Capacitacion);
?>
