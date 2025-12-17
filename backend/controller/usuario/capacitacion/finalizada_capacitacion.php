<?php
require_once "../../../config/database.php";

$id = isset($_GET['idUserx']) ? trim($_GET['idUserx']) : '';

// Validar si el ID del usuario es válido (ej. numérico y no vacío)
if (empty($id) || !is_numeric($id)) {
    // Manejar el error, por ejemplo, enviar una respuesta de error o un array vacío
    echo json_encode([]);
    exit; // Terminar la ejecución si el ID no es válido
}

$zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
$fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);

// Obtener el año y el mes actuales en formatoYYYY y MM
$currentYear = $fechaHoraActual->format('Y');
$currentMonth = $fechaHoraActual->format('m'); // 'm' da el mes con cero inicial (ej: 05 para Mayo)


// Consulta para obtener las últimas 6 capacitaciones
// donde el usuario es colaborador, visible es 'no', etc.
$query = "SELECT DISTINCT
            c.`id_capacitacion`,
            c.`fecha-inicio`,
            c.`fecha-fin`,
            c.`modalidad`,
            c.`link`,
            c.`lugar`,
            c.`temas`,
            c.`requerimientos`,
            c.`id_empleado`,
            c.`fecha_creacion`,
            c.`borrar`,
            c.`estado`,
            c.`obligacion`
          FROM `capacitacion` c
          INNER JOIN `colaborador_capacitacion` cc ON c.`id_capacitacion` = cc.`id_capacitacion`
          WHERE c.`borrar`= 'no'
            AND c.`estado` = 'Cerrado'
            AND YEAR(c.`fecha-inicio`) = :currentYear
            AND MONTH(c.`fecha-inicio`) = :currentMonth
            AND cc.`id_colaborador` = :userId
            AND cc.`visible` = 'no'
          ORDER BY c.`fecha-fin` DESC
          LIMIT 6; -- Limitar a los últimos 6 resultados
";

// Preparar y ejecutar la consulta principal
$stmt = $pdo->prepare($query);

// Vincular los parámetros usando bindParam
$stmt->bindParam(':userId', $id, PDO::PARAM_INT);
$stmt->bindParam(':currentYear', $currentYear, PDO::PARAM_INT);
$stmt->bindParam(':currentMonth', $currentMonth, PDO::PARAM_INT);

$stmt->execute();
$Capacitacion = $stmt->fetchAll(PDO::FETCH_ASSOC); // Usar FETCH_ASSOC para mejor manejo

// Procesar cada nota (capacitación)
foreach ($Capacitacion as &$nota) { // Usamos & para modificar el array original
    // Formatear fechas si es necesario
    if (!empty($nota["fecha-inicio"])) {
        $nota["fecha-inicio"] = date("Y-m-d H:i:s", strtotime($nota["fecha-inicio"]));
    }
    if (!empty($nota["fecha-fin"])) {
        $nota["fecha-fin"] = date("Y-m-d H:i:s", strtotime($nota["fecha-fin"]));
    }

    $id_capacitacion = $nota["id_capacitacion"];

    // --- Consulta para obtener los colaboradores (se mantiene igual) ---
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
    WHERE (c.id_equipo IS NOT NULL OR c.id_colaborador IS NOT NULL)
      AND c.id_capacitacion = :id_capacitacion
      AND c.visible = 'si'";

    $stmtCollab = $pdo->prepare($collabQuery);
    $stmtCollab->bindParam(':id_capacitacion', $id_capacitacion, PDO::PARAM_INT);
    $stmtCollab->execute();
    $nota["colaboradores"] = $stmtCollab->fetchAll(PDO::FETCH_ASSOC);
    // --- Fin Consulta colaboradores ---


    // --- Consulta para obtener materiales (se mantiene igual) ---
    $materialQuery = "SELECT `id_material`, `ruta_material`, `nombre_material`, `tamano_material`, `id_capacitacion`
                      FROM `materiales`
                      WHERE `id_capacitacion` = :id_capac";
    $stmtMaterial = $pdo->prepare($materialQuery);
    $stmtMaterial->bindParam(':id_capac', $id_capacitacion, PDO::PARAM_INT);
    $stmtMaterial->execute();
    $nota["materiales"] = $stmtMaterial->fetchAll(PDO::FETCH_ASSOC);
    // --- Fin Consulta materiales ---

}

// Convertir a JSON y enviar la respuesta
header('Content-Type: application/json'); // Asegurar que la respuesta es JSON
echo json_encode($Capacitacion);
?>