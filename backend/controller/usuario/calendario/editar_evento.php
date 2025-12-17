<?php
require_once '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $titulo = trim($_POST['title'] ?? '');
        $descripcion = trim($_POST['description'] ?? '');
        $tipo_evento = $_POST['tipo-evento'] ?? '';
        $color = $_POST['color'] ?? '#3788d8';
        $start = $_POST['start'] ?? null;
        $end = $_POST['end'] ?? null;

        if (!$id || !$titulo || !$start || !$end) {
            throw new Exception("Datos incompletos.");
        }

        $inicio = new DateTime($start);
        $fin = new DateTime($end);

        if ($inicio >= $fin) {
            throw new Exception("La fecha de fin debe ser mayor que la de inicio.");
        }

        // 🚀 Actualizar evento
        $sql = "UPDATE eventos 
                SET titulo = :titulo,
                    descripcion = :descripcion,
                    color = :color,
                    start = :start,
                    end = :end
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':color' => $color,
            ':start' => $inicio->format('Y-m-d H:i:s'),
            ':end' => $fin->format('Y-m-d H:i:s'),
            ':id' => $id
        ]);

        // 👉 Si es Personalizado, actualizar asignaciones
        if ($tipo_evento === 'Personalizado') {
            // Eliminar todas las asignaciones actuales
            $pdo->prepare("DELETE FROM eventos_asignaciones WHERE id_evento = :id_evento")
                ->execute([':id_evento' => $id]);

            // Control extra: no permitir empleados y equipos a la vez
            if (!empty($_POST['empleadosSeleccionados']) && !empty($_POST['equiposSeleccionados'])) {
                throw new Exception("No podés asignar empleados y equipos al mismo tiempo.");
            }

            // Insertar empleados
            if (!empty($_POST['empleadosSeleccionados'])) {
                foreach ($_POST['empleadosSeleccionados'] as $idEmpleado) {
                    $pdo->prepare("
                        INSERT INTO eventos_asignaciones (id_evento, id_empleado)
                        VALUES (:id_evento, :id_empleado)
                    ")->execute([
                        ':id_evento' => $id,
                        ':id_empleado' => $idEmpleado
                    ]);
                }
            }

            // Insertar equipos
            if (!empty($_POST['equiposSeleccionados'])) {
                foreach ($_POST['equiposSeleccionados'] as $idEquipo) {
                    $pdo->prepare("
                        INSERT INTO eventos_asignaciones (id_evento, id_equipo)
                        VALUES (:id_evento, :id_equipo)
                    ")->execute([
                        ':id_evento' => $id,
                        ':id_equipo' => $idEquipo
                    ]);
                }
            }
        }

        // 🎯 Redirigir de nuevo al calendario
        header("Location: ../../../../pages/user/calendario.php");
        exit;

    } catch (Exception $e) {
        echo "❌ Error al editar el evento: " . $e->getMessage();
        exit;
    }
} else {
    echo "⚠️ Acceso no permitido.";
    exit;
}
