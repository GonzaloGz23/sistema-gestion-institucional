<?php
include '../../../../backend/config/database.php';

if (!isset($_GET['id_pregunta'])) {
    die('Falta el ID');
}

$id_pregunta = $_GET['id_pregunta'];

// Consulta del archivo
$sql = "SELECT archivo, tipo_archivo FROM formulario_respuestas WHERE id_formulario_respuestas = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_pregunta]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['archivo'])) {
    die('Archivo no encontrado.');
}

// Si el archivo está codificado en base64:
$contenido = base64_decode($row['archivo']); // Si lo guardaste binario directo, omite esta línea
$tipoArchivo = $row['tipo_archivo'] ?? 'application/octet-stream';

// Opcional: forzar descarga
// header('Content-Disposition: attachment; filename="archivo_' . $id_pregunta . '"');

// Para mostrar el archivo (por ejemplo PDF, imagen, etc.)
header('Content-Type: ' . $tipoArchivo);
header('Content-Length: ' . strlen($contenido));

echo $contenido;
exit;
