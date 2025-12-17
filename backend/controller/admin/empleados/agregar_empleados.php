<?php
require_once '../../../config/database.php';
require '../../../../pages/common/functions.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['apellido']) || empty(trim($_POST['apellido'])) ||
        !isset($_POST['dni']) || empty(trim($_POST['dni'])) ||
        !isset($_POST['usuario']) || empty(trim($_POST['usuario'])) ||
        !isset($_POST['contrasena']) || empty(trim($_POST['contrasena'])) ||
        !isset($_POST['id_equipo']) || !isset($_POST['id_edificio']) ||
        !isset($_POST['id_rol']) || empty(trim($_POST['id_rol']))
    ) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        exit;
    }

    if (!isset($_POST['id_entidad'])) {
        echo json_encode(["success" => false, "message" => "No está especificada la entidad."]);
        exit;
    }

    $id_entidad = trim($_POST['id_entidad']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $usuario = trim($_POST['usuario']);
    $contrasena = trim($_POST['contrasena']);
    $id_equipo = (int) $_POST['id_equipo'];
    $id_edificio = (int) $_POST['id_edificio'];
    $id_rol = (int) $_POST['id_rol'];
    $estado = $_POST['estado'] ?? 'habilitado';

    // Validar DNI solo con números y máximo 8 caracteres
    if (!preg_match('/^[0-9]{1,8}$/', $dni)) {
        echo json_encode(["success" => false, "message" => "El DNI solo puede contener hasta 8 números."]);
        exit;
    }

    // Verificar si el DNI ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE dni = :dni AND borrado = 0");
    $stmt->bindParam(':dni', $dni);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "El DNI ya está registrado en el sistema."]);
        exit;
    }

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE usuario = :usuario AND borrado = 0");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "El usuario ya está registrado en el sistema."]);
        exit;
    }

    // Encriptar contraseña
    if (strlen($contrasena) < 6) {
        echo json_encode(["success" => false, "message" => "La contraseña debe tener al menos 6 caracteres."]);
        exit;
    }
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar el nuevo empleado
    $stmt = $pdo->prepare("
        INSERT INTO empleados (
            id_entidad, nombre, apellido, dni, usuario, `password`,
            id_equipo, id_edificio, id_rol, estado
        ) VALUES (
            :entidad, :nombre, :apellido, :dni, :usuario, :contrasena,
            :id_equipo, :id_edificio, :id_rol, :estado
        )
    ");

    $stmt->bindParam(':entidad', $id_entidad);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':contrasena', $contrasenaHash);
    $stmt->bindParam(':id_equipo', $id_equipo);
    $stmt->bindParam(':id_edificio', $id_edificio);
    $stmt->bindParam(':id_rol', $id_rol);
    $stmt->bindParam(':estado', $estado);
    $stmt->execute();
    
    echo json_encode(["success" => true, "message" => "Empleado agregado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al agregar el empleado", "debug" => $e->getMessage()]);
}
