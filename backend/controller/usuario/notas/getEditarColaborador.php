<?php
require_once "../../../config/session_config.php";
include '../../../../backend/config/database.php';
require_once '../../../../pages/common/functions.php';
header('Content-Type: application/json');

$idNota = $_POST['id_nota'] ?? null;
$idUsuario = $_POST['id_usuario'] ?? null;

if ($idNota && $idUsuario) {
    // Verificar si ya existe
    $check = $pdo->prepare("SELECT * FROM collaboradores WHERE id_nota = ? AND id_usuario = ?");
    $check->execute([$idNota, $idUsuario]);

    if ($check->rowCount() > 0) {
        // Ya existe, actualizar estado a 1
        $update = $pdo->prepare("UPDATE collaboradores SET estado = 1 WHERE id_nota = ? AND id_usuario = ?");
        $update->execute([$idNota, $idUsuario]);
    } else {
        // No existe, insertar nuevo
        $insert = $pdo->prepare("INSERT INTO collaboradores (id_nota, id_usuario, estado) VALUES (?, ?, 1)");
        $insert->execute([$idNota, $idUsuario]);
    }

   
    // Obtener datos del usuario
    $stmt = $pdo->prepare("SELECT nombre, apellido FROM empleados WHERE id_empleado = ?");
    $stmt->execute([$idUsuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql_notificacion="
            SELECT 
                fat.token_equipo,
                n.titulo tituloNota,
                n.id_usuario creador,
                e.nombre,
                e.apellido,
                ec.nombre nombreCol,
                ec.apellido apellidoCol,
                ff.titulo_notificacion,
                ff.cuerpo_notificacion,
                ff.imagen_notificacion,
                ff.link_ref,
                c.id_usuario rela_usuario,
                fat.firebase_app_tokensid

            FROM `notas` n
            inner JOIN collaboradores c on c.id_nota = n.id_notas
            inner JOIN empleados e on e.id_empleado = n.id_usuario  
            inner JOIN empleados ec on ec.id_empleado= c.id_usuario 
            inner join firebase_app_tokens fat on fat.rela_usuario=ec.id_empleado and fat.activo=1
            inner join firebase_app_msg ff on ff.tema=:tema
            where n.id_notas=$idNota and ec.id_empleado=$idUsuario
            ";
            sendAllTitle(
                $pdo,
                "NotasCompartidas",
                null,
                array(
                    "title_cols" => ["nombre", "apellido"],
                    "body_cols" => ["apellidoCol","nombreCol","tituloNota"]
                ),
                $sql_notificacion
            );

    if ($user) {
         
        $siglas = strtoupper(substr($user['nombre'], 0, 1) . substr($user['apellido'], 0, 1));
        echo json_encode([
            'status' => 'ok',
            'id' => $idUsuario,
            'nombre' => $user['nombre'],
            'apellido' => $user['apellido'],
            'siglas' => $siglas
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Empleado no encontrado']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
?>
