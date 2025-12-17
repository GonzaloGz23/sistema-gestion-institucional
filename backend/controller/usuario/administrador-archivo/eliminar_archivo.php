<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

try {
    $id = $_POST['id'];

    $query = "UPDATE `archivo_carpeta` SET `visible` = 'no' WHERE `id_archivo` = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo 'ok';
    } else {
        // Si no se actualizó ninguna fila, puedes devolver un mensaje diferente o un código de error.
        echo 'No se encontró la carpeta o no se realizaron cambios.';
    }
} catch (PDOException $th) {
    echo 'Excepción capturada: ', $th->getMessage(), "\n";
}
