<?php
include '../../../../config/database.php';
// Incluir configuración centralizada de sesiones
require_once '../../../../config/session_config.php';

header('Content-Type: application/json; charset=utf-8');

// Usar la función de verificación de usuario autenticado
if (!verificarUsuarioAutenticado()) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$id_empleado = $_SESSION['usuario']['id'];
//id del formulario
$id_formulario = $_POST['id_formulario'] ?? null;

date_default_timezone_set('America/Argentina/Buenos_Aires');
$fechaHoraArgentina = date('Y-m-d H:i:s');

if (!$id_empleado) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

try {
    // 1. Crear la solicitud principal
    $stmtSolicitud = $pdo->prepare("
    INSERT INTO rrhh_solicitudes (id_empleado, id_formulario, fecha_solicitud)
    VALUES (?, ?, ?)
");
    $stmtSolicitud->execute([$id_empleado, $id_formulario, $fechaHoraArgentina]);
    $idSolicitudRH = $pdo->lastInsertId();

    //  2. Guardar las respuestas asociadas a la solicitud
    foreach ($_POST['respuestas'] as $index => $respuesta) {
        $id_pregunta = $respuesta['id_preguntas'];
        $texto = $respuesta['respuesta'];
        $id_opcion = $respuesta['id_opcion_preguntas'] ?? null;

        $archivoCodificado = null;
        $tipoArchivo = null;

        // ... manejar archivos si hay
        $campoArchivo = "respuestas[{$index}][archivo]";
        if (isset($_FILES["respuestas"]["name"][$index]["archivo"]) && is_uploaded_file($_FILES["respuestas"]["tmp_name"][$index]["archivo"])) {
            $archivoTmp = $_FILES["respuestas"]["tmp_name"][$index]["archivo"];
            $contenidoBinario = file_get_contents($archivoTmp);
            $archivoCodificado = base64_encode($contenidoBinario);  // Codificación base64

            // Obtener el tipo MIME del archivo
            $tipoArchivo = mime_content_type($archivoTmp);  // Tipo MIME del archivo (por ejemplo, 'image/jpeg', 'application/pdf', etc.)
        }
        // insersión 
        $stmt = $pdo->prepare("
            INSERT INTO formulario_respuestas (
                respuesta, id_preguntas, id_opcion_preguntas, id_empleados, fecha, archivo, tipo_archivo, id_solicitud_rh
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $texto,
            $id_pregunta,
            $id_opcion,
            $id_empleado,
            $fechaHoraArgentina,
            $archivoCodificado,
            $tipoArchivo,
            $idSolicitudRH
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Respuestas guardadas correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar respuestas: ' . $e->getMessage()]);
}
