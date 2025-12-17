<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$idUsuario = $data['idusuario'];

// Verificar si el colaborador ya existe en la carpeta
$queryVerificar = "SELECT COUNT(*) FROM colaborador_carpetas WHERE id_carpeta = :idCarpeta AND id_colaborador = :idUsuario";
$stmtVerificar = $pdo->prepare($queryVerificar);
$stmtVerificar->bindParam(':idCarpeta', $id, PDO::PARAM_INT);
$stmtVerificar->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmtVerificar->execute();
$existeColaborador = $stmtVerificar->fetchColumn();

if ($existeColaborador > 0) {
    echo json_encode(['success' => false, 'message' => "El colaborador ya está asignado a esta carpeta"]);
    exit;
}

// Obtener nombre y apellido del usuario
$queryUsuario = "SELECT nombre, apellido FROM empleados WHERE id_empleado = :idUsuario";
$stmtUsuario = $pdo->prepare($queryUsuario);
$stmtUsuario->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmtUsuario->execute();
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => "Usuario no encontrado"]);
    exit;
}

$nombreUsuario = $usuario['nombre'];
$apellidoUsuario = $usuario['apellido'];

// Insertar en la tabla colaborador_carpetas
$stmtInsertar = $pdo->prepare("INSERT INTO colaborador_carpetas (nombre_colaborador, apellido_colaborador, id_colaborador, id_carpeta) VALUES (:nombre, :apellido, :idUsuario, :idCarpeta)");

try {
    $stmtInsertar->execute([
        ':nombre' => $nombreUsuario,
        ':apellido' => $apellidoUsuario,
        ':idUsuario' => $idUsuario,
        ':idCarpeta' => $id
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "❌ Error al insertar: " . $e->getMessage()]);
}
?>