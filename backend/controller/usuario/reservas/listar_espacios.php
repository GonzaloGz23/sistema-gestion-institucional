<?php
require_once '../../../config/database.php';

header('Content-Type: application/json');

try {
    $sql = "
        SELECT 
            r.id_reserva,
            r.fecha,
            r.hora_inicio,
            r.hora_fin,
            r.detalle,
            e.alias AS espacio,
            eq.alias AS equipo
        FROM reservas r
        INNER JOIN espacios_reservables e ON r.id_espacio = e.id_espacio
        LEFT JOIN equipos eq ON r.id_equipo = eq.id_equipo
        WHERE r.eliminado = 0
        ORDER BY r.fecha DESC, r.hora_inicio ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $espacios
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar espacios: " . $e->getMessage()
    ]);
}
