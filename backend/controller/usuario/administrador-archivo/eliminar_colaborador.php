<?php
require_once "../../../config/database.php";
date_default_timezone_set('America/Argentina/Buenos_Aires');

$data = json_decode(file_get_contents('php://input'), true);
$idCarpeta = $data['id'];
$idUsuario = $data['idusuario'];

$query = "DELETE FROM colaborador_carpetas WHERE id_carpeta = :idCarpeta AND id_colaborador = :idUsuario";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':idCarpeta', $idCarpeta, PDO::PARAM_INT);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);

try {
  $stmt->execute();
  echo json_encode(['success' => true]);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => "❌ Error al eliminar: " . $e->getMessage()]);
}
?>