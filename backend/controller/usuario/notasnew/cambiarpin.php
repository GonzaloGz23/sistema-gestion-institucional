<?php
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

header('Content-Type: application/json');

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!empty($data['id_nota']) && isset($data['pineada'])) {
            $id_nota = $data['id_nota'];
            $pineada = $data['pineada'];

            $stmt = $pdo->prepare("UPDATE `notas` SET `esta_pineada` = ? WHERE `id_notas` = ?");
            $stmt->execute([$pineada, $id_nota]);

            echo json_encode([
                'success' => true,
                'message' => 'Pin actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Datos incompletos'
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
