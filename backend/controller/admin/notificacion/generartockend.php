<?php
// Firebase service account credentials should be configured via environment variables
// $serviceAccountPath = __DIR__ . '/your-firebase-adminsdk-file.json';
$serviceAccountPath = __DIR__ . '/your-firebase-adminsdk-file.json';
$credentials = json_decode(file_get_contents($serviceAccountPath), true);
$privateKey = $credentials['private_key'];
$clientEmail = $credentials['client_email'];

// === Paso 2: Crear el JWT y obtener el access_token ===
function base64url_encode($data)
{
    return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
}

$now = time();
$jwtHeader = base64url_encode(['alg' => 'RS256', 'typ' => 'JWT']);
$jwtPayload = base64url_encode([
    'iss' => $clientEmail,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600
]);
$jwtToSign = $jwtHeader . '.' . $jwtPayload;

openssl_sign($jwtToSign, $signature, $privateKey, 'sha256WithRSAEncryption');
$jwtSigned = $jwtToSign . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    'assertion' => $jwtSigned
]));
$response = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($response, true);
if (!isset($tokenData['access_token'])) {
    die('Error obteniendo access_token: ' . $response);
}
$accessToken = $tokenData['access_token'];

// === Paso 3: Enviar notificación a FCM ===
$projectId = $credentials['project_id'];
$tokenDestino = 'AQUI_TU_TOKEN_DEL_DISPOSITIVO';

$message = [
    "message" => [
        "token" => $tokenDestino,
        "notification" => [
            "title" => "¡Nueva notificación!",
            "body" => "Completa el formulario ahora.",
        ],
        "webpush" => [
            "fcm_options" => [
                "link" => "https://tusitio.com/formulario"
            ]
        ]
    ]
];

$ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json; UTF-8'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
$response = curl_exec($ch);
curl_close($ch);

echo 'Respuesta de FCM: ' . $response;
