<?php
/**
 * Controlador para cambiar estado de capacitaciones
 * Archivo: backend/controller/admin/revision-capacitaciones/cambiar_estado.php
 * 
 * Maneja el workflow de estados: en_espera → en_revision → aprobado
 * Permite también retrocesos manuales según la lógica de negocio
 */

// Incluir configuraciones necesarias
require_once '../../../config/database_courses.php';
require_once '../../../config/database.php';
require_once '../../../config/session_config.php';
include_once './in_code.php';
// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

$esLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
$urlcabecera = $esLocal ? 'http://localhost/newLandingPage/' : 'https://example.com/training/';

$cod = new IdEncoder();

// Verificar que el usuario esté autenticado
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Usuario no autenticado'
    ]);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido. Use POST.'
    ]);
    exit;
}
// Verificar que la capacitación existe y no está eliminada
$dni = $_POST["dni"];
$genero = $_POST["genero"];

$sqlCheck = "SELECT * FROM profesores WHERE dni = :dni and genero=:genero limit 1";
$stmt = $pdoCourses->prepare($sqlCheck);
$stmt->execute(['dni' => $dni, "genero" => $genero]);

$profesor = $stmt->fetch();

echo json_encode(array(
    "data" => $profesor
));

