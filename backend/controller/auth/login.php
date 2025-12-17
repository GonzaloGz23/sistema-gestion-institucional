<?php
// Incluir configuración centralizada de sesiones
require_once '../../config/session_config.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Validación de campos
if (empty($_POST['usuario']) || empty($_POST['contrasena'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

$usuario = trim($_POST['usuario']);
$contrasena = $_POST['contrasena'];

try {
    // Buscar usuario
    $stmt = $pdo->prepare("
        SELECT 
            e.id_empleado, 
            e.id_entidad, 
            e.nombre, 
            e.apellido, 
            e.password, 
            e.id_equipo, 
            e.id_rol,
            e.estado,
            i.nombre AS institucion
        FROM empleados e
        INNER JOIN entidades i ON e.id_entidad = i.id_entidad
        WHERE e.usuario = :usuario AND e.borrado = 0
        LIMIT 1
    ");
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }

    if ($user['estado'] !== 'habilitado') {
        echo json_encode(['success' => false, 'message' => 'Usuario deshabilitado.']);
        exit;
    }

    if (!password_verify($contrasena, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
        exit;
    }

    // Guardar sesión estructurada
    $_SESSION['usuario'] = [
        'id'          => $user['id_empleado'],
        'nombre'      => $user['nombre'],
        'apellido'    => $user['apellido'],
        'id_equipo'   => $user['id_equipo'],
        'id_entidad'  => $user['id_entidad'],
        'id_rol'      => $user['id_rol'],
        'institucion' => $user['institucion'],
    ];

    // (Opcional) Rutas permitidas del rol
    $moduloStmt = $pdo->prepare("
        SELECT m.ruta 
        FROM roles_modulos rm
        JOIN modulos m ON rm.id_modulo = m.id_modulo
        WHERE rm.id_rol = :idRol
    ");
    $moduloStmt->execute([':idRol' => $user['id_rol']]);
    $_SESSION['usuario']['modulos_permitidos'] = $moduloStmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso.']);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos.',
        'debug' => $e->getMessage()
    ]);
}
