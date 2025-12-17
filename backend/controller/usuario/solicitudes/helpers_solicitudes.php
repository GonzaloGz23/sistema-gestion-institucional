<?php
function validarCamposRequeridos($campos, $origen = [])
{
    foreach ($campos as $campo) {
        if (empty($origen[$campo])) {
            throw new Exception("Falta el campo: $campo");
        }
    }
}

function validarDNIDesdeBD($pdo, $id_emisor)
{
    $stmt = $pdo->prepare("SELECT dni FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$id_emisor]);
    $usuario = $stmt->fetch();
    if (!$usuario || empty($usuario['dni'])) {
        throw new Exception("Debe tener DNI cargado para enviar solicitudes.");
    }
}

function sanitizar($nombre)
{
    $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
    $nombre = preg_replace('/[^\w\.-]/', '_', $nombre);
    return strtolower($nombre);
}

function guardarSolicitud($pdo, $datos)
{
    $stmt = $pdo->prepare("INSERT INTO solicitudes (asunto, contenido, fecha_resolucion, privada, id_emisor, id_equipo_emisor, id_etiqueta)
        VALUES (:asunto, :contenido, :fecha_resolucion, :privada, :id_emisor, :id_equipo_emisor, :id_etiqueta)");

    $stmt->execute([
        ':asunto' => $datos['asunto'],
        ':contenido' => $datos['contenido'],
        ':fecha_resolucion' => $datos['fecha_resolucion'],
        ':privada' => $datos['privada'],
        ':id_emisor' => $datos['id_emisor'],
        ':id_equipo_emisor' => $datos['id_equipo_emisor'],
        ':id_etiqueta' => $datos['id_etiqueta']
    ]);

    return $pdo->lastInsertId();
}

function guardarDestinatarios($pdo, $id_solicitud, $equipos)
{
    $stmt = $pdo->prepare("INSERT INTO solicitudes_destinatarios (id_solicitud, id_equipo) VALUES (?, ?)");
    foreach ($equipos as $id_equipo) {
        $stmt->execute([$id_solicitud, (int) $id_equipo]);
    }
}

function guardarArchivosAdjuntos($pdo, $archivos, $extensionesPermitidas, $tamanoMaximo, $tiposMimePermitidos, $asociaciones = [])
{
    if (!isset($archivos['name']) || !is_array($archivos['name'])) {
        throw new Exception('Estructura de archivos inválida.');
    }

    $carpetaModulo = 'solicitudes';
    $directorioDestino = __DIR__ . '/../../../../uploads/' . $carpetaModulo;

    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }

    $archivosProcesados = [];

    // 🔍 Fase 1: Validación
    for ($i = 0; $i < count($archivos['name']); $i++) {
        $nombreOriginal = $archivos['name'][$i];
        $tmp = $archivos['tmp_name'][$i];
        $tamano = $archivos['size'][$i];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!file_exists($tmp)) {
            throw new Exception("El archivo temporal '{$nombreOriginal}' no existe o no se pudo acceder.");
        }

        $tipoMime = mime_content_type($tmp);

        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception("Archivo '{$nombreOriginal}' tiene una extensión no permitida.");
        }

        if (!in_array($tipoMime, $tiposMimePermitidos)) {
            throw new Exception("Archivo '{$nombreOriginal}' tiene un tipo MIME no permitido: {$tipoMime}");
        }

        if ($tamano > $tamanoMaximo) {
            throw new Exception("Archivo '{$nombreOriginal}' excede el tamaño máximo permitido.");
        }

        $nombreSanitizado = uniqid() . '_' . sanitizar($nombreOriginal);
        $rutaCompleta = $directorioDestino . '/' . $nombreSanitizado;
        $rutaRelativa = $carpetaModulo . '/' . $nombreSanitizado;

        $archivosProcesados[] = [
            'origen' => $tmp,
            'destino' => $rutaCompleta,
            'nombre_original' => $nombreOriginal,
            'ruta_relativa' => $rutaRelativa,
            'mime' => $tipoMime,
            'tamano' => $tamano
        ];
    }

    // ✅ Fase 2: Mover y registrar
    $fechaCreacion = date('Y-m-d H:i:s');
    $archivosInsertados = [];

    foreach ($archivosProcesados as $archivo) {
        if (move_uploaded_file($archivo['origen'], $archivo['destino'])) {
            foreach ($asociaciones as $asociacion) {
                $id_solicitud = $asociacion['id_solicitud'];
                $id_mensaje = $asociacion['id_mensaje'];

                $sql = "
                    INSERT INTO solicitudes_archivos (
                        id_solicitud,
                        nombre_original,
                        ruta_archivo,
                        tipo_archivo,
                        tamano,
                        creado_en,
                        id_solicitudes_mensaje
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $id_solicitud,
                    $archivo['nombre_original'],
                    $archivo['ruta_relativa'],
                    $archivo['mime'],
                    $archivo['tamano'],
                    $fechaCreacion,
                    $id_mensaje
                ]);

                $archivosInsertados[] = [
                    'nombre' => $archivo['nombre_original'],
                    'ruta' => $archivo['ruta_relativa'],
                    'id_solicitud' => $id_solicitud,
                    'id_mensaje' => $id_mensaje
                ];
            }
        }
    }

    return $archivosInsertados;
}




