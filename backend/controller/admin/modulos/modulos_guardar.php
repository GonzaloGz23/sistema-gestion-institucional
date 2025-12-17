<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $perfil = trim($_POST['perfil'] ?? '');
    $ruta = trim($_POST['ruta'] ?? '');
    $orden = $_POST['orden'] ?? null;
    $icono_svg = trim($_POST['icono_svg'] ?? '');
    $activo = isset($_POST['activo']) ? (int) $_POST['activo'] : 1;
    $id_modulo_fk = !empty($_POST['id_modulo_fk']) ? (int) $_POST['id_modulo_fk'] : null;

    if (empty($nombre)) {
        echo json_encode(["success" => false, "message" => "El nombre del módulo es obligatorio"]);
        exit;
    }

    if ($id) {
        // 🔹 Actualizar módulo existente
        $sql = "
            UPDATE modulos 
            SET 
                nombre = :nombre,
                perfil = :perfil,
                ruta = :ruta,
                orden = :orden,
                icono_svg = :icono_svg,
                activo = :activo,
                id_modulo_fk = :id_modulo_fk
            WHERE id_modulo = :id
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    } else {
        // 🔹 Insertar nuevo módulo
        $sql = "
            INSERT INTO modulos 
                (nombre, perfil, ruta, orden, icono_svg, activo, id_modulo_fk)
            VALUES 
                (:nombre, :perfil, :ruta, :orden, :icono_svg, :activo, :id_modulo_fk)
        ";

        $stmt = $pdo->prepare($sql);
    }

    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':perfil', $perfil);
    $stmt->bindParam(':ruta', $ruta);
    $stmt->bindParam(':orden', $orden);
    $stmt->bindParam(':icono_svg', $icono_svg);
    $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
    $stmt->bindParam(':id_modulo_fk', $id_modulo_fk);

    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => $id 
            ? "Módulo actualizado correctamente"
            : "Módulo agregado correctamente"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar el módulo",
        "debug" => $e->getMessage()
    ]);
}
?>