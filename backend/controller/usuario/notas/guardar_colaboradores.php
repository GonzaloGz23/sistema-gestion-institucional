<?php
require_once "../../../config/database.php";
$data = json_decode(file_get_contents("php://input"), true);
$colaboradores = $data['colaboradores'];

$success = true;

foreach ($colaboradores as $colab) {
    $id_usuario = intval($colab['id_usuario']);
    $id_nota = intval($colab['id_nota']);

    try {
        // Eliminar duplicados antes de insertar
        $deleteQuery = "DELETE FROM collaboradores WHERE id_usuario = :id_usuario AND id_nota = :id_nota";
        $stmtDelete = $pdo->prepare($deleteQuery);
        $stmtDelete->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtDelete->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
        $stmtDelete->execute();

        // Insertar el nuevo registro
        $query = "INSERT INTO collaboradores (id_usuario, id_nota) VALUES (:id_usuario, :id_nota)";
        $stmtInsert = $pdo->prepare($query);
        $stmtInsert->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtInsert->bindParam(':id_nota', $id_nota, PDO::PARAM_INT);
        $stmtInsert->execute();
    } catch (PDOException $e) {
        $success = false;
        // Puedes agregar un log de errores aquí si es necesario
        // error_log("Error en la inserción de colaboradores: " . $e->getMessage());
    }
}

echo json_encode(["success" => $success]);
?>