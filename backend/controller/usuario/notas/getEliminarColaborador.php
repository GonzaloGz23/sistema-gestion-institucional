<?php
include '../../../../backend/config/database.php';
header('Content-Type: application/json');

$idNota = $_POST['id_nota'] ?? null;
$idUsuario = $_POST['id_usuario'] ?? null;

if ($idNota && $idUsuario) {
    $stmt = $pdo->prepare("UPDATE collaboradores SET estado = 0 WHERE id_nota = ? AND id_usuario = ?");
    $stmt->execute([$idNota, $idUsuario]);

    // Recuperar datos del empleado para reinsertar en el <select>
    $stmt2 = $pdo->prepare("SELECT nombre, apellido FROM empleados WHERE id_empleado = ?");
    $stmt2->execute([$idUsuario]);
    $user = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'status' => 'ok',
            'id' => $idUsuario,
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Empleado no encontrado']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
