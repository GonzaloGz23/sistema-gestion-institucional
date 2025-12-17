<?php
include '../../../../backend/config/database.php';

$idSolicitudRH = $_POST['id_solicitud_rh'] ?? null;
$idFormulario = $_POST['id_form'] ?? null;
$idEmpleado = $_POST['id_empleado'] ?? null;

if (!$idSolicitudRH || !$idFormulario || !$idEmpleado) {
    echo '<p class="text-danger">Faltan datos para mostrar la solicitud.</p>';
    exit;
}

// Iniciar el buffer de salida
ob_start();

// Ajustar la consulta para incluir el texto del valor en campos tipo radio y select
$stmt = $pdo->prepare("
    SELECT 
        rf.id_formulario_respuestas id_formRespuesta,
        p.id_preguntas,
        rf.respuesta, 
        p.preguntas, 
        op.opcion AS texto_opcion,
        p.orden, 
        p.id_tipo_campo, 
        tp.descripcion, 
        rf.archivo  
    FROM formulario_respuestas rf
    LEFT JOIN preguntas p ON p.id_preguntas = rf.id_preguntas
    LEFT JOIN formularios f ON f.id_formularios = p.id_formularios_fk
    LEFT JOIN empleados e ON e.id_empleado = rf.id_empleados
    LEFT JOIN tipo_campo tp ON tp.id_tipo_campo = p.id_tipo_campo
    LEFT JOIN opcion_preguntas op ON op.id_opcion_preguntas = rf.id_opcion_preguntas
    WHERE f.id_formularios = ? AND rf.id_solicitud_rh = ? AND e.id_empleado = ?
    ORDER BY p.orden ASC
");
$stmt->execute([$idFormulario, $idSolicitudRH, $idEmpleado]);

$respuestas = [];
$data_res = $stmt->fetchAll();

if (count($data_res) > 0) {
    foreach ($data_res as $row) {
        $id = $row['id_preguntas'];

        if (!isset($respuestas[$id])) {
            $respuestas[$id] = [
                'id_formRespuesta' => $row['id_formRespuesta'],
                'id_preguntas' => $row['id_preguntas'],
                'preguntas' => $row['preguntas'],
                'id_tipo_campo' => $row['id_tipo_campo'],
                'orden' => $row['orden'],
                'archivo' => $row['archivo'],
                'respuestas' => [],
                'opciones' => []
            ];
        }

        if (!empty($row['texto_opcion'])) {
            $respuestas[$id]['opciones'][] = $row['texto_opcion'];
        }

        if (!empty($row['respuesta'])) {
            $respuestas[$id]['respuestas'][] = $row['respuesta'];
        }
    }
    ?>

    <style>
        .rating {
            display: inline-flex;
            align-items: center;
        }

        .star {
            font-size: 1.5rem;
            color: #ccc;
            margin-right: 0.1rem;
        }

        .star.selected {
            color: #ffc107;
        }
    </style>

    <div class="row">
        <?php
        foreach ($respuestas as $preg) {
            $id_tipo_campo = $preg['id_tipo_campo'];
            $titulo_pregunta = $preg['preguntas'];
            $respuestasCampo = $preg['respuestas'];
            $opcionesCampo = $preg['opciones'];

            switch ($id_tipo_campo) {
                case 5: // Radio Buttons
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>
                            <span class="text-secondary">' . htmlspecialchars(implode(', ', $opcionesCampo)) . '</span>
                          </div>';
                    break;

                case 6: // Checkboxes Múltiples
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>
                            <ul class="list-group">';
                    foreach ($opcionesCampo as $opcion) {
                        echo '<li class="list-group-item">' . htmlspecialchars($opcion) . '</li>';
                    }
                    echo '</ul></div>';
                    break;

                case 7: // Select
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>
                            <span class="text-secondary">' . htmlspecialchars(implode(', ', $opcionesCampo)) . '</span>
                          </div>';
                    break;

                case 8: // Archivos
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>
                            <a href="../../pages/admin/ui/solicitudes/archivo.php?id_pregunta=' . $preg['id_formRespuesta'] . '" target="_blank">
                                ' . htmlspecialchars($respuestasCampo[0]) . '
                            </a>
                          </div>';
                    break;

                case 10: // Calificación (Estrellas)
                    $calificacion = intval($respuestasCampo[0]);
                    echo '<div class="rating">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>';
                    for ($j = 1; $j <= 5; $j++) {
                        $selected = ($j <= $calificacion) ? 'selected' : '';
                        echo '<span class="star ' . $selected . '">★</span>';
                    }
                    echo '</div>';
                    break;

                default:
                    // Para los tipos de campo restantes
                    echo '<div class="mb-3">
                            <strong>' . htmlspecialchars($titulo_pregunta) . ':</strong>
                            <span class="text-secondary">' . htmlspecialchars(implode(', ', $respuestasCampo)) . '</span>
                          </div>';
                    break;
            }
        }
        ?>
    </div>

    <?php
} else {
    echo '<p>No hay respuestas registradas.</p>';
}

// Capturar el contenido renderizado hasta ahora
$htmlContent = ob_get_clean();

// Obtener la respuesta actual de RRHH
$stmtRespuesta = $pdo->prepare("
    SELECT respuesta 
    FROM rrhh_solicitudes 
    WHERE id_solicitud_rh = ?
");
$stmtRespuesta->execute([$idSolicitudRH]);
$respuestaRRHH = $stmtRespuesta->fetchColumn() ?: 'Sin respuesta';

// Enviar el contenido renderizado y la respuesta de RRHH en formato JSON
echo json_encode([
    'html' => $htmlContent,
    'respuesta_rrhh' => $respuestaRRHH
]);
