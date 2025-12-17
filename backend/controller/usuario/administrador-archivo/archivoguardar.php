<?php
require_once "../../../config/database.php";

// Función para enviar una respuesta JSON
function enviarRespuesta($exito, $mensaje, $datos = null) {
    header('Content-Type: application/json');
    echo json_encode(['exito' => $exito, 'mensaje' => $mensaje, 'datos' => $datos]);
    exit;
}


function sanitizar($nombre)
{
    $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
    $nombre = preg_replace('/[^\w\.-]/', '_', $nombre);
    return strtolower($nombre);
}



function formatearTamanoArchivo($bytes) {
    if ($bytes >= 1073741824) { // GB
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) { // MB
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) { // KB
        return number_format($bytes / 1024, 2) . ' KB';
    } else { // Bytes
        return $bytes . ' bytes';
    }
}



if (!empty($_FILES['archivos']['name'][0])) { // Verifica si se recibió al menos un archivo
    $nombreArchivo = $_POST['nombrarchivo'];
    $carpetaPadre = $_POST['padrecarp'];
    $carpetaDestino = "../../../../uploads/admin-archivo/";
    $zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
    $fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);
    $actual = $fechaHoraActual->format('Y-m-d H:i:s');


    $archivos = $_FILES['archivos']; // Obtiene el array de archivos

    try {
        $idsInsertados = []; // Array para almacenar los IDs de los archivos insertados

        // Itera a través de los archivos
        for ($i = 0; $i < count($archivos['name']); $i++) {
            $nombreArchivoOriginal = $archivos['name'][$i];
            $archivoTemporal = $archivos['tmp_name'][$i];
            $tamano = $archivos['size'][$i];
              
            $v = $i + 1;
            $nombreArchivoUnic = $nombreArchivo.'-'.$v;
            $tamanoFormateado = formatearTamanoArchivo($tamano);
            $tamanoMaximo = 10 * 1024 * 1024;

            if ($tamano > $tamanoMaximo) {
                enviarRespuesta(false, "El archivo $nombreArchivoOriginal excede el tamaño máximo permitido (10 MB).");
            } else {
                $nombreArchivoOriginal = str_replace(' ', '_', $nombreArchivoOriginal);
                $nombreArchivoUnico = uniqid() . '_' . sanitizar($nombreArchivoOriginal);
                $rutaCompleta = $carpetaDestino . $nombreArchivoUnico;

                move_uploaded_file($archivoTemporal, $rutaCompleta);

                $sql = "INSERT INTO `archivo_carpeta` (`nombre_archivo`, `descarga_archivo`, `rela_carpeta`, `nombre_arch_carpe`, `tamano`, `fecha_actual`) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nombreArchivoUnic, $rutaCompleta, $carpetaPadre, $nombreArchivoUnico, $tamanoFormateado, $actual]);

                $idsInsertados[] = $pdo->lastInsertId();
            }
        }

        enviarRespuesta(true, "Archivos insertados con éxito.", ['ids' => $idsInsertados]);
    } catch (PDOException $e) {
        enviarRespuesta(false, "Error al insertar los archivos: " . $e->getMessage());
    }
} else {
    enviarRespuesta(false, "No se recibieron archivos.");
}
?>