<?php
require_once "../../../config/database.php";

$response = ["success" => true, "error" => "Error desconocido"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id_nota']) && !empty($data['id_nota']) && isset($data['nueva_etiqueta']) && !empty(trim($data['nueva_etiqueta']))) {
        $id_nota = intval($data['id_nota']);
        $nueva_etiqueta = trim($data['nueva_etiqueta']);

        // Verificar si ya existe un registro con el mismo id_nota y etiqueta en notas_etiquetas
        $query_check_etiquetas = "SELECT COUNT(*) FROM notas_etiquetas WHERE id_nota = ? AND descripcion_etiqueta = ?";
        $stmt_check_etiquetas = $pdo->prepare($query_check_etiquetas);
        $stmt_check_etiquetas->execute([$id_nota, $nueva_etiqueta]);
        $count_etiquetas = $stmt_check_etiquetas->fetchColumn();

        // Verificar si ya existe un registro con el mismo id_nota y etiqueta en la tabla notas
        $query_check_notas = "SELECT COUNT(*) FROM notas WHERE id_notas = ? AND etiqueta = ?";
        $stmt_check_notas = $pdo->prepare($query_check_notas);
        $stmt_check_notas->execute([$id_nota, $nueva_etiqueta]);
        $count_notas = $stmt_check_notas->fetchColumn();

        if ($count_etiquetas == 0 && $count_notas == 0) {
            // Si no existe, insertar la nueva etiqueta
            $query_insert = "INSERT INTO notas_etiquetas (id_nota, descripcion_etiqueta) VALUES (?, ?)";
            $stmt_insert = $pdo->prepare($query_insert);

            if ($stmt_insert->execute([$id_nota, $nueva_etiqueta])) {
                $response["success"] = true;
            } else {
                $response["error"] = "Error al agregar la etiqueta a la tabla notas_etiquetas.";
            }
        } else {
            // Si ya existe, indicar que la etiqueta ya está presente
            $response["success"] = false;
            $response["error"] = "La etiqueta ya está asociada a esta nota.";
        }
    } else {
        $response["error"] = "Datos incompletos para agregar la etiqueta.";
    }
}

header("Content-Type: application/json");
echo json_encode($response);
?>