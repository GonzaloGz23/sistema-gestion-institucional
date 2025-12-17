<?php
require_once "../../../config/database.php";

$response = array("status" => "error", "message" => "Error desconocido");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lee el cuerpo de la solicitud como JSON
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (isset($data["descripcion_etiqueta"]) && !empty(trim($data["descripcion_etiqueta"]))) {
        $tag_name = trim($data["descripcion_etiqueta"]);

        // Consulta preparada para insertar la etiqueta
        $query = "INSERT INTO `etiquetas` (`descripcion_etiqueta`) VALUES (:tag_name)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':tag_name', $tag_name);

        if ($stmt->execute()) {
            // Obtener el ID de la etiqueta insertada
            $insert_id = $pdo->lastInsertId();

            $response["status"] = "success";
            $response["message"] = "Etiqueta agregada con éxito.";
            $response["id_etiqueta"] = $insert_id;
            $response["descripcion_etiqueta"] = $tag_name;
        } else {
            $response["message"] = "No se pudo agregar la etiqueta.";
        }
    } else {
        $response["message"] = "Nombre de etiqueta no proporcionado.";
    }
} else {
    $response["message"] = "Método de solicitud no permitido.";
}

header("Content-Type: application/json");
echo json_encode($response);
?>