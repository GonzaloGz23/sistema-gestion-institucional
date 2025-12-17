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
            r.id_equipo,
            e.alias AS espacio,
            d.alias AS edificio,
            eq.alias AS equipo
        FROM reservas r
        INNER JOIN espacios_reservables e ON r.id_espacio = e.id_espacio
        INNER JOIN edificios d ON e.id_edificio = d.id_edificio
        LEFT JOIN equipos eq ON r.id_equipo = eq.id_equipo
        WHERE r.eliminado = 0
        ORDER BY r.fecha DESC, r.hora_inicio ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $reservas
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al listar reservas: " . $e->getMessage()
    ]);
}
