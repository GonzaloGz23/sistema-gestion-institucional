<?php

use Soap\Sdl;

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
       if (!empty($_POST['id_lista_tarea']) ) {
        $id_lista=$_POST['id_lista_tarea']; 
      
      
        $stmt = $pdo->prepare("UPDATE `nota_lista` SET `estados` = ? WHERE `id_nota_lista` = ?");
        $stmt->execute([ 0, $id_lista]);
        $id_lista_tarea = $pdo->lastInsertId(); // 🔥 ID del insert

        echo json_encode([
            'success' => true,
            'message' => 'Nota creada exitosamente',
            'id_lista_tarea' => $id_lista_tarea // ← Devuelve el ID al JS
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