<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    if (!isset($_POST['id_modulo'], $_POST['campo'], $_POST['valor'])) {
        echo json_encode(["success" => false, "message" => "Faltan parámetros"]);
        exit;
    }

    $id = (int) $_POST['id_modulo'];
    $campo = $_POST['campo'];
    $valor = $_POST['valor'];

    // Lista blanca de campos permitidos
    $permitidos = ['nombre', 'ruta', 'activo', 'perfil', 'orden', 'icono_svg', 'id_modulo_fk'];
    if (!in_array($campo, $permitidos)) {
        echo json_encode(["success" => false, "message" => "Campo no permitido"]);
        exit;
    }

    $sql = "UPDATE modulos SET $campo = :valor WHERE id_modulo = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':valor', $valor);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Campo actualizado correctamente"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error al actualizar", "debug" => $e->getMessage()]);
}
?>