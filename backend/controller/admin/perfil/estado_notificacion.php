<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$estado = $data['estado'] ?? null;

if (!$id || !is_numeric($estado)) {
    echo json_encode(['success' => false, 'msg' => 'Datos inválidos']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE firebase_app_tokens SET activo = :estado WHERE firebase_app_tokensid = :id");
    $stmt->execute([':estado' => $estado, ':id' => $id]);

    // Devuelve datos si querés actualizar visualmente
    $sql = "SELECT  f.firebase_app_tokensid id, f.fecha_alta fecha, f.tipo_dispositivo dispositivo, f.navegador navegador, CASE WHEN f.activo = 1 THEN 'Activo' ELSE 'Inactivo' END AS estado,
                   f.activo AS checkead FROM `firebase_app_tokens` f WHERE f.firebase_app_tokensid = :id"; // por ejemplo filtrar por usuario actual

$stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);    
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        $datos['success'] = true;
     $datos['estado_texto'] = $datos['estado'] == 1 ? 'Activo' : 'Inactivo';
        $datos['fecha'] = $datos['fecha'] ;
        $datos['dispositivo'] = $datos['dispositivo'];
        $datos['navegador'] = $datos['navegador'];
        $datos['estado'] = $datos['checkead'];
        echo json_encode($datos);
    } else {
        echo json_encode(['success' => false, 'msg' => 'No se encontró el registro']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
