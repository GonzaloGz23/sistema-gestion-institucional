<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

$json = file_get_contents('php://input');
$data = json_decode($json);

if ($data) {
    $idEquipo = $data->equip;
    $carpeta = $data->carpeta;
    $idUsuario = $data->usuario;
    $carpetapadre = $data->padrecarpeta;
    $zonaHorariaArgentina = new DateTimeZone('America/Argentina/Buenos_Aires');
    $fechaHoraActual = new DateTime('now', $zonaHorariaArgentina);
    $actual = $fechaHoraActual->format('Y-m-d H:i:s');

    try {
        if ($carpetapadre) {
            // Lógica para crear subcarpeta
            $informacionsubcarpeta = "INSERT INTO carpeta (nombre_carpeta, rela_grupo, rela_usuario, grupal, tipo_relacion, fecha_actual) VALUES (?, ?, ?, 'no', '1', ?)";
            $stmt = $pdo->prepare($informacionsubcarpeta);
            $stmt->execute([$carpeta, $idEquipo, $idUsuario, $actual]);
            $subcarpeta = $pdo->lastInsertId();

            $relasubcarpeta = "INSERT INTO subcarpeta (carpeta_padre, carpeta_hijo) VALUES (?, ?)";
            $stmt = $pdo->prepare($relasubcarpeta);
            $stmt->execute([$carpetapadre, $subcarpeta]);

            echo json_encode(['success' => true, 'message' => 'Subcarpeta creada con éxito.']);
        } else {
            // Lógica para crear carpeta principal
            $informacionsubcarpeta = "INSERT INTO carpeta (nombre_carpeta, rela_grupo, rela_usuario, grupal, tipo_relacion, fecha_actual) VALUES (?, ?, ?, 'no', '2', ?)";
            $stmt = $pdo->prepare($informacionsubcarpeta);
            $stmt->execute([$carpeta, $idEquipo, $idUsuario, $actual]);
            $subcarpeta = $pdo->lastInsertId();

            $relasubcarpeta = "INSERT INTO subcarpeta (carpeta_hijo) VALUES (?)";
            $stmt = $pdo->prepare($relasubcarpeta);
            $stmt->execute([$subcarpeta]);

            echo json_encode(['success' => true, 'message' => 'Carpeta creada con éxito.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos.']);
}
?>