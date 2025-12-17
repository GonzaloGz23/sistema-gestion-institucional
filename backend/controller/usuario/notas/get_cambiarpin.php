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
       if (!empty($_POST['id_nota'])  ) {
        $id_nota=$_POST['id_nota']; 
        $pieado=$_POST['pineada']; 
      
        $stmt = $pdo->prepare("UPDATE `notas` SET `esta_pineada` = ? WHERE `id_notas` = ?");
        $stmt->execute([$pieado, $id_nota]);

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