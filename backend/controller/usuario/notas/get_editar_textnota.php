<?php

// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
       if (!empty($_POST['id_nota'])  && isset($_POST['nota'])) {
        $id_tarea=$_POST['id_nota']; 
        $tarea=$_POST['nota']; 
      
        $stmt = $pdo->prepare("UPDATE `notas` SET `contenido`=?  WHERE `id_notas`=?");
        $stmt->execute([$tarea, $id_tarea]);
        echo json_encode([
            'success' => true,
            'message' => 'Nota creada exitosamente'
        ]);
       }

    } catch (Throwable $th) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $th->getMessage()
        ]);
    }
}

?>