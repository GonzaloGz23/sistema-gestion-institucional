<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id_modulo, nombre, perfil, orden FROM modulos WHERE activo = 'Activo'");
    $modulos = $stmt->fetchAll();

    echo json_encode(['success' => true, 'modulos' => $modulos]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener módulos',
        'debug' => $e->getMessage()
    ]);
}
