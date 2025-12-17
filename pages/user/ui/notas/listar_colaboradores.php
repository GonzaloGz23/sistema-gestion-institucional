<?php
// Incluir configuración de sesión y validar usuario
require_once '../../../../backend/config/session_config.php';
include '../../../../backend/config/database.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario de forma segura
$usuarioActual = obtenerUsuarioActual();
$iduser = $usuarioActual['id'];

$stmt = $pdo->prepare("
   SELECT id_empleado AS id, nombre, apellido 
   FROM empleados 
   WHERE id_empleado != ? AND id_entidad = ? AND borrado = 0 AND estado = 'habilitado'
");
$stmt->execute([$iduser, $usuarioActual['id_entidad']]);

$usuarios = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $usuarios[] = [
        'id' => $row['id'],
        'nombre' => $row['nombre'],
        'apellido' => $row['apellido'],
        'siglas' => strtoupper(substr($row['nombre'], 0, 1) . substr($row['apellido'], 0, 1))
    ];
}

header('Content-Type: application/json');
echo json_encode($usuarios);
