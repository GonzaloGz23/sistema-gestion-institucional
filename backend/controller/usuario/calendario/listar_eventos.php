<?php
header('Content-Type: application/json');
require_once '../../../config/database.php';

try {
    $eventos = [];

    // Parámetros esperados
    $idUsuario = $_GET['id_usuario'] ?? null;
    $idEquipo = $_GET['id_equipo'] ?? null;
    $start = $_GET['start'] ?? null;
    $end = $_GET['end'] ?? null;

    if (!$idUsuario || !$idEquipo || !$start || !$end) {
        throw new Exception("Parámetros incompletos.");
    }

    // Normalizar fechas
    $start = substr($start, 0, 19);
    $end = substr($end, 0, 19);
    $start = str_replace('T', ' ', $start);
    $end = str_replace('T', ' ', $end);

    // Consulta principal
    $sql = "
        SELECT e.id, e.titulo, e.descripcion, e.tipo_evento, e.color, e.start, e.end, e.id_equipo_creador
        FROM eventos e
        WHERE e.borrado = 0
          AND e.start <= :end
          AND e.end >= :start
          AND (
              e.tipo_evento = 'Institucional'
              OR (e.tipo_evento = 'Equipo' AND e.id_equipo_creador = :id_equipo)
              OR (e.tipo_evento = 'Individual' AND e.id_creador = :id_usuario)
              OR (
                  e.tipo_evento = 'Personalizado'
                  AND (
                      e.id_equipo_creador = :id_equipo_personalizado
                      OR EXISTS (
                          SELECT 1
                          FROM eventos_asignaciones ea
                          WHERE ea.id_evento = e.id
                            AND (
                                ea.id_empleado = :id_usuario_asignado
                                OR ea.id_equipo = :id_equipo_asignado
                            )
                      )
                  )
              )
          )
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':start' => $start,
        ':end' => $end,
        ':id_equipo' => $idEquipo,
        ':id_usuario' => $idUsuario,
        ':id_equipo_personalizado' => $idEquipo,
        ':id_usuario_asignado' => $idUsuario,
        ':id_equipo_asignado' => $idEquipo
    ]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $evento = [
            'id' => $row['id'],
            'title' => $row['titulo'],
            'start' => $row['start'],
            'end' => $row['end'],
            'color' => $row['color'],
            'extendedProps' => [
                'descripcion' => $row['descripcion'],
                'tipo_evento' => $row['tipo_evento'],
                'id_equipo_creador' => $row['id_equipo_creador']
            ]
        ];

        // 🔥 Si es personalizado, agregar empleados/equipos asignados
        if ($row['tipo_evento'] === 'Personalizado') {
            $sqlAsignaciones = "
                SELECT id_empleado, id_equipo
                FROM eventos_asignaciones
                WHERE id_evento = :id_evento
            ";
            $stmtAsignaciones = $pdo->prepare($sqlAsignaciones);
            $stmtAsignaciones->execute([':id_evento' => $row['id']]);

            $empleados = [];
            $equipos = [];

            while ($asignacion = $stmtAsignaciones->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($asignacion['id_empleado'])) {
                    $empleados[] = $asignacion['id_empleado'];
                }
                if (!empty($asignacion['id_equipo'])) {
                    $equipos[] = $asignacion['id_equipo'];
                }
            }

            if (count($empleados)) {
                $evento['extendedProps']['personalizadoTipo'] = 'empleados';
                $evento['extendedProps']['empleadosSeleccionados'] = $empleados;
            } elseif (count($equipos)) {
                $evento['extendedProps']['personalizadoTipo'] = 'equipos';
                $evento['extendedProps']['equiposSeleccionados'] = $equipos;
            }
        }

        $eventos[] = $evento;
    }

    echo json_encode($eventos);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
