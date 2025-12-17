<?php
require_once "../../../config/database.php";

function formatearTamanoArchivo($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048567, 2) . ' MB'; // Corregido 1048576
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Helper para extraer el nombre original del nombre almacenado (ej: "uniqid_nombre_original.ext")
function extractOriginalName($storedName) {
    // Buscar el primer guion bajo
    $firstUnderscorePos = strpos($storedName, '_');

    // Si hay un guion bajo y no está al principio
    if ($firstUnderscorePos !== false && $firstUnderscorePos > 0) {
        // Retornar la parte después del primer guion bajo
        return substr($storedName, $firstUnderscorePos + 1);
    }

    // Si no hay guion bajo o está al principio, asumimos que el nombre completo es el original (caso inesperado si uniqid() siempre genera el patrón)
    return $storedName;
}


$idCapacitacion = $_POST['idCapacitacion'] ?? null;
$link = $_POST['link'] ?? '';
$lugar = $_POST['lugar'] ?? '';
// *** CORREGIR: EL NOMBRE DEL INPUT FILE EN JS ES 'materiales[]', debe ser 'materiales' aquí ***
$materiales = $_FILES['materiale'] ?? null;
$fechainicio = $_POST['fechaInicio'] ?? null;
$fechafin = $_POST['fechaFin'] ?? null;
$tema = $_POST['temas'] ?? '';
$obligacion = $_POST['obligacion'] ?? '';

$requerimientos = $_POST['requerimientos'] ?? '';
$modalidad = $_POST['modalidad'] ?? '';
$empleadoActual = $_POST['usuarioActual'] ?? ''; // Aunque no se actualiza, se recibe
$carpetaDestino = "../../../../uploads/capacitacion/";
$colaboradoresJSON = $_POST['colaboradores'] ?? null;

$colaboradores = json_decode($colaboradoresJSON, true);

// Validar que el ID de la capacitación esté presente
if (empty($idCapacitacion) || !is_numeric($idCapacitacion)) {
    echo json_encode(["success" => false, "error" => "ID de capacitación inválido."]);
    exit;
}

// Validar y formatear la fecha y hora
if (!empty($fechainicio)) {
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $fechainicio);
    if ($dateTime) {
        $fechainicio = $dateTime->format('Y-m-d H:i:s');
    } else {
        echo json_encode(["success" => false, "error" => "Formato de fecha/hora de inicio inválido"]);
        exit;
    }
}

if (!empty($fechafin)) {
    $dateTime2 = DateTime::createFromFormat('Y-m-d\TH:i', $fechafin);
    if ($dateTime2) {
        $fechafin = $dateTime2->format('Y-m-d H:i:s');
    } else {
        echo json_encode(["success" => false, "error" => "Formato de fecha/hora de fin inválido"]);
        exit;
    }
}

// Solo proceder si los datos esenciales están presentes
if (!empty($fechainicio) && !empty($fechafin) && !empty($tema) && isset($modalidad)) {
    try {
        $pdo->beginTransaction();

        if(!empty($requerimientos)) {
        // Actualizar la tabla de capacitación
        $queryCapacitacion = "UPDATE `capacitacion` SET `fecha-inicio`=?, `fecha-fin`=?, `modalidad`=?, `link`=?, `lugar`=?, `temas`=?, `requerimientos`=?, `obligacion`=? WHERE `id_capacitacion` = ?";
        $stmtCapacitacion = $pdo->prepare($queryCapacitacion);
        $stmtCapacitacion->execute([$fechainicio, $fechafin, $modalidad, $link ?: null, $lugar ?: null, $tema, $requerimientos, $obligacion ,$idCapacitacion]);
   }else {
     // Actualizar la tabla de capacitación
        $queryCapacitacion = "UPDATE `capacitacion` SET `fecha-inicio`=?, `fecha-fin`=?, `modalidad`=?, `link`=?, `lugar`=?, `temas`=?, `obligacion`=? WHERE `id_capacitacion` = ?";
        $stmtCapacitacion = $pdo->prepare($queryCapacitacion);
        $stmtCapacitacion->execute([$fechainicio, $fechafin, $modalidad, $link ?: null, $lugar ?: null, $tema, $obligacion , $idCapacitacion]);
   }

      $consultCapacitacion = "SELECT `id_capacitacion`, `fecha-inicio`, `fecha-fin`, `temas`, `id_eventos` FROM `capacitacion` WHERE `id_capacitacion` = :idCapacitacion";
$stmtconCapacitacion = $pdo->prepare($consultCapacitacion);
$stmtconCapacitacion->bindParam(':idCapacitacion', $idCapacitacion, PDO::PARAM_INT); // Es importante usar bindParam para seguridad
$stmtconCapacitacion->execute();
$conCapacitacion = $stmtconCapacitacion->fetch(PDO::FETCH_ASSOC);

if ($conCapacitacion) {
    $idEventos = $conCapacitacion['id_eventos'];
    // Ahora puedes usar $idEventos
    $queryEvent = "UPDATE `eventos` SET `descripcion`=?,`start`=?,`end`=? WHERE `id` = ?";
        $stmtEvent = $pdo->prepare($queryEvent);
        $stmtEvent->execute([$tema, $fechainicio, $fechafin, $idEventos]);
} 


        // --- Insertar los nuevos colaboradores si no existen ya para esta capacitación ---
        if (!empty($colaboradores) && is_array($colaboradores)) {
            $queryVerificarColaborador = "SELECT COUNT(*) FROM `colaborador_capacitacion` WHERE `id_capacitacion` = ? AND `id_colaborador` = ?";
            $stmtVerificarColaborador = $pdo->prepare($queryVerificarColaborador);
            $queryInsertarColaborador = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_colaborador`) VALUES (?, ?)";
            $stmtInsertarColaborador = $pdo->prepare($queryInsertarColaborador);

            foreach ($colaboradores as $idColaborador) {

                $idColaboradores = $idColaborador['id'];     // Este es el ID real (de empleado o equipo)
        $tipoColaborador = $idColaborador['type']; // Este es el tipo ('employee' o 'team')
                // Asegúrate de que $idColaborador no esté vacío y sea numérico antes de insertar
                if (!empty($idColaboradores) && is_numeric($idColaboradores) && $tipoColaborador === 'employee') {
                    $stmtVerificarColaborador->execute([$idCapacitacion, $idColaboradores]);
                    $conteo = $stmtVerificarColaborador->fetchColumn();

                    if ($conteo === 0) {
                        // Si el colaborador no existe para esta capacitación, lo insertamos
                        $stmtInsertarColaborador->execute([$idCapacitacion, $idColaboradores]);

                        $queryInsertEmployee = "INSERT INTO `colaborador_capacitacion` (`id_capacitacion`, `id_colaborador`, `visible`) VALUES (:capacitacionId, :empleadoId, 'no')";
                        $stmtInsertEmployee = $pdo->prepare($queryInsertEmployee);
                        $stmtInsertEmployee->execute([$idCapacitacion, $idColaboradores]);


                        $queryGeteventos = "SELECT `id_eventos` FROM `capacitacion` WHERE `id_capacitacion` = :capacitacionId";
                        $stmtGetEventos = $pdo->prepare( $queryGeteventos);
                    
                        $stmtGetEventos->bindParam(':capacitacionId', $idCapacitacion, PDO::PARAM_INT);
                        $stmtGetEventos->execute();
                        $eventoId = $stmtGetEventos->fetchColumn();


                       
                        $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_empleado`) VALUES (?, ?)";
                        $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
                        $stmtAsignaciones->execute([$eventoId, $idColaboradores]);



                    }
                } else if(!empty($idColaboradores) && is_numeric($idColaboradores) && $tipoColaborador === 'team') {








                   
// Suponiendo que $idColaborador en este contexto es el ID del equipo seleccionado
$idEquipoSeleccionado = $idColaboradores; // Renombramos para mayor claridad en este bloque

// 1. Insertar el registro de la capacitación vinculada al equipo
$queryInsertarEquipo = "INSERT INTO `colaborador_capacitacion`(`id_capacitacion`, `id_equipo`) VALUES (?, ?)";
$stmtInsertarEquipo = $pdo->prepare($queryInsertarEquipo);

try {
    $stmtInsertarEquipo->execute([$idCapacitacion, $idEquipoSeleccionado]);




    $queryGeteventos = "SELECT `id_eventos` FROM `capacitacion` WHERE `id_capacitacion` = :capacitacionId";
    $stmtGetEventos = $pdo->prepare( $queryGeteventos);

    $stmtGetEventos->bindParam(':capacitacionId', $idCapacitacion, PDO::PARAM_INT);
    $stmtGetEventos->execute();
    $eventoId = $stmtGetEventos->fetchColumn();


   
    $asignacionesEventos = "INSERT INTO `eventos_asignaciones`(`id_evento`, `id_equipo`) VALUES (?, ?)";
    $stmtAsignaciones = $pdo->prepare($asignacionesEventos);
    $stmtAsignaciones->execute([$eventoId, $idEquipoSeleccionado]);



    // Opcional: Verificar $stmtInsertarEquipo->rowCount() si necesitas confirmar que la inserción del equipo fue exitosa.

    // --- Ahora, obtener los empleados de ese equipo e insertarlos individualmente ---

    // 2. Consulta para obtener todos los empleados que pertenecen a este equipo
    $queryGetEmployees = "SELECT `id_empleado` FROM `empleados` WHERE `id_equipo` = :equipoId";
    $stmtGetEmployees = $pdo->prepare($queryGetEmployees);

    $stmtGetEmployees->bindParam(':equipoId', $idEquipoSeleccionado, PDO::PARAM_INT);
    $stmtGetEmployees->execute();
    $employees = $stmtGetEmployees->fetchAll(PDO::FETCH_ASSOC);

    // 3. Preparar la consulta para insertar cada empleado en colaborador_capacitacion
    // Asegúrate de que el nombre de la columna para el ID del empleado es correcto (`id_colaborador` es común)
    $queryInsertEmployee = "INSERT INTO `colaborador_capacitacion` (`id_capacitacion`, `id_colaborador`, `visible`) VALUES (:capacitacionId, :empleadoId, 'no')";
    $stmtInsertEmployee = $pdo->prepare($queryInsertEmployee);

    // 4. Iterar sobre los empleados encontrados e insertar cada uno
    foreach ($employees as $employee) {
        $empleadoId = $employee['id_empleado'];

        // Vincular los parámetros para la inserción individual del empleado
        $stmtInsertEmployee->bindParam(':capacitacionId', $idCapacitacion, PDO::PARAM_INT);
        $stmtInsertEmployee->bindParam(':empleadoId', $empleadoId, PDO::PARAM_INT);

        try {
            // Ejecutar la inserción para el empleado actual
            $stmtInsertEmployee->execute();
            // Opcional: Logear éxito o verificar rowCount() para cada inserción individual
        } catch (PDOException $e) {
            // Manejar errores de inserción individual (ej. si un empleado ya estaba asociado)
            // Puedes logear el error y decidir si continuar con los otros empleados o detenerte
            error_log("Error al insertar empleado $empleadoId para capacitación $idCapacitacion: " . $e->getMessage());
            // Dependiendo de tu lógica, podrías mostrar una alerta o simplemente logearlo
        }
    }

    // Opcional: Logear o reportar que los empleados del equipo fueron procesados/insertados

} catch (PDOException $e) {
    // Manejar errores en la inserción del equipo o en la obtención de empleados
    error_log("Error en la operación con equipo ID $idEquipoSeleccionado y capacitacion ID $idCapacitacion: " . $e->getMessage());
    // Aquí deberías manejar la respuesta al frontend indicando un error
    // header('Content-Type: application/json');
    // echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
    // exit; // Terminar la ejecución
}


                }
            }
        }

        // --- Obtener los nombres originales de los materiales ya existentes para esta capacitación ---
        $existingMaterialNamesQuery = "SELECT nombre_material FROM `materiales` WHERE `id_capacitacion` = :id_capacitacion";
        $stmtExisting = $pdo->prepare($existingMaterialNamesQuery);
        $stmtExisting->bindParam(':id_capacitacion', $idCapacitacion, PDO::PARAM_INT);
        $stmtExisting->execute();
        $existingStoredNames = $stmtExisting->fetchAll(PDO::FETCH_COLUMN); // Obtener solo los nombres almacenados

        // Convertir los nombres almacenados a nombres originales (ignorando el prefijo uniqid_) y a minúsculas para comparación sin distinción de mayúsculas
        $existingOriginalNamesLower = array_map(function($storedName) {
            return strtolower(extractOriginalName($storedName));
        }, $existingStoredNames);


        // --- Procesar y guardar nuevos archivos si se subieron ---
        if (!empty($materiales) && is_array($materiales['name'])) {
            $subidosCorrectamente = []; // Opcional: para llevar un registro de los archivos subidos
            $duplicadosOmitidos = []; // Opcional: para llevar un registro de los duplicados
            $erroresSubida = []; // Opcional: para registrar errores de subida

            for ($i = 0; $i < count($materiales['name']); $i++) {

                // Verificar si hay un error de subida para este archivo
                if ($materiales['error'][$i] !== UPLOAD_ERR_OK) {
                    $erroresSubida[] = "Error de subida '{$materiales['name'][$i]}': Código {$materiales['error'][$i]}";
                    error_log("Error de subida para el archivo " . $materiales['name'][$i] . ": " . $materiales['error'][$i]);
                    continue; // Saltar al siguiente archivo si hay un error
                }

                $nombreArchivoOriginal = $materiales['name'][$i];
                $archivoTemporal = $materiales['tmp_name'][$i];
                $tamano = $materiales['size'][$i];
                $tamanoFormateado = formatearTamanoArchivo($tamano);

                // --- Limpiar el nombre original para comparación y almacenamiento ---
                // Reemplazar espacios por guiones bajos en el nombre original
                $nombreOriginalLimpio = str_replace(' ', '_', $nombreArchivoOriginal);
                $nombreOriginalLimpioLower = strtolower($nombreOriginalLimpio); // Nombre original en minúsculas para comparación


                // --- Verificar si este nombre original ya existe para esta capacitación ---
                $isDuplicate = false;
                if (in_array($nombreOriginalLimpioLower, $existingOriginalNamesLower)) {
                    $isDuplicate = true;
                }

                if ($isDuplicate) {
                    // Este archivo es un duplicado por nombre original para esta capacitación.
                    // Omitir la subida y la inserción en la BD.
                    $duplicadosOmitidos[] = $nombreArchivoOriginal;
                    error_log("Archivo duplicado omitido para ID Capacitacion {$idCapacitacion}: '{$nombreArchivoOriginal}'");
                    continue; // Saltar al siguiente archivo en el bucle de subidas
                }

                // --- Si no es un duplicado, proceder con la subida y la inserción ---

                // Generar nombre único para el archivo en el servidor (basado en el nombre original limpio)
                $nombreArchivoUnico = uniqid() . '_' . $nombreOriginalLimpio;
                $rutaCompleta = $carpetaDestino . $nombreArchivoUnico;

                // Asegurarse de que la carpeta de destino exista
                if (!is_dir($carpetaDestino)) {
                    mkdir($carpetaDestino, 0777, true); // Crear recursivamente con permisos amplios (ajustar si es necesario)
                }


                if (move_uploaded_file($archivoTemporal, $rutaCompleta)) {
                    // Insertar el registro del nuevo material en la base de datos
                    $sqlMaterial = "INSERT INTO `materiales`(`ruta_material`, `nombre_material`, `tamano_material`, `id_capacitacion`) VALUES (?, ?, ?, ?)";
                    $stmtMaterial = $pdo->prepare($sqlMaterial);
                    $stmtMaterial->execute([$rutaCompleta, $nombreArchivoUnico, $tamanoFormateado, $idCapacitacion]);

                    // Opcional: Añadir el nombre original limpiado a la lista de nombres existentes (en minúsculas)
                    // Esto maneja el caso de subir "archivo.pdf" y "ARCHIVO.PDF" en el mismo lote.
                    $existingOriginalNamesLower[] = $nombreOriginalLimpioLower;
                    $subidosCorrectamente[] = $nombreArchivoOriginal; // Registrar como subido correctamente

                } else {
                    // Error al mover el archivo subido (permisos, espacio, etc.)
                    $erroresSubida[] = "Error al mover el archivo '{$nombreArchivoOriginal}'.";
                    error_log("Error al mover el archivo subido {$nombreArchivoOriginal} a {$rutaCompleta}");
                    // No hacemos rollback aquí, solo registramos el error y continuamos con otros archivos si los hay.
                }
            } // Fin del bucle de archivos subidos
        }

        $pdo->commit(); // Confirmar la transacción

        // Opcional: Incluir información sobre archivos procesados en la respuesta
        echo json_encode([
            "success" => true,
            "message" => "Capacitación actualizada.",
            "subidos" => $subidosCorrectamente ?? [],
            "duplicados_omitidos" => $duplicadosOmitidos ?? [],
            "errores_subida" => $erroresSubida ?? []
        ]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => "Error al actualizar la capacitación o procesar materiales: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Datos incompletos para la actualización."]);
}
?>