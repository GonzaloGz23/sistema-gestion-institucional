<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Leer el cuerpo de la solicitud JSON
$json_data = file_get_contents('php://input');
$data = json_decode($json_data);

if ($data !== null) { // Verificar si el JSON se decodificó correctamente
    $idEquipo = $data->equip;
    $carpeta = $data->carpeta;
    $idUsuario = $data->usuario;
    $carpetapadre = $data->padrecarpeta;

    try {
        // Insertar la nueva carpeta
        $informacionsubcarpeta = "INSERT INTO carpeta (nombre_carpeta, rela_grupo, rela_usuario, grupal, tipo_relacion) VALUES (?, ?, ?, 'no', '1')";
        $stmt = $pdo->prepare($informacionsubcarpeta);
        $stmt->execute([$carpeta, $idEquipo, $idUsuario]);
        $subcarpeta = $pdo->lastInsertId(); // Obtener el ID de la carpeta insertada

        // Insertar la relación subcarpeta
        $relasubcarpeta = "INSERT INTO subcarpeta (carpeta_padre, carpeta_hijo) VALUES (?, ?)";
        $stmt = $pdo->prepare($relasubcarpeta);
        $stmt->execute([$carpetapadre, $subcarpeta]);

        // Enviar una respuesta JSON de éxito
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Subcarpeta creada con éxito.']);
    } catch (PDOException $e) {
        // Enviar una respuesta JSON de error
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al crear la subcarpeta: ' . $e->getMessage()]);
    }
} else {
    // Enviar una respuesta JSON de error si el JSON no es válido
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos.']);
}
?>