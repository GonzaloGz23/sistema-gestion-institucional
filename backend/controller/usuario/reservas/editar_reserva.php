<?php
require_once '../../../config/database.php';
header('Content-Type: application/json');

// Recibir datos
$id_reserva = $_POST['id_reserva'] ?? null;
$id_espacio = $_POST['id_espacio'] ?? null;
$id_empleado = $_POST['id_empleado'] ?? null;
$id_equipo = $_POST['id_equipo'] ?? null;
$fecha = $_POST['fecha'] ?? null;
$hora_inicio = $_POST['hora_inicio'] ?? null;
$hora_fin = $_POST['hora_fin'] ?? null;
$detalle = $_POST['detalle'] ?? null;

// Validar campos obligatorios
if (!$id_reserva || !$id_espacio || !$id_empleado || !$id_equipo || !$fecha || !$hora_inicio || !$hora_fin) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan campos obligatorios.'
    ]);
    exit;
}

// Validar fecha no pasada
if ($fecha < date('Y-m-d')) {
    echo json_encode([
        'success' => false,
        'message' => 'No se puede modificar una reserva a una fecha pasada.'
    ]);
    exit;
}

// Validar que hora fin sea mayor a hora inicio
if ($hora_inicio >= $hora_fin) {
    echo json_encode([
        'success' => false,
        'message' => 'La hora de fin debe ser posterior a la de inicio.'
    ]);
    exit;
}

// Validar que la reserva pertenece al equipo del usuario
$sqlCheck = "SELECT id_equipo FROM reservas WHERE id_reserva = :id_reserva LIMIT 1";
$stmt = $pdo->prepare($sqlCheck);
$stmt->execute([':id_reserva' => $id_reserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    echo json_encode([
        'success' => false,
        'message' => 'Reserva no encontrada.'
    ]);
    exit;
}

if ($reserva['id_equipo'] != $id_equipo) {
    echo json_encode([
        'success' => false,
        'message' => 'No tenés permiso para editar esta reserva.'
    ]);
    exit;
}

// Bloquear edición si ya comenzó
$horaInicioSegundos = strlen($hora_inicio) === 5 ? $hora_inicio . ':00' : $hora_inicio;

$fechaHoraInicio = DateTime::createFromFormat('Y-m-d H:i:s', "$fecha $horaInicioSegundos");
$ahora = new DateTime('now');

if ($fechaHoraInicio <= $ahora) {
    echo json_encode([
        'success' => false,
        'message' => 'No se puede editar una reserva que ya comenzó.'
    ]);
    exit;
}


// Validar superposición con otras reservas (excepto consigo misma)
try {
    $sqlValidacion = "
        SELECT COUNT(*) 
        FROM reservas 
        WHERE 
            id_espacio = :id_espacio
            AND fecha = :fecha
            AND eliminado = 0
            AND id_reserva != :id_reserva
            AND (
                :hora_inicio < hora_fin AND hora_inicio < :hora_fin
            )
    ";

    $stmt = $pdo->prepare($sqlValidacion);
    $stmt->execute([
        ':id_espacio' => $id_espacio,
        ':fecha' => $fecha,
        ':hora_inicio' => $hora_inicio,
        ':hora_fin' => $hora_fin,
        ':id_reserva' => $id_reserva
    ]);

    $conflictos = $stmt->fetchColumn();

    if ($conflictos > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe otra reserva en ese rango horario.'
        ]);
        exit;
    }

    // Actualizar
    $actualizado_en = date('Y-m-d H:i:s');

    // Actualizar reserva
    $update = "
       UPDATE reservas SET
            id_espacio   = :id_espacio,
            id_empleado  = :id_empleado,
            id_equipo    = :id_equipo,
            fecha        = :fecha,
            hora_inicio  = :hora_inicio,
            hora_fin     = :hora_fin,
            detalle      = :detalle,
            actualizado_en = :actualizado_en
        WHERE id_reserva = :id_reserva
    ";

    $stmt = $pdo->prepare($update);
    $stmt->execute([
        ':id_espacio'      => $id_espacio,
        ':id_empleado'     => $id_empleado,
        ':id_equipo'       => $id_equipo,
        ':fecha'           => $fecha,
        ':hora_inicio'     => $hora_inicio,
        ':hora_fin'        => $hora_fin,
        ':detalle'         => $detalle,
        ':actualizado_en'  => $actualizado_en,
        ':id_reserva'      => $id_reserva
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Reserva actualizada correctamente.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar reserva: ' . $e->getMessage()
    ]);
}
