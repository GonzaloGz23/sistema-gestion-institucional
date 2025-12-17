<?php
require_once "../../../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null; // Este $id es el id_capacitacion

if ($id) {
    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // 1. Obtener el id_eventos vinculado a la capacitación
        $queryGetEventoId = "SELECT `habilitado` FROM `grupos` WHERE `id_grupo` = :id_grup";
        $stmtGetEventoId = $pdo->prepare($queryGetEventoId);
        $stmtGetEventoId->bindParam(':id_grup', $id, PDO::PARAM_INT);
        $stmtGetEventoId->execute();
        // FetchColumn obtiene el valor de la primera columna del primer resultado
        $eventoId = $stmtGetEventoId->fetchColumn();

        // Verificar si se encontró un id_eventos (puede ser false si no hay fila o null si la columna es null)
        if ($eventoId === false || $eventoId === null) {
            // Si no se encuentra un evento vinculado, puedes decidir qué hacer:
            // Opción A: Lanzar un error porque la eliminación vinculada no es completa.
            // Opción B: Continuar y solo marcar la capacitación (menos recomendado para consistencia).
            // Elegimos la Opción A para mantener la integridad referencial implícita.
            throw new Exception("No se encontró un evento vinculado válido para la capacitación con ID: " . $id);
        }

        if ($eventoId === 'si') {
        // 2. Actualizar la columna 'borrar' en la tabla 'capacitacion'
        $queryUpdateCapacitacion = "UPDATE `grupos` SET `habilitado` = 'no' WHERE `id_grupo` = :id_gru";
        $stmtUpdateCapacitacion = $pdo->prepare($queryUpdateCapacitacion);
        $stmtUpdateCapacitacion->bindParam(':id_gru', $id, PDO::PARAM_INT);
        $stmtUpdateCapacitacion->execute(); // Ejecutar la actualización de capacitación

      }else if($eventoId === 'no') {
 $queryUpdateCapacitacion = "UPDATE `grupos` SET `habilitado` = 'si' WHERE `id_grupo` = :id_gru";
        $stmtUpdateCapacitacion = $pdo->prepare($queryUpdateCapacitacion);
        $stmtUpdateCapacitacion->bindParam(':id_gru', $id, PDO::PARAM_INT);
        $stmtUpdateCapacitacion->execute(); // Ejecutar la actualización de capacitación
        }
   
        // Si ambas actualizaciones se ejecutaron sin lanzar excepciones, confirmar la transacción
        $pdo->commit();

        echo json_encode(["success" => true, "message" => "Capacitación y evento asociado marcados como borrados."]);

    } catch (Exception $e) {
        // Si algo falla, revertir la transacción para deshacer cualquier cambio parcial
        $pdo->rollBack();

        // Manejar el error
        // Puedes loguear $e->getMessage() para depuración
        $errorMessage = "Error al marcar como borrado: " . $e->getMessage();
        echo json_encode(["success" => false, "error" => $errorMessage]);
    }
} else {
    echo json_encode(["success" => false, "error" => "ID de capacitación no válido proporcionado."]);
}
?>