<?php
// Incluir configuración de sesión y validar usuario
require_once '../../../config/session_config.php';
require_once '../../../config/database.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}
header('Content-Type: application/json');

// Validación de datos obligatorios
if (!isset($_POST['id_rol']) || !isset($_POST['nombre']) || !isset($_POST['modulos'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

$idRol       = (int) $_POST['id_rol'];
$nombre      = trim($_POST['nombre']);
$descripcion = trim($_POST['descripcion'] ?? '');
$modulos     = $_POST['modulos'];

try {
    // 1. Actualizar el rol
    $stmt = $pdo->prepare("
        UPDATE roles
        SET alias = :nombre, descripcion = :desc
        WHERE id_rol = :id
    ");
    $stmt->execute([
        ':nombre' => $nombre,
        ':desc'   => $descripcion,
        ':id'     => $idRol
    ]);

    // 2. Eliminar los módulos anteriores
    $pdo->prepare("DELETE FROM roles_modulos WHERE id_rol = :id")->execute([':id' => $idRol]);

    // 3. Insertar los nuevos módulos
    $stmtInsert = $pdo->prepare("
        INSERT INTO roles_modulos (id_rol, id_modulo, fecha_asignacion)
        VALUES (:idRol, :idModulo, NOW())
    ");
    foreach ($modulos as $idModulo) {
        $stmtInsert->execute([
            ':idRol'    => $idRol,
            ':idModulo' => $idModulo
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Rol actualizado correctamente.']);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el rol.',
        'debug'   => $e->getMessage()
    ]);
}
