<?php
require_once '../../../config/database.php';

// Verificamos que los datos necesarios estén presentes
if (!isset($_POST['nombre']) || !isset($_POST['id_equipo'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros obligatorios']);
    exit;
}

$nombre = strtolower(trim($_POST['nombre']));
$id_equipo = (int) $_POST['id_equipo'];

if ($nombre === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre no puede estar vacío']);
    exit;
}

try {
    // 1. Verificamos si ya existe una etiqueta activa con ese nombre
    $stmt = $pdo->prepare("SELECT id_etiqueta FROM solicitudes_etiquetas WHERE nombre = :nombre AND id_equipo = :id_equipo AND borrado = 0");
    $stmt->execute([
        ':nombre' => $nombre,
        ':id_equipo' => $id_equipo
    ]);

    if ($row = $stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe una etiqueta activa con ese nombre']);
        exit;
    }

    // 2. Verificamos si existe una etiqueta con ese nombre pero eliminada (borrado = 1)
    $stmt = $pdo->prepare("SELECT id_etiqueta FROM solicitudes_etiquetas WHERE nombre = :nombre AND id_equipo = :id_equipo AND borrado = 1");
    $stmt->execute([
        ':nombre' => $nombre,
        ':id_equipo' => $id_equipo
    ]);

    if ($row = $stmt->fetch()) {
        // Reactivar etiqueta
        $stmtUpdate = $pdo->prepare("UPDATE solicitudes_etiquetas SET borrado = 0 WHERE id_etiqueta = :id_etiqueta");
        $stmtUpdate->execute([':id_etiqueta' => $row['id_etiqueta']]);

        echo json_encode([
            'success' => true,
            'id_etiqueta' => $row['id_etiqueta']
        ]);
        exit;
    }

    // 3. Insertar nueva etiqueta
    $stmt = $pdo->prepare("INSERT INTO solicitudes_etiquetas (nombre, id_equipo, borrado) VALUES (:nombre, :id_equipo, 0)");
    $stmt->execute([
        ':nombre' => $nombre,
        ':id_equipo' => $id_equipo
    ]);

    echo json_encode([
        'success' => true,
        'id_etiqueta' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear la etiqueta: ' . $e->getMessage()]);
}
