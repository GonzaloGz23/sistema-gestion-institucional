<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

// Recibir datos
$id_espacio = $_POST['id_espacio'] ?? null;
$id_empleado = $_POST['id_empleado'] ?? null; // Debería venir de sesión, por ahora desde POST
$id_equipo = $_POST['id_equipo'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$hora_inicio = $_POST['hora_inicio'] ?? null;
$hora_fin = $_POST['hora_fin'] ?? null;
$detalle = $_POST['detalle'] ?? null;

// Validaciones obligatorias
if (!$id_espacio || !$id_empleado || !$fecha || !$hora_inicio || !$hora_fin) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan campos obligatorios.'
    ]);
    exit;
}

// Validar fecha mínima (hoy o futuro)
if ($fecha < date('Y-m-d')) {
    echo json_encode([
        'success' => false,
        'message' => 'No se puede reservar una fecha pasada.'
    ]);
    exit;
}

// Validar horario logico
if ($hora_inicio >= $hora_fin) {
    echo json_encode([
        'success' => false,
        'message' => 'La hora de fin debe ser posterior a la de inicio.'
    ]);
    exit;
}

// Validar superposición
try {
    $sql = "
        SELECT COUNT(*) 
        FROM reservas 
        WHERE 
            id_espacio = :id_espacio
            AND fecha = :fecha
            AND eliminado = 0
            AND (
                :hora_inicio < hora_fin AND hora_inicio < :hora_fin
            )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_espacio' => $id_espacio,
        ':fecha' => $fecha,
        ':hora_inicio' => $hora_inicio,
        ':hora_fin' => $hora_fin
    ]);

    $conflictos = $stmt->fetchColumn();

    if ($conflictos > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una reserva en ese rango horario.'
        ]);
        exit;
    }

    // Obtener fecha actual desde PHP
    $creadoEn = date('Y-m-d H:i:s');

    // Insertar reserva
    $insert = "
    INSERT INTO reservas (
        id_espacio,
        id_empleado,
        id_equipo,
        fecha,
        hora_inicio,
        hora_fin,
        detalle,
        creado_en,
        eliminado
    ) VALUES (
        :id_espacio,
        :id_empleado,
        :id_equipo,
        :fecha,
        :hora_inicio,
        :hora_fin,
        :detalle,
        :creado_en,
        0
    )
";

    $stmt = $pdo->prepare($insert);
    $stmt->execute([
        ':id_espacio' => $id_espacio,
        ':id_empleado' => $id_empleado,
        ':id_equipo' => $id_equipo ?: null,
        ':fecha' => $fecha,
        ':hora_inicio' => $hora_inicio,
        ':hora_fin' => $hora_fin,
        ':detalle' => $detalle,
        ':creado_en'   => $creadoEn
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Reserva registrada correctamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar reserva: ' . $e->getMessage()
    ]);
}