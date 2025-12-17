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

// Validaciones básicas
if (!isset($_POST['nombre']) || empty(trim($_POST['nombre']))) {
    echo json_encode(['success' => false, 'message' => 'El nombre del rol es obligatorio.']);
    exit;
}

if (!isset($_POST['modulos']) || !is_array($_POST['modulos']) || count($_POST['modulos']) === 0) {
    echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos un módulo.']);
    exit;
}

$nombre        = trim($_POST['nombre']);
$descripcion   = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$modulos       = $_POST['modulos'];
$idInstitucion = $_SESSION['usuario']['id_entidad'] ?? null;

if (!$idInstitucion) {
    echo json_encode(['success' => false, 'message' => 'No se pudo identificar la institución.']);
    exit;
}

try {
    // 1. Crear el nuevo rol
    $stmt = $pdo->prepare("
        INSERT INTO roles (id_entidad, alias, descripcion, fecha_creacion)
        VALUES (:idInst, :nombre, :descripcion, NOW())
    ");
    $stmt->execute([
        ':idInst'      => $idInstitucion,
        ':nombre'      => $nombre,
        ':descripcion' => $descripcion
    ]);

    $idRol = $pdo->lastInsertId();

    // 2. Insertar módulos vinculados
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

    echo json_encode(['success' => true, 'message' => 'Rol creado correctamente.']);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear el rol.',
        'debug'   => $e->getMessage()
    ]);
}
