<?php
include '../../../../backend/config/database.php';

$idSolicitudRH = $_POST['id_solicitud_rh'] ?? null;
$idFormulario = $_POST['id_form'] ?? null;
$idEmpleado = $_POST['id_empleado'] ?? null;

if (!$idSolicitudRH || !$idFormulario || !$idEmpleado) {
    echo '<p class="text-danger">Faltan datos para mostrar la solicitud.</p>';
    exit;
}

// Obtener datos del formulario
$stmt = $pdo->prepare("
    SELECT 
        p.id_preguntas, 
        p.preguntas, 
        fr.respuesta, 
        fr.archivo, 
        fr.tipo_archivo, 
        p.id_tipo_campo, 
        op.opcion 
    FROM formulario_respuestas fr
    INNER JOIN preguntas p ON p.id_preguntas = fr.id_preguntas
    LEFT JOIN opcion_preguntas op ON op.id_opcion_preguntas = fr.id_opcion_preguntas
    WHERE fr.id_solicitud_rh = ?
    ORDER BY p.orden ASC
");
$stmt->execute([$idSolicitudRH]);

$respuestas = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $idPregunta = $row['id_preguntas'];

    if (!isset($respuestas[$idPregunta])) {
        $respuestas[$idPregunta] = [
            'id_preguntas' => $row['id_preguntas'],
            'preguntas' => $row['preguntas'],
            'id_tipo_campo' => $row['id_tipo_campo'],
            'archivo' => $row['archivo'],
            'tipo_archivo' => $row['tipo_archivo'],
            'respuestas' => [],
            'opciones' => []
        ];
    }

    // Agrupar respuestas y opciones
    $respuestas[$idPregunta]['respuestas'][] = $row['respuesta'];
    if (!empty($row['opcion'])) {
        $respuestas[$idPregunta]['opciones'][] = htmlspecialchars($row['opcion']);
    }
}
?>

<style>
    .rating {
        display: inline-flex;
        align-items: center;
    }

    .star {
        font-size: 1rem;
        color: #ccc;
        transition: color 0.2s;
    }

    .star.selected {
        color: #ffc107;
    }

    .round {
        width: 0.7rem;
        height: 0.7rem;
        background-color: white;
        border: 1px solid #999191;
        border-radius: 50%;
        display: inline-block;
        margin: 0 5px;
    }

    .round.selected {
        background-color: #999191;
    }
</style>

<div class="row">
    <?php
    if (empty($respuestas)) {
        echo '<p class="text-muted">No hay respuestas registradas.</p>';
    } else {
        $checkboxGroups = [];

        foreach ($respuestas as $preg) {
            $idPregunta = $preg['id_preguntas'];
            $pregunta = $preg['preguntas'];
            $tipoCampo = $preg['id_tipo_campo'];
            $archivo = $preg['archivo'];
            $tipoArchivo = $preg['tipo_archivo'];
            $respuestas = $preg['respuestas'];
            $opciones = array_unique($preg['opciones']); // Eliminamos duplicados

            switch ($tipoCampo) {

                case 6: // Checkboxes Múltiples
                case 7: // Select Múltiple
                    if (!isset($checkboxGroups[$idPregunta])) {
                        $checkboxGroups[$idPregunta] = [
                            'pregunta' => $pregunta,
                            'opciones' => []
                        ];
                    }

                    $checkboxGroups[$idPregunta]['opciones'] = array_merge(
                        $checkboxGroups[$idPregunta]['opciones'],
                        $opciones
                    );
                    break;

                case 8: // Archivos
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($pregunta) . ':</strong>
                            <div class="text-secondary">
                                <a href="../../pages/admin/ui/solicitudes/archivo.php?id_pregunta=' . $idPregunta . '" target="_blank">
                                    <i class="bi bi-eye-fill"></i> Ver archivo
                                </a>
                            </div>
                          </div>';
                    break;

                case 9: // Círculos de calificación (1-5)
                    $calificacion = intval($respuestas[0]);
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($pregunta) . ':</strong>
                            <div class="d-flex align-items-center">';
                    for ($j = 1; $j <= 5; $j++) {
                        $selected = ($j === $calificacion) ? 'selected' : '';
                        echo '<span class="round ' . $selected . '" data-value="' . $j . '"></span> ' . $j . ' ';
                    }
                    echo '</div></div>';
                    break;

                case 10: // Estrellas de calificación
                    $calificacion = intval($respuestas[0]);
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($pregunta) . ':</strong>
                            <div class="rating">';
                    for ($j = 1; $j <= 5; $j++) {
                        $selected = ($j <= $calificacion) ? 'selected' : '';
                        echo '<span class="star ' . $selected . '">&#9733;</span>';
                    }
                    echo '</div></div>';
                    break;

                default: 
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($pregunta) . ':</strong>
                            <span class="text-secondary"><small>' . htmlspecialchars($respuestas[0]) . '</small></span>
                          </div>';
                    break;
            }
        }

        // Renderizar los grupos de Checkboxes/Selects
        foreach ($checkboxGroups as $group) {
            echo '<div class="mb-3">
                    <strong>' . htmlspecialchars($group['pregunta']) . ':</strong>
                    <ul class="list-group mt-1">';
            foreach (array_unique($group['opciones']) as $opcion) {
                echo '<li class="list-group-item">' . htmlspecialchars($opcion) . '</li>';
            }
            echo '</ul></div>';
        }
    }
    ?>
</div>
