<?php
require_once "../../../config/database.php";

$titulo = $_POST['titulo'] ?? '';
$contenido = $_POST['contenido'] ?? '';
$etiqueta = $_POST['etiqueta'] ?? '';
$recordatorio = $_POST['recordatorio'] ?? null;
$entidad = $_POST['entidads'] ?? '';
$pineado = $_POST['pineados'] ?? '';

$etiquetas = json_decode($_POST['etiquetas'] ?? '[]'); // Recibir etiquetas como JSON
$colaboradores = json_decode($_POST['colaboradores'] ?? '[]'); // Recibir etiquetas como JSON


// Validar y formatear la fecha y hora
if (!empty($recordatorio)) {
    $dateTime = DateTime::createFromFormat('Y-m-d\TH:i', $recordatorio);
    if ($dateTime) {
        $recordatorio = $dateTime->format('Y-m-d H:i:s');
    } else {
        echo json_encode(["success" => false, "error" => "Formato de fecha/hora inválido"]);
        exit;
    }
}

if (!empty($titulo) && !empty($contenido)) {
    try {
        $pdo->beginTransaction(); // Iniciar transacción

        $query = "INSERT INTO notas (titulo, contenido, etiqueta, recordatorio, id_usuario, esta_pineada) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$titulo, $contenido, $etiqueta, $recordatorio, $entidad, $pineado]);
        $noteId = $pdo->lastInsertId(); // Obtener el ID de la nota recién insertada

        // Insertar etiquetas en notas_etiquetas
        foreach ($etiquetas as $descripcionEtiqueta) {
            $queryEtiqueta = "INSERT INTO notas_etiquetas (id_nota, descripcion_etiqueta) VALUES (?, ?)";
            $stmtEtiqueta = $pdo->prepare($queryEtiqueta);
            $stmtEtiqueta->execute([$noteId, $descripcionEtiqueta]);
        }

 // Insertar etiquetas en collaborador
 foreach ($colaboradores as $descripcionColaborador) {
    $queryColaborador = "INSERT INTO collaboradores (id_usuario, id_nota) VALUES (?, ?)";
    $stmtColaborador = $pdo->prepare($queryColaborador);
    $stmtColaborador->execute([$descripcionColaborador, $noteId]);
}



        $pdo->commit(); // Confirmar transacción
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        $pdo->rollBack(); // Revertir transacción en caso de error
        echo json_encode(["success" => false, "error" => "Error al insertar la nota: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
}
?>