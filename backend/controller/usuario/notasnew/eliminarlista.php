
 <?php
 require_once "../../../config/session_config.php";
 require_once "../../../config/database.php";
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id_lista_tarea'] ?? null;

if ($id) {
    
        // UPDATE texto
        $stmt = $pdo->prepare("UPDATE nota_lista SET estados = 0 WHERE id_nota_lista = :id");
        $stmt->execute([ $id]);
    

    } 
    echo json_encode(['success'=>true]);
   

?>


