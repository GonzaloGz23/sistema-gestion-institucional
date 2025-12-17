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
       if (!empty($_POST['id_lista_tarea']) && !empty($_POST['tarea']) && isset($_POST['id_lista_tarea'])) {
        $id_tarea=$_POST['id_lista_tarea']; 
        $tarea=$_POST['tarea']; 
        $chekeado=$_POST['chekeado']; 

      
        $stmt = $pdo->prepare("UPDATE `nota_lista` SET `lista` = ?,chequeado=? WHERE `id_nota_lista` = ?");
        $stmt->execute([$tarea, $chekeado,$id_tarea]);
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