<?php
// Eliminar session_start() y reemplazarlo por la configuración centralizada
require_once '../../../../config/session_config.php';
require_once '../../../../config/database.php';
require_once '../../../../../pages/common/functions.php';

// Verificar autenticación antes de continuar
if (!verificarUsuarioAutenticado()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$iduser = $_SESSION['usuario']['id'];
header('Content-Type: application/json');

try {


    

    $titulo = trim($_POST['tituloFormulario'] ?? '') ?: 'Formulario sin título';

    $tipo_form = isset($_POST['tipoFormulario']) ? $_POST['tipoFormulario'] : NULL;

    $tipo_enfoque = $_POST['tipoenfoque'] ?? 'General';
    $obj=$pdo;


    // Insertar formulario
    $stmtForm = $pdo->prepare("INSERT INTO `formularios`( `nombre`, `id_empleados`, `id_tipos_formularios`,`fecha_creacion`)   VALUES (?, ?, ?, NOW())");
    $stmtForm->execute([$titulo, $iduser, $tipo_form]);
    $form_id = $pdo->lastInsertId();

    $seleccionados = json_decode($_POST['seleccionados'], true); // Ahora es un array PHP
    
    if ($tipo_enfoque == 'General') {
       
        $stmtAsignacion = $pdo->prepare("INSERT INTO `formulario_asignacion`( `general`, `id_formulario`)  VALUES (?, ?)");
        $stmtAsignacion->execute([1, $form_id]);
        sendAll($pdo,"Formulario",null,array(
                "{titulo}"=>$titulo
            ));
    } else if ($tipo_enfoque == 'equipo') {
        if (!empty($seleccionados)) {
            $stmtAsignacion = $pdo->prepare("INSERT INTO `formulario_asignacion`( `id_equipo`, `id_formulario`) VALUES (?, ?)");
            $valores=[];
            foreach ($seleccionados as $id_asignado) {
                $stmtAsignacion->execute([$id_asignado, $form_id]);
                array_push($valores,$id_asignado);
            }
            $ids=implode(",",$valores);
            sendAll($obj,"Formulario",array(
                "rela_equipo"=>$ids
            ),array(
                "{titulo}"=>$titulo
            ));
        }
    } else if ($tipo_enfoque == 'individual') {
        if (!empty($seleccionados)) {
            $stmtAsignacion = $pdo->prepare("INSERT INTO `formulario_asignacion`( `id_empleados`, `id_formulario`) VALUES (?, ?)");
            $valores=[];

            foreach ($seleccionados as $id_asignado) {
                $stmtAsignacion->execute([$id_asignado, $form_id]);
                array_push($valores,$id_asignado);

            }
            $ids=implode(",",$valores);
            sendAll($obj,"Formulario",array(
                "rela_usuario"=>$ids
            ),array(
                "{titulo}"=>$titulo
            ));
        }
    }



    $data = json_decode($_POST["data"], true);  // true para forzar array asociativo

    foreach ($data as $orden => $info) {
        $texto = $info["pregunta"] ?? '';
        $obligatorio = !empty($info["obligatorio"]) ? 1 : 0;
        $tipo = $info["tipo_campo"] ?? null;
        // Insertar pregunta
        $stmtPregunta = $pdo->prepare("INSERT INTO `preguntas`(`preguntas`, `orden`, `obligatorio`, `id_tipo_campo`, `id_formularios_fk`) VALUES (?, ?, ?, ?, ?)");
        $stmtPregunta->execute([$texto, $orden, $obligatorio, $tipo, $form_id]);
        $id_pregunta = $pdo->lastInsertId();

        // Si es pregunta cerrada y hay opciones
        if (in_array($tipo, ['5', '6', '7']) && isset($info["opciones"])) {
            foreach ($info["opciones"] as $opcion) {
                $valor = trim($opcion);
                if ($valor !== '') {
                    $stmtCerrada = $pdo->prepare("INSERT INTO `opcion_preguntas`(`opcion`, `id_preguntas`) VALUES (?, ?)");
                    $stmtCerrada->execute([$valor, $id_pregunta]);
                }
            }
        }
    }
    
 

    echo json_encode([
        'success' => true,
        'message' => 'Formulario creado correctamente',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar: ' . $e->getMessage(),
    ]);
}
