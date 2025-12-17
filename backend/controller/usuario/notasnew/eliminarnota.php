<?php
include '../../../../backend/config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['id_nota'])) {
            $id_nota = $data['id_nota'];
            

            $stmt = $pdo->prepare("UPDATE notas SET estado = 0 WHERE id_notas = ?");
            $stmt->execute([$id_nota]);

            echo json_encode([
                'success' => true,
                'message' => 'Se ha Eliminado'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar'
            ]);
        }

    } catch (Throwable $th) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $th->getMessage()
        ]);
    }
}
?>
