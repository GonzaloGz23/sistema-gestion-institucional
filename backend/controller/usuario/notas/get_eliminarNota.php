<?php
include '../../../../backend/config/database.php';// ajustá el path si es necesario
try {
    //code...
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_nota'])) {
        $id_nota = intval($_POST['id_nota']);
    
        // Eliminar la nota (o marcarla como eliminada si tenés soft delete)
        $stmt = $pdo->prepare("UPDATE notas SET estado = 0 WHERE id_notas = ?");
        $success = $stmt->execute([$id_nota]);
    
        echo json_encode([
            'status' => 'ok']);
    } else {
        echo json_encode(['status' => false]);
    }
} catch (\Throwable $th) {
    echo $th;
}
?>
