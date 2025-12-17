<?php
include '../../../../backend/config/database.php';
require_once "../../../../backend/config/session_config.php";
header('Content-Type: application/json');
   $usuarioActual = obtenerUsuarioActual();
            $id_empleado = $usuarioActual['id'];

            try {
    $sql = "SELECT  f.firebase_app_tokensid id, f.fecha_alta fecha, f.tipo_dispositivo dispositivo, f.navegador navegador, CASE WHEN f.activo = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                   f.activo AS checkead FROM `firebase_app_tokens` f WHERE f.rela_usuario = :idUsuario"; // por ejemplo filtrar por usuario actual

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':idUsuario' =>  $id_empleado]); // asumiendo que $usuarioActual está disponible

    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($datos);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>