<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            m.id_modulo,
            m.nombre,
            m.perfil,
            m.ruta,
            m.orden,
            m.icono_svg,
            m.activo,
            COALESCE(m2.nombre, '-') AS modulo_referencia
        FROM modulos m
        LEFT JOIN modulos m2 ON m.id_modulo_fk = m2.id_modulo
        ORDER BY m.orden ASC, m.nombre ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $modulos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener los módulos",
        "debug" => $e->getMessage()
    ]);
}
?> 
