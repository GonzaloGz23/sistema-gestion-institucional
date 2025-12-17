<?php
/**
 * Endpoint para subir imagen de capacitación
 * Nomenclatura: id-timestamp-nombre.ext
 * Validaciones: tipo MIME, tamaño, sanitización
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/database_courses.php';
require_once __DIR__ . '/../../../config/usuario_actual.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}

// Verificar que se haya enviado la capacitación ID
if (!isset($_POST['id_capacitacion']) || empty($_POST['id_capacitacion'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'ID de capacitación no proporcionado'
    ]);
    exit;
}

$id_capacitacion = intval($_POST['id_capacitacion']);

// Verificar que se haya enviado un archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'No se recibió ningún archivo';
    if (isset($_FILES['imagen']['error'])) {
        switch ($_FILES['imagen']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'El archivo excede el tamaño máximo permitido';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = 'El archivo se subió parcialmente';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'No se seleccionó ningún archivo';
                break;
            default:
                $errorMsg = 'Error al subir el archivo (código: ' . $_FILES['imagen']['error'] . ')';
        }
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $errorMsg
    ]);
    exit;
}

try {
    // Obtener información del archivo
    $archivo = $_FILES['imagen'];
    $nombreOriginal = $archivo['name'];
    $tmpPath = $archivo['tmp_name'];
    $tamano = $archivo['size'];
    $tipoMime = mime_content_type($tmpPath);

    // Validar tipo MIME
    $tiposPermitidos = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp'
    ];
    if (!in_array($tipoMime, $tiposPermitidos)) {
        throw new Exception('Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG, GIF o WEBP');
    }

    // Validar tamaño (2MB máximo)
    $tamanoMaximo = 2 * 1024 * 1024; // 2MB en bytes
    if ($tamano > $tamanoMaximo) {
        throw new Exception('El archivo excede el tamaño máximo de 2MB');
    }

    // Función de sanitización de nombres
    function sanitizarNombreArchivo($filename)
    {
        // Separar nombre y extensión
        $ultimoPunto = strrpos($filename, '.');
        $nombre = $ultimoPunto !== false ? substr($filename, 0, $ultimoPunto) : $filename;
        $extension = $ultimoPunto !== false ? substr($filename, $ultimoPunto) : '';

        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        $extension = strtolower($extension);

        // Eliminar acentos usando iconv
        $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);

        // Reemplazar espacios con guiones bajos
        $nombre = str_replace(' ', '_', $nombre);

        // Eliminar caracteres no permitidos (mantener solo letras, números, guiones bajos y guiones)
        $nombre = preg_replace('/[^a-z0-9_-]/', '', $nombre);

        // Eliminar guiones bajos múltiples consecutivos
        $nombre = preg_replace('/_+/', '_', $nombre);

        // Eliminar guiones bajos al inicio y final
        $nombre = trim($nombre, '_');

        return $nombre . $extension;
    }

    // Generar timestamp en formato YYYYMMDD_HHMMSS
    $timestamp = date('Ymd_His');

    // Sanitizar nombre original
    $nombreSanitizado = sanitizarNombreArchivo($nombreOriginal);

    // Generar nombre final: id-timestamp-nombre.ext
    $nombreFinal = $id_capacitacion . '-' . $timestamp . '-' . $nombreSanitizado;

    // Detectar entorno (local o producción)
    $esLocal = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

    // Definir directorio de destino en el proyecto hermano
    if ($esLocal) {
        // Entorno local: localhost/newLandingPage/assets/img/capacitaciones/
        $directorioBase = __DIR__ . '/../../../../../newLandingPage/assets/img/capacitaciones/';
    } else {
        // Entorno producción: example.com/training/assets/img/capacitaciones/
        $directorioBase = __DIR__ . '/../../../../../training-platform/assets/img/capacitaciones/';
    }

    // Crear directorio recursivamente si no existe (incluyendo padres)
    if (!is_dir($directorioBase)) {
        // mkdir con true crea todos los directorios padres necesarios (como mkdir -p)
        if (!mkdir($directorioBase, 0755, true)) {
            // Verificar si falló por permisos o por otra razón
            $error = error_get_last();
            throw new Exception('No se pudo crear el directorio de imágenes en la landing page. Verifique los permisos. ' .
                ($error ? $error['message'] : ''));
        }

        // Verificar que el directorio se creó correctamente
        if (!is_dir($directorioBase)) {
            throw new Exception('El directorio no existe después de intentar crearlo');
        }

        // Verificar que el directorio tiene permisos de escritura
        if (!is_writable($directorioBase)) {
            throw new Exception('El directorio existe pero no tiene permisos de escritura');
        }
    } else {
        // El directorio existe, verificar permisos de escritura
        if (!is_writable($directorioBase)) {
            throw new Exception('El directorio de imágenes existe pero no tiene permisos de escritura. ' .
                'Ejecute: chmod 755 ' . $directorioBase);
        }
    }

    $rutaCompleta = $directorioBase . $nombreFinal;

    // Verificar que la capacitación existe y obtener la imagen anterior
    $stmt = $pdoCourses->prepare("SELECT ruta_imagen FROM capacitaciones WHERE id = ? AND esta_eliminada = 0");
    $stmt->execute([$id_capacitacion]);
    $capacitacion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$capacitacion) {
        throw new Exception('La capacitación no existe o fue eliminada');
    }

    $imagenAnterior = $capacitacion['ruta_imagen'];

    // Mover archivo al destino
    if (!move_uploaded_file($tmpPath, $rutaCompleta)) {
        $error = error_get_last();
        throw new Exception('Error al mover el archivo al directorio de destino. ' .
            ($error ? $error['message'] : 'Verifique permisos del directorio'));
    }

    // Verificar que el archivo se movió correctamente
    if (!file_exists($rutaCompleta)) {
        throw new Exception('El archivo no existe después de intentar moverlo');
    }

    // Establecer permisos del archivo
    chmod($rutaCompleta, 0644);

    // Generar URL relativa para la base de datos
    // $imagenUrl = '/sistemaInstitucional/images/training/' . $nombreFinal;
    $imagenUrl = $nombreFinal;

    // Actualizar la base de datos
    $stmtUpdate = $pdoCourses->prepare("UPDATE capacitaciones SET ruta_imagen = ? WHERE id = ?");
    $stmtUpdate->execute([$imagenUrl, $id_capacitacion]);

    // Eliminar imagen anterior si existe y no es la imagen por defecto
    if (
        $imagenAnterior &&
        !empty($imagenAnterior) &&
        strpos($imagenAnterior, 'default-course') === false
    ) {

        // Construir ruta completa de la imagen anterior en el proyecto hermano
        $rutaImagenAnterior = $directorioBase . $imagenAnterior;

        if (file_exists($rutaImagenAnterior)) {
            @unlink($rutaImagenAnterior); // @ para suprimir warnings si falla
        }
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Imagen subida correctamente',
        'imagen_url' => $imagenUrl,
        'nombre_archivo' => $nombreFinal,
        'tamano' => $tamano,
        'tipo_mime' => $tipoMime
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}
