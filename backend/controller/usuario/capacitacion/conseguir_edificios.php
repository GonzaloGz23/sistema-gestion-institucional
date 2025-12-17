<?php
require_once "../../../config/database.php";

header('Content-Type: application/json'); // Tell the client the response is JSON


// --- Your SQL Query ---
$sql = "SELECT `id_edificio`, `alias`, `direccion` FROM `edificios` WHERE estado = 'habilitado' AND borrado = 0";

try {
    $stmt = $pdo->query($sql);
    $edificios = $stmt->fetchAll(); // Get all results

    // Output the data as JSON
    echo json_encode($edificios);

} catch (\PDOException $e) {
    // In a real application, log this error and return a generic error
    echo json_encode(['error' => 'Database query error']);
    // For debugging: 'Database query error: ' . $e->getMessage()
}

?>