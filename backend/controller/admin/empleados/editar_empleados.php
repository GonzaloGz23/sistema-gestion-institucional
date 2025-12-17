<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (
        !isset($_POST['id']) || !isset($_POST['nombre']) || empty(trim($_POST['nombre'])) ||
        !isset($_POST['apellido']) || empty(trim($_POST['apellido'])) ||
        !isset($_POST['dni']) || empty(trim($_POST['dni'])) ||
        !isset($_POST['usuario']) || empty(trim($_POST['usuario'])) ||
        !isset($_POST['id_equipo']) || !isset($_POST['id_edificio']) ||
        !isset($_POST['id_rol']) || empty(trim($_POST['id_rol']))
    ) {
        echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
        exit;
    }

    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = trim($_POST['dni']);
    $usuario = trim($_POST['usuario']);
    $id_equipo = (int) $_POST['id_equipo'];
    $id_edificio = (int) $_POST['id_edificio'];
    $id_rol = (int) $_POST['id_rol'];
    $estado = $_POST['estado'] ?? 'habilitado';
    
    // Obtener la nueva contraseña si se proporcionó
    $nuevaContrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

    // Validar DNI numérico y máximo 8 caracteres
    if (!preg_match('/^[0-9]{1,8}$/', $dni)) {
        echo json_encode(["success" => false, "message" => "El DNI solo puede contener hasta 8 números."]);
        exit;
    }

    // Verificar si el usuario ya existe para otro empleado
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM empleados
        WHERE usuario = :usuario AND id_empleado != :id AND borrado = 0
    ");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["success" => false, "message" => "El usuario ya está registrado en otro empleado."]);
        exit;
    }

    // Validar nueva contraseña si se proporcionó
    if (!empty($nuevaContrasena) && strlen($nuevaContrasena) < 6) {
        echo json_encode(["success" => false, "message" => "La contraseña debe tener al menos 6 caracteres."]);
        exit;
    }

    // Preparar la consulta de actualización
    if (!empty($nuevaContrasena)) {
        // Actualizar con nueva contraseña
        $contrasenaHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE empleados 
            SET nombre = :nombre,
                apellido = :apellido,
                dni = :dni,
                usuario = :usuario,
                password = :password,
                id_equipo = :id_equipo,
                id_edificio = :id_edificio,
                id_rol = :id_rol,
                estado = :estado
            WHERE id_empleado = :id
        ");
        $stmt->bindParam(':password', $contrasenaHash);
    } else {
        // Actualizar sin cambiar contraseña
        $stmt = $pdo->prepare("
            UPDATE empleados 
            SET nombre = :nombre,
                apellido = :apellido,
                dni = :dni,
                usuario = :usuario,
                id_equipo = :id_equipo,
                id_edificio = :id_edificio,
                id_rol = :id_rol,
                estado = :estado
            WHERE id_empleado = :id
        ");
    }
    
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':id_equipo', $id_equipo);
    $stmt->bindParam(':id_edificio', $id_edificio);
    $stmt->bindParam(':id_rol', $id_rol);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $mensaje = !empty($nuevaContrasena) ? 
        "Empleado y contraseña actualizados correctamente" : 
        "Empleado actualizado correctamente";
        
    echo json_encode(["success" => true, "message" => $mensaje]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar el empleado", "debug" => $e->getMessage()]);
}
