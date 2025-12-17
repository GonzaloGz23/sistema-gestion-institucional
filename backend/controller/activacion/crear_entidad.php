<?php
// Incluir configuración de sesión
require_once '../../config/session_config.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Validación inicial de campos
$required = ['nombreEntidad', 'nombre', 'apellido', 'usuario', 'contrasena'];
foreach ($required as $field) {
    if (!isset($_POST[$field]) || empty(trim(string: $_POST[$field]))) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }
}

// Validación de sesión de licencia
if (!isset($_SESSION['usuario']['id_licencia_activada'])) {
    echo json_encode(value: ['success' => false, 'message' => 'Sesión inválida. Active una licencia.']);
    exit;
}

// Recolección de datos
$nombreEntidad = trim($_POST['nombreEntidad']);
$nombre        = trim($_POST['nombre']);
$apellido      = trim($_POST['apellido']);
$usuario       = trim($_POST['usuario']);
$contrasena    = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT);
$idLicencia    = $_SESSION['usuario']['id_licencia_activada'];

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Insertar institución
    $stmt = $pdo->prepare("
        INSERT INTO entidades (nombre, id_licencia, fecha_creacion)
        VALUES (:nombre, :idLicencia, NOW())
    ");
    $stmt->execute([
        ':nombre' => $nombreEntidad,
        ':idLicencia' => $idLicencia
    ]);
    $idInstitucion = $pdo->lastInsertId();

    // 2. Crear rol "Administrador"
    $stmt = $pdo->prepare("
        INSERT INTO roles (id_entidad, alias, descripcion, fecha_creacion)
        VALUES (:idInstitucion, 'Administrador', 'Rol principal del sistema', NOW())
    ");
    $stmt->execute([':idInstitucion' => $idInstitucion]);
    $idRol = $pdo->lastInsertId();

    // 3. Insertar administrador principal
    $stmt = $pdo->prepare("
        INSERT INTO empleados 
        (id_entidad, nombre, apellido, dni, usuario, password, id_rol, fecha_creacion, estado, borrado)
        VALUES
        (:idInstitucion, :nombre, :apellido, '', :usuario, :contrasena, :idRol, NOW(), 'habilitado', 0)
    ");
    $stmt->execute([
        ':idInstitucion' => $idInstitucion,
        ':nombre' => $nombre,
        ':apellido' => $apellido,
        ':usuario' => $usuario,
        ':contrasena' => $contrasena,
        ':idRol' => $idRol
    ]);

    // 4. Asignar todos los módulos activos al rol creado
    $modulos = $pdo->query("SELECT id_modulo FROM modulos WHERE activo = 'Activo'")->fetchAll(PDO::FETCH_COLUMN);

    $stmtInsert = $pdo->prepare("INSERT INTO roles_modulos (id_rol, id_modulo) VALUES (?, ?)");
    foreach ($modulos as $idModulo) {
        $stmtInsert->execute([$idRol, $idModulo]);
    }

    // Finalizar transacción
    $pdo->commit();

    // Limpiar la sesión temporal
    unset($_SESSION['usuario']['id_licencia_activada']);

    echo json_encode(['success' => true, 'message' => 'La institución fue creada correctamente.']);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar la entidad: ' . $e->getMessage()
    ]);
}
