<?php

require_once '../../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once '../../../../config/session_config.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();
$iduser = $usuarioActual['id'];
header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaActual = date('Y-m-d H:i:s');

if (!empty($_POST['id']) && !empty($_POST['estado'])) {

    $idFormulario = $_POST['id'];
    $nuevoEstado = $_POST['estado'];

    try {
        $stmt = $pdo->prepare("UPDATE `formularios` SET `estado` = :estado WHERE `id_formularios` = :id");
        $stmt->bindParam(':estado', $nuevoEstado);
        $stmt->bindParam(':id', $idFormulario);
        $stmt->execute();
        if ($nuevoEstado == 'Visible') {
            //traer los empleados a quienes se asignaron
            $traerDatos = $pdo->prepare(" SELECT f.nombre,f.id_tipos_formularios , DATE_FORMAT(f.fecha_creacion, '%d/%m/%Y') fechacreacion, f.estado , f.id_formularios, e.id_empleado, eq.id_equipo , fa.general 
                             FROM `formulario_asignacion` fa
                             LEFT JOIN formularios f on f.id_formularios = fa.id_formulario
                             LEFT JOIN tipos_formularios tf on tf.id_tipos_formularios = f.id_tipos_formularios
                             LEFT JOIN empleados e on e.id_empleado= fa.id_empleados
                             LEFT JOIN equipos eq on eq.id_equipo = fa.id_equipo
                             WHERE f.id_formularios = :idform
                              
                            ");
            $traerDatos->execute([$idFormulario]);
            $datos_form = $traerDatos->fetchAll();

            if (count($datos_form) > 0) {
                foreach ($datos_form as $s) {
                    switch ($s['id_tipos_formularios']) {
                        case 1:
                            $url = 'https://example.com/sistemaInstitucional/pages/user/form_legajos.php';
                            break;

                        case 2:
                            $url = 'https://example.com/sistemaInstitucional/pages/user/rrhh.php';
                            break;

                        case 3:
                            $url = 'https://example.com/sistemaInstitucional/pages/user/form_prestaciones.php';
                            break;

                        case 4:
                            $url = 'https://example.com/sistemaInstitucional/pages/user/form_resultados.php';
                            break;

                        default:
                            $url = 'https://example.com/sistemaInstitucional/pages/user/form_legajos.php';
                            break;
                    }
                    $notfi = $pdo->prepare("
                                INSERT INTO `notificacion` (
                                    `titulo`, `link`, `fecha_creacion`, 
                                    `id_modulo`, `estado`, `id_empleado`, 
                                    `id_equipo`, `id_general`,  `icono`
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");

                    $titulo = 'Formulario: ' . $s['nombre'];
                    $link = $url;
                    $id_modulo = 1;
                    $estado = 0;
                    $id_empleado = $s['id_empleado'];
                    $id_equipo = $s['id_equipo'];
                    $id_general = $s['general'];
                    $icon = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/icons/ui-checks.svg';

                    $notfi->execute([
                        $titulo,
                        $link,
                        $fechaActual,
                        $id_modulo,
                        $estado,
                        $id_empleado,
                        $id_equipo,
                        $id_general,
                        $icon
                    ]);
                }
            }

            //INSERT INTO `notificacion`( `titulo`, `link`, `fecha_creacion`, `id_modulo`, `estado`, `id_empleado`) VALUES ()
        }

        echo json_encode(['success' => true, 'message' => 'Se actualizo el estado']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos o formato incorrecto.']);
    exit;
}
