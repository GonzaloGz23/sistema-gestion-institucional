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
       if (!empty($_POST['id_nota']) && !empty($_POST['tarea']) ) {
        $id_nota=$_POST['id_nota']; 
        $tarea=$_POST['tarea']; 
        $chekeado=array_key_exists("isChecked",$_POST)?$_POST['isChecked']:0;
      
        $stmt = $pdo->prepare("INSERT INTO `nota_lista`(`lista`, `chequeado`, `rela_notas`) VALUES (?,?,?)");
        $stmt->execute([$tarea, $chekeado, $id_nota]);
     $id_lista_tarea = $pdo->lastInsertId(); // 🔥 ID del insert

        echo json_encode([
            'success' => true,
            'message' => 'Nota creada exitosamente',
            'id_lista_tarea' => $id_lista_tarea,
            'list_check' => $chekeado,
            
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