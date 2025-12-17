<?php
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

        if (!$id) {
            throw new Exception("ID de evento no recibido.");
        }

        $sql = "UPDATE eventos SET borrado = 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

		header("Location: ../../../../pages/user/calendario.php");
        exit;
    } catch (Exception $e) {
        echo "❌ Error al eliminar el evento: " . $e->getMessage();
        exit;
    }
} else {
    echo "⚠️ Acceso no permitido.";
    exit;
}
