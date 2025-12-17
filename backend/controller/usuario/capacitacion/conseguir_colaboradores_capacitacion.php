<?php
require_once "../../../config/database.php";

$id_capacitacion = isset($_GET['id']) ? trim($_GET['id']) : '';

if (!empty($id_capacitacion) && is_numeric($id_capacitacion)) {
    $collabQuery = "SELECT
    cc.visible,
     cc.id_colaborador AS id_empleado, -- <-- Añadir el ID del colaborador (empleado)
        cc.id_equipo AS id_equipo,       -- <-- Añadir el ID del equipo
        CASE
            WHEN cc.id_equipo IS NOT NULL THEN eq.alias
            ELSE NULL
        END AS equipo_alias,
        CASE
            WHEN cc.id_colaborador IS NOT NULL THEN em.nombre
            ELSE NULL
        END AS empleado_nombre,
        CASE
            WHEN cc.id_colaborador IS NOT NULL THEN em.apellido
            ELSE NULL
        END AS empleado_apellido
    FROM colaborador_capacitacion cc
    LEFT JOIN equipos eq ON cc.id_equipo = eq.id_equipo
    LEFT JOIN empleados em ON cc.id_colaborador = em.id_empleado
    WHERE cc.id_capacitacion = :id_capacitacion AND cc.visible = 'si'";

    $stmtCollab = $pdo->prepare($collabQuery);
    $stmtCollab->bindParam(':id_capacitacion', $id_capacitacion, PDO::PARAM_INT);
    $stmtCollab->execute();
    $colaboradores = $stmtCollab->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($colaboradores);
} else {
    echo json_encode([]);
}
?>