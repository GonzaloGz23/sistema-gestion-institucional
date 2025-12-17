 
<?php


try {
  require_once '../../backend/config/database.php';
  require_once "../../backend/config/session_config.php";
  require_once __DIR__ . '/functions.php';
  //code...
  $token_id = $_GET["deviceid"];
  if (!array_key_exists("deviceid", $_GET)) throw new Exception("No se encontro el dispositivo", 1);
  if (strlen($token_id) < 60) throw new Exception("El token no se recibio", 1);

  $add = add($pdo, $token_id);
  $usuario = obtenerUsuarioActual();
  sendAllTitle(
    $pdo,
    "Bienvenido",
    array(
      "rela_usuario" => $usuario["id"]
    ),
    array(
      "title_cols" => ["apellido", "nombre"]
    )
  );
  echo json_encode(
    array(
      "status" => true,
      "msg" => "Notificaciones activadas. Te mantendremos al tanto."
    )
  );
} catch (\Throwable $th) {
  echo json_encode(array("msg"=>$th->getMessage()));
}

?>

