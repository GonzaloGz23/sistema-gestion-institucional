<?php
require_once '../../../config/database.php';
// Incluir configuración de sesión y validar usuario
require_once '../../../config/session_config.php';
// Firebase service account credentials should be configured via environment variables
// include './your-firebase-adminsdk-file.json';
header('Content-Type: application/json');

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();
$iduser = $usuarioActual['id'];
$idequipo = $usuarioActual['id_equipo'];
$id_general = 1;

// El token debe ser traído de la base de datos, por ejemplo:
$tokenDestino = 'TOKEN_FCM_QUE_GUARDASTE_EN_BASE_DE_DATOS';

try {
    $conNotificacion = $pdo->prepare("
        SELECT * FROM `notificacion`
        WHERE estado = 0 AND (
            `id_empleado` = ? OR
            `id_equipo` = ? OR
            `id_general` = ?
        )
        ORDER BY fecha_creacion DESC
        LIMIT 1
    ");
    $conNotificacion->execute([$iduser, $idequipo, $id_general]);
    $datos_notifi = $conNotificacion->fetch(PDO::FETCH_ASSOC);

    if ($datos_notifi) {
        // Marcar como leída
        $update = $pdo->prepare("UPDATE notificacion SET estado = 'leído' WHERE id_notificacion = ?");
        $update->execute([$datos_notifi['id_notificacion']]);

        // Prepara notificación
        $notification = [
            'title' => $datos_notifi['titulo'],
            'body' => 'Completa el siguiente formulario',
            'icon' => $datos_notifi['icono'],
            'click_action' => $datos_notifi['link']
        ];

        $data = [
            'to' => $tokenDestino,
            'notification' => $notification,
            'priority' => 'high'
        ];

        $serverKey = 'YOUR_FCM_SERVER_KEY'; // Firebase Cloud Messaging server key

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);

        echo json_encode([
            "success" => true,
            "response_firebase" => json_decode($result)
        ]);
    } else {
        echo json_encode(["mostrar" => false]);
    }
} catch (Throwable $th) {
    echo json_encode(["error" => $th->getMessage()]);
}
?>