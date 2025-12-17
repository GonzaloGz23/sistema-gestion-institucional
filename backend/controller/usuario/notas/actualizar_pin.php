<?php   
require_once "../../../config/database.php";

// Verifica si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lee los datos JSON enviados desde el frontend
    $data = json_decode(file_get_contents('php://input'), true);

    // Verifica si se recibieron los datos necesarios
    if (isset($data['note_id']) && isset($data['is_pinned'])) {
        $noteId = intval($data['note_id']); // Asegúrate de que sea un entero
        $isPinned = $data['is_pinned']; // Recibirá true o false

        // Convierte el valor booleano a un formato que tu base de datos entienda
        $pinnedValue = $isPinned ? 'si' : 'no'; // Asumiendo que en tu DB usas 'si' y 'no'

        try {
            // Prepara la consulta SQL para actualizar el estado de pineado
            $stmt = $pdo->prepare("UPDATE notas SET esta_pineada = :esta_pineada WHERE id_notas = :id_notas");

            // Vincula los parámetros
            $stmt->bindParam(':esta_pineada', $pinnedValue, PDO::PARAM_STR);
            $stmt->bindParam(':id_notas', $noteId, PDO::PARAM_INT);

            // Ejecuta la consulta
            if ($stmt->execute()) {
                // La actualización fue exitosa
                $response = ['success' => true];
            } else {
                // Hubo un error al ejecutar la consulta
                $response = ['success' => false, 'error' => 'Error al actualizar la nota.'];
            }
        } catch (PDOException $e) {
            // Error de conexión o consulta
            $response = ['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()];
        }

        // Establece el tipo de contenido de la respuesta a JSON
        header('Content-Type: application/json');

        // Envía la respuesta JSON al frontend
        echo json_encode($response);

    } else {
        // No se recibieron los datos necesarios
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Datos de nota o estado de pineado no recibidos.']);
    }
} else {
    // La solicitud no es POST
    header('HTTP/1.1 405 Method Not Allowed');
    header('Allow: POST');
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
}

// Cierra la conexión a la base de datos (opcional, PDO se cierra al finalizar el script)
$pdo = null;
?>