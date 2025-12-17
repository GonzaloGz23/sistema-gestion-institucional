<?php
/**
 * Controlador para cambiar estado de capacitaciones
 * Archivo: backend/controller/admin/revision-capacitaciones/cambiar_estado.php
 * 
 * Maneja el workflow de estados: en_espera → en_revision → aprobado
 * Permite también retrocesos manuales según la lógica de negocio
 */

// Incluir configuraciones necesarias
require_once '../../../config/database_courses.php';
require_once '../../../config/database.php';
require_once '../../../config/session_config.php';
include_once './in_code.php';
// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$esLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
$urlcabecera = $esLocal ? 'http://localhost/newLandingPage/' : 'https://example.com/training/';

$cod = new IdEncoder();

// Verificar que el usuario esté autenticado
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}
$pdoCourses->beginTransaction();
try {
    //code...
    // Obtener usuario actual
    $usuarioActual = obtenerUsuarioActual();
    if (!$usuarioActual) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener datos del usuario'
        ]);
        exit;
    }
    // Verificar que la capacitación existe y no está eliminada
    $sqlInsertar = "
        INSERT INTO capacitaciones 
            (equipo_id, alcance, tipo_categoria, categoria_id, tipo_capacitacion, lugar, tipo_modalidad,estado_id)
        VALUES 
            (:id_equipo, :alcance, :tipo_categoria, :categoria_id, :tipo_capacitacion, :lugar, :tipo_modalidad,1)
    ";

    $stmtInsertar = $pdoCourses->prepare($sqlInsertar);
    $id_equipo = $usuarioActual["id_equipo"];
    $alcance = $input["alcance"];
    $especifica = $input["especifica"];
    $categoriaGeneral = $input["categoriaGeneral"];
    $lugar = $input["lugar"];
    $subcategoria = $input["subcategoria"];
    $categoria_id = 0;
    $tipo_capacitacion = $input["tipoCapacitacion"];
    $tipo_modalidad = $input["modalidad"];
    if ($subcategoria != "") {
        $tipo_categoria = "subcategoria";
        $categoria_id = $subcategoria;
    } else if ($especifica != "" && $subcategoria == "") {
        $tipo_categoria = "especifica";
        $categoria_id = $especifica;
    } else if ($categoriaGeneral != "" && $especifica == "") {
        $tipo_categoria = "general";
        $categoria_id = $categoriaGeneral;

    }


    $stmtInsertar->bindParam(':id_equipo', $id_equipo, PDO::PARAM_INT);
    $stmtInsertar->bindParam(':alcance', $alcance, PDO::PARAM_STR);
    $stmtInsertar->bindParam(':tipo_categoria', $tipo_categoria, PDO::PARAM_STR);
    $stmtInsertar->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmtInsertar->bindParam(':tipo_capacitacion', $tipo_capacitacion, PDO::PARAM_STR);
    $stmtInsertar->bindParam(':lugar', $lugar, PDO::PARAM_STR);

    $stmtInsertar->bindParam(':tipo_modalidad', $tipo_modalidad, PDO::PARAM_STR);

    $stmtInsertar->execute();

    // ✅ ID generado automáticamente
    $capacitacionId = $pdoCourses->lastInsertId();

    $id_codif = $cod->encode($capacitacionId);

    $urlFull = $urlcabecera . 'inscripciones/detalleCapacitacion.php?id=' . $id_codif . '';

    $updateLink = $pdoCourses->prepare("UPDATE `capacitaciones` SET `link`='$urlFull' WHERE `id`='$capacitacionId'");

    $updateLink->execute();
    if ($updateLink) {
        # code...
        $pdoCourses->commit();
        echo json_encode(array(
            "status" => true,
            "idCapacitacion" => $capacitacionId
        ));
    }
} catch (\Throwable $th) {
    $pdoCourses->rollback();

    // Log del error para debugging
    error_log("Error en insert_capacitacion.php: " . $th->getMessage());
    error_log("Stack trace: " . $th->getTraceAsString());

    // Devolver JSON con el error
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'error' => $th->getMessage(),
        'file' => basename($th->getFile()),
        'line' => $th->getLine()
    ]);
    exit;
}