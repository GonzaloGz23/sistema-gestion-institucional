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

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');



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
// Verificar que la capacitación existe y no está eliminada
$dni=$_POST["dni"];
$genero=$_POST["genero"];
$idCapacitacion=$_POST["idCapacitacion"];

$sqlCheck = "SELECT id FROM profesores WHERE dni = :dni and genero=:genero limit 1";
$stmt = $pdoCourses->prepare($sqlCheck);
$stmt->execute(['dni' => $dni,"genero"=>$genero]);

$profesorID = $stmt->fetchColumn();


if (empty($profesorID)) {
    $profesorID=0;
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
    //insertar 
    if($profesorID==0){
        $sqlInsertar = "
            INSERT INTO profesores 
                (apellido, nombre, dni, genero, telefono, correo)
            VALUES 
                (:apellido, :nombre, :dni, :genero, :telefono, :correo)
        ";
        $stmtInsertar = $pdoCourses->prepare($sqlInsertar);
        $apellido=$_POST["apellido"];
        $nombre=$_POST["nombre"];
        
        $telefono=$_POST["telefono"];
        $correo=$_POST["correo"];
        $stmtInsertar->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $stmtInsertar->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmtInsertar->bindParam(':dni', $dni, PDO::PARAM_STR);
        $stmtInsertar->bindParam(':genero', $genero, PDO::PARAM_STR);
        $stmtInsertar->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmtInsertar->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmtInsertar->execute();
    
        // ✅ ID generado automáticamente
        $profesorID = $pdoCourses->lastInsertId();
    }
    // 1. Verificar si ya existe la asignación
$consult_asig = "
    SELECT * 
    FROM asignacion_profesores 
    WHERE capacitacion_id = :id_capacitacion 
      AND profesor_id = :id_profesor
";

$stmtasig = $pdoCourses->prepare($consult_asig);
$stmtasig->execute([
    'id_capacitacion' => $idCapacitacion,
    'id_profesor'     => $profesorID
]);

$asignacion = $stmtasig->fetch(PDO::FETCH_ASSOC);
if ($asignacion) {
    $update = "
        UPDATE asignacion_profesores
        SET esta_activo = :esta_activo
        WHERE capacitacion_id = :id_capacitacion
          AND profesor_id = :id_profesor
    ";

    $stmtUpdate = $pdoCourses->prepare($update);
    $stmtUpdate->execute([
        'esta_activo'         => 1, // ej: 'activo' o 'inactivo'
        'id_capacitacion'=> $idCapacitacion,
        'id_profesor'    => $profesorID
    ]);

    if ($stmtUpdate) {
        $pdoCourses->commit();
        echo json_encode(array(
            "status"=>true,
            "idCapacitacion"=>$idCapacitacion
        ));
    }

} else {


    //asignar profesor
    $asig_prof=  $pdoCourses->prepare("
        INSERT INTO asignacion_profesores(profesor_id, capacitacion_id, esta_activo, fecha_asignacion) 
        VALUES (:profesor_id, :capacitacion_id, 1, NOW())
    ");
    $asig_prof->bindParam(':profesor_id', $profesorID, PDO::PARAM_INT);
    $asig_prof->bindParam(':capacitacion_id', $idCapacitacion, PDO::PARAM_INT);    
    $asig_prof->execute();
    if ($asig_prof) {
        # code...
        $pdoCourses->commit();
        echo json_encode(array(
            "status"=>true,
            "idCapacitacion"=>$idCapacitacion
        ));
    }
}
} catch (\Throwable $th) {
    $pdoCourses->rollback();
    throw $th;
    //throw $th;
}