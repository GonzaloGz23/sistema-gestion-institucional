<?php
require_once "../../../config/database.php";
// Obtener el cuerpo de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);
// Verificar si se recibieron los IDs del material y la capacitación
if (isset($data['idMaterial']) && is_numeric($data['idMaterial']) && isset($data['idCapacitacion']) && is_numeric($data['idCapacitacion'])) {
    $idMaterial = $data['idMaterial'];
    $idCapacitacion = $data['idCapacitacion'];

    try {
        $pdo->beginTransaction();

        // 1. Obtener la ruta del archivo para eliminarlo del sistema de archivos
        $querySelect = "SELECT ruta_material, nombre_material FROM materiales WHERE id_material = :idMaterial AND id_capacitacion = :idCapacitacion";
        $stmtSelect = $pdo->prepare($querySelect);
        $stmtSelect->bindParam(':idMaterial', $idMaterial, PDO::PARAM_INT);
        $stmtSelect->bindParam(':idCapacitacion', $idCapacitacion, PDO::PARAM_INT);
        $stmtSelect->execute();
        $resultSelect = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($resultSelect && isset($resultSelect['ruta_material'])) {
            $rutaArchivo = $resultSelect['ruta_material'];
            $nombreArchivo = $resultSelect['nombre_material']; // Guardamos el nombre del archivo por si acaso

            // 2. Eliminar el registro de la base de datos
            $queryDelete = "DELETE FROM materiales WHERE id_material = :idMaterial AND id_capacitacion = :idCapacitacion";
            $stmtDelete = $pdo->prepare($queryDelete);
            $stmtDelete->bindParam(':idMaterial', $idMaterial, PDO::PARAM_INT);
            $stmtDelete->bindParam(':idCapacitacion', $idCapacitacion, PDO::PARAM_INT);
            $stmtDelete->execute();

            // Verificar si se eliminó el registro
            if ($stmtDelete->rowCount() > 0) {
                // 3. Intentar eliminar el archivo del sistema de archivos
                if (file_exists($rutaArchivo)) {
                    if (unlink($rutaArchivo)) {
                        $pdo->commit();
                        echo json_encode(["success" => true, "message" => "Material eliminado correctamente."]);
                    } else {
                        // Si no se pudo eliminar el archivo, hacer un rollback y enviar un error
                        $pdo->rollBack();
                        echo json_encode(["success" => false, "error" => "Error al eliminar el archivo del servidor, pero el registro en la base de datos fue eliminado."]);
                    }
                } else {
                    // Si el archivo no existe, pero el registro se eliminó, considerar como éxito parcial
                    $pdo->commit();
                    echo json_encode(["success" => true, "message" => "Registro del material eliminado. El archivo '$nombreArchivo' no se encontró en el servidor."]);
                }
            } else {
                $pdo->rollBack();
                echo json_encode(["success" => false, "error" => "No se encontró el material con ID '$idMaterial' para eliminar en la capacitación con ID '$idCapacitacion'."]);
            }
        } else {
            $pdo->rollBack();
            echo json_encode(["success" => false, "error" => "No se encontró la ruta del material con ID '$idMaterial' en la capacitación con ID '$idCapacitacion'."]);
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "error" => "Error al eliminar el material: " . $e->getMessage()]);
    }

} else {
    echo json_encode(["success" => false, "error" => "IDs de material o capacitación no válidos."]);
}
?>