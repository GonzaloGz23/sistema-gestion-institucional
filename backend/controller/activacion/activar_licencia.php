<?php
// Incluir configuración de sesión
require_once '../../config/session_config.php';
require_once '../../config/database.php';
header('Content-Type: application/json');

// Validar entrada
if (!isset($_POST['signUpName']) || empty(trim($_POST['signUpName']))) {
    echo json_encode(['success' => false, 'message' => 'El código de licencia es requerido.']);
    exit;
}

$codigo = trim($_POST['signUpName']);

try {
    // Verificar existencia y estado de la licencia
    $stmt = $pdo->prepare("SELECT id_licencia, activa FROM licencias WHERE codigo_licencia = :codigo LIMIT 1");
    $stmt->execute([':codigo' => $codigo]);
    $licencia = $stmt->fetch();

    if (!$licencia) {
        echo json_encode([
            'success' => false,
            'message' => 'El código ingresado no corresponde a ninguna licencia.'
        ]);
        exit;
    }

    if ($licencia['activa'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'La licencia ya fue activada anteriormente.'
        ]);
        exit;
    }

    // Activar licencia y asignar fecha de expiración
    $stmt = $pdo->prepare("
        UPDATE licencias 
        SET activa = 1, fecha_expiracion = DATE_ADD(NOW(), INTERVAL 1 YEAR) 
        WHERE id_licencia = :id
    ");
    $stmt->execute([':id' => $licencia['id_licencia']]);

    // Guardar en sesión para uso posterior
    $_SESSION['usuario']['id_licencia_activada'] = $licencia['id_licencia'];

    echo json_encode([
        'success' => true,
        'message' => 'La licencia fue activada correctamente.'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al validar la licencia.',
        'debug' => $e->getMessage()
    ]);
}
