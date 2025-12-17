<?php
require_once '../../../config/database.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $stmt = $pdo->prepare("
        INSERT INTO modulos (nombre, ruta, perfil, activo, orden, id_modulo_fk, icono_svg)
        VALUES (:nombre, :ruta, :perfil, :activo, :orden, :id_modulo_fk, :icono_svg)
    ");

    $success = $stmt->execute([
        ':nombre'       => $_POST['nombre'] ?? '',
        ':ruta'         => $_POST['ruta'] ?? '',
        ':perfil'       => $_POST['perfil'] ?? '',
        ':activo'       => $_POST['activo'] ?? '',
        ':orden'        => $_POST['orden'] ?? 0,
        ':id_modulo_fk' => !empty($_POST['id_modulo_fk']) ? $_POST['id_modulo_fk'] : null,
        ':icono_svg'    => !empty($_POST['icono_svg']) ? $_POST['icono_svg'] : null,
    ]);

    if ($success) {
        $id_modulo = $pdo->lastInsertId();
        echo json_encode([
            'success' => true,
            'id_modulo' => $id_modulo,
            'message' => 'Módulo agregado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar el módulo'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error SQL: ' . $e->getMessage()
    ]);
}
