<?php
// session_start();

function loadingConfigFirebase($pdo)
{
    $stmt = $pdo->prepare("
        select * from firebase_app_cred
    ");
    $stmt->execute();
    $cred = $stmt->fetchAll()[0];
    //var_dump($cred);
    return $cred;
}
function base64UrlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function testConnectGoogleFirebase($pdo)
{
    $now = time();
    $serviceAccount = loadingConfigFirebase($pdo);
    try {
        //code...
        $jwtHeader = ['alg' => 'RS256', 'typ' => 'JWT'];
        if (!array_key_exists("client_email", $serviceAccount)) {
            throw new Exception("No se encontro el client_email de google");
        }

        $jwtClaimSet = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];

        $base64Header = base64UrlEncode(json_encode($jwtHeader));
        $base64ClaimSet = base64UrlEncode(json_encode($jwtClaimSet));

        $unsignedJWT = $base64Header . '.' . $base64ClaimSet;

        // Firmar con la clave privada
        $privateKey = $serviceAccount['private_key'];
        $privateKey = str_replace("\\n", "\n", $privateKey);
        openssl_sign($unsignedJWT, $signature, $privateKey, 'SHA256');

        $base64Signature = base64UrlEncode($signature);

        $jwt = $unsignedJWT . '.' . $base64Signature;

        // Ahora hacer POST para intercambiar JWT por access token
        $postFields = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        /*if (isset($result['access_token'])) {
            echo "Access Token: " . $result['access_token'] . "\n";
        } else {
            echo "Error obteniendo token: " . $response . "\n";
        }

        //var_dump($result);
        echo "<br>";*/

        return $result;
    } catch (\Throwable $th) {
        throw $th;
    }
}
function sendNotificacions($array, $pdo)
{
    $result = testConnectGoogleFirebase($pdo);
    $ch = curl_init("https://fcm.googleapis.com/v1/projects/sistemainstitucional-7f982/messages:send");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $result['access_token']
    ]);
    $obj = '{
        "message": {
        "token": "' . $array["dispositivo"] . '",
        "notification": {
            "title": "' . $array["titulo_notificacion"] . '",
            "body": "' . $array["cuerpo_notificacion"] . '",
            "image": ""
        },
         "android": {
            "notification": {
                "sound": "default"
            }
        },
        "webpush": {
            "fcm_options": {
                "link": "' . $array["link_ref"] . '"
            }
        }
        }
    }';
    //echo "<br>".$obj."<br>";
    curl_setopt($ch, CURLOPT_POSTFIELDS, $obj);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "post");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);

    curl_close($ch);
    try {
        //code...
        $sql_inserrtar = "insert into firebase_app_logs(fecha,body,resultado,firebase_app_tokensid,rela_usuario) values(now(),'" . preg_replace('/\s+/', '', $obj) . "','" . preg_replace('/\s+/', '', $data) . "','" . $array["firebase_app_tokensid"] . "','" . $array["rela_usuario"] . "')";
        $stmTotal = $pdo->prepare($sql_inserrtar);
        $stmTotal->execute();
        unset($stmTotal);
    } catch (\Throwable $th) {
        throw $th;
    }

    //return $response;
}

function sendAllTitle($pdo, $tema, $where, $dataSchema, $sql = null)
{ //dataSchema["title_cols"]=>["apellido","nombre"]
    if ($sql == null) {

        $sql = "
            SELECT 
                d.token_equipo,
                e.nombre,
                e.apellido,
                ff.titulo_notificacion,
                ff.cuerpo_notificacion,
                ff.imagen_notificacion,
                ff.link_ref,
                d.rela_usuario,
                d.firebase_app_tokensid

            FROM `firebase_app_tokens` d
            inner join empleados e on e.id_empleado=d.rela_usuario
            inner join firebase_app_msg ff on ff.tema=:tema
            where d.activo=1        
        ";
    }

    $array_conditions = [];
    if (!empty($where)) {
        if (array_key_exists("rela_equipo", $where)) { //where["rela_equipo"]="1,2,3,4,5" || where["rela_usuario"]="1,2,3,4,5"
            $valor = $where["rela_equipo"];
            $sql .= " AND rela_equipo in({$valor})";
        }
        if (array_key_exists("rela_usuario", $where)) {
            $valor = $where["rela_usuario"];

            $sql .= " AND rela_usuario in({$valor})";
        }
    }
    $array_conditions[":tema"] = $tema;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($array_conditions);
    $resultado = $stmt->fetchAll();
    foreach ($resultado as $item) {

        $titulo = $item["titulo_notificacion"] . " ";
        $cuerpo_notif = $item["cuerpo_notificacion"] . " ";
        if (!empty($dataSchema)) {
            if (array_key_exists("title_cols", $dataSchema)) {
                foreach ($dataSchema["title_cols"] as $de) {
                    # code...

                    if (strpos($item["titulo_notificacion"], $de) !== false) {
                        $titulo = str_replace("{\$" . $de . "}", $item[$de], $titulo);
                    } else {
                        $titulo .= $item[$de] . " ";
                    }
                }
            }
            if (array_key_exists("body_cols", $dataSchema)) {
                foreach ($dataSchema["body_cols"] as $de) {
                    # code...
                    if (strpos($item["cuerpo_notificacion"], $de) !== false) {
                        $cuerpo_notif = str_replace("{\$" . $de . "}", $item[$de], $cuerpo_notif);
                    } else {
                        $cuerpo_notif .= $item[$de] . " ";
                    }
                }
            }
        }

        $array = array(
            "dispositivo" => $item["token_equipo"],
            "titulo_notificacion" => $titulo,
            "cuerpo_notificacion" => $cuerpo_notif,
            "imagen_notificacion" => $item["imagen_notificacion"],
            "link_ref" => $item["link_ref"],
            "rela_usuario" => $item["rela_usuario"],
            "firebase_app_tokensid" => $item["firebase_app_tokensid"]
        );
       // sleep(1);  // Espera 1 segundo
        sendNotificacions($array, $pdo);
    }
    return true;
}


function sendAll($pdo, $tema, $where = null, $datos = null)
{
    $sql = "
        SELECT 
            d.token_equipo,
            e.nombre,
            e.apellido,
            ff.titulo_notificacion,
            ff.cuerpo_notificacion,
            ff.imagen_notificacion,
            ff.link_ref,
            d.firebase_app_tokensid

        FROM `firebase_app_tokens` d
        inner join empleados e on e.id_empleado=d.rela_usuario
        inner join firebase_app_msg ff on ff.tema=:tema
        where d.activo=1        
    ";
    $array_conditions = [];
    if (!empty($where)) {
        if (array_key_exists("rela_equipo", $where)) {
            $valor = $where["rela_equipo"];
            $sql .= " AND rela_equipo in({$valor})";
        }
        if (array_key_exists("rela_usuario", $where)) {
            $valor = $where["rela_usuario"];

            $sql .= " AND rela_usuario in({$valor})";
        }
    }

    $array_conditions[":tema"] = $tema;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($array_conditions);
    $resultado = $stmt->fetchAll();
    foreach ($resultado as $item) {
        //var_dump($item);
        if (!empty($datos)) {
            # code...
            $titulo = str_replace(
                array_keys($datos),
                array_values($datos),
                $item["titulo_notificacion"]
            );

            $cuerpo_notif = str_replace(
                array_keys($datos),
                array_values($datos),
                $item["cuerpo_notificacion"]
            );
        } else {
            $titulo = $item["titulo_notificacion"];
            $cuerpo_notif = $item["cuerpo_notificacion"];
        }

        $array = array(
            "dispositivo" => $item["token_equipo"],
            "titulo_notificacion" => $titulo,
            "cuerpo_notificacion" => $cuerpo_notif,
            "imagen_notificacion" => $item["imagen_notificacion"],
            "link_ref" => $item["link_ref"],
            "rela_usuario" => $item["rela_usuario"],
            "firebase_app_tokensid" => $item["firebase_app_tokensid"]

        );
        sendNotificacions($array, $pdo);
    }
    return true;
}


function add($pdo, $token)
{
    try {
        //no te olvides de que tenes que preguntar si ya no existe otro para no añadir repetidos
        $usuarioActual = obtenerUsuarioActual();
        if (!$usuarioActual) {
            throw new Exception("Usuario no autenticado");
        }
        $stmTotal = $pdo->prepare("
            select count(1) as cantidad from  `firebase_app_tokens`   where token_equipo=:token_id 
            and rela_usuario={$usuarioActual['id']}
        ");
        $stmTotal->execute([":token_id" => $token]);
        $count = $stmTotal->fetch();

        //obtengo el tipo de despisitivo
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        $datos = analizarDispositivo($userAgent);




        if (intval($count["cantidad"]) == 0) {
            //tambien no te olvides de deshabilitar el activo para cuando ya no quiere recibir las notificaciones
            // Obtener datos del usuario de forma segura

            $stmt = $pdo->prepare("
           
                 INSERT INTO `firebase_app_tokens`( `rela_usuario`, `rela_equipo`, `token_equipo`, `fecha_alta`, `activo`, tipo_dispositivo, so_especifico, navegador, user_agent) 
                  VALUES (?,?,?,?,?,?,?,?,?)
             ");
            $stmt->execute([
                $usuarioActual["id"],
                $usuarioActual["id_equipo"],
                $token,
                date('Y-m-d'),
                1,

                $datos['tipo_dispositivo'],

                $datos['so_especifico'],
                $datos['navegador'],
                $userAgent
            ]);
        }

        return true;
    } catch (\Throwable $th) {
        throw $th;
    }
}
function detectarDispositivo($ua)
{
    $ua = strtolower($ua);

    // Detectar móvil
    if (strpos($ua, 'mobile') !== false) return 'Celular';
    if (strpos($ua, 'iphone') !== false) return 'Celular';
    if (strpos($ua, 'android') !== false && strpos($ua, 'mobile') !== false) return 'Celular';

    // Detectar tablet (ipad, tablets android sin "mobile" suelen ser tablets)
    if (strpos($ua, 'ipad') !== false) return 'Tablet';
    if (strpos($ua, 'android') !== false && strpos($ua, 'mobile') === false) return 'Tablet';

    // Detectar MacBook vs Mac de escritorio
    if (strpos($ua, 'macintosh') !== false) {
        if (strpos($ua, 'macbook') !== false) {
            return 'MacBook (Laptop)';
        }
        return 'Mac de escritorio';
    }

    // Detectar Windows
    if (strpos($ua, 'windows') !== false) {
        // Opcionalmente diferenciar Laptop o Desktop es difícil solo con UA, lo dejamos general
        return 'Windows PC';
    }

    // Detectar Linux
    if (strpos($ua, 'linux') !== false) {
        return 'Linux PC';
    }

    return 'Desconocido';
}
function analizarDispositivo($ua)
{
    $uaLower = strtolower($ua);

    // 1. Usar tu función para detectar dispositivo (Celular, Tablet, MacBook, Windows PC, Linux PC, etc)
    $tipoDispositivo = detectarDispositivo($uaLower);



    // 3. Detectar dispositivo móvil específico para asignar en es_pc_notebook_mac
    if (strpos($uaLower, 'android') !== false) {
        $esPC = 'Android';
    } elseif (strpos($uaLower, 'iphone') !== false) {
        $esPC = 'iPhone';
    } elseif (strpos($uaLower, 'ipad') !== false) {
        $esPC = 'iPad';
    } elseif (strpos($uaLower, 'ipod') !== false) {
        $esPC = 'iPod';
    } else {
        // Si no es móvil, chequeo PC o Mac
        if (in_array($tipoDispositivo, ['Windows PC', 'Linux PC', 'Mac de escritorio', 'MacBook (Laptop)'])) {
            $esPC = $tipoDispositivo;  // ej: 'Windows PC', 'MacBook (Laptop)'
        } else {
            $esPC = $tipoDispositivo;  // otros tipos detectados o 'Desconocido'
        }
    }

    // 4. Detectar sistema operativo específico para PC
    if ($tipoDispositivo === 'Windows PC') {
        $soEspecifico = 'Windows';
    } elseif ($tipoDispositivo === 'Linux PC') {
        $soEspecifico = 'Linux';
    } elseif ($tipoDispositivo === 'Mac de escritorio' || $tipoDispositivo === 'MacBook (Laptop)') {
        $soEspecifico = 'MacOS';
    } else {
        $soEspecifico = 'No aplica';
    }

    // 5. Detectar navegador básico
    if (strpos($uaLower, 'firefox') !== false) {
        $navegador = 'Firefox';
    } elseif (strpos($uaLower, 'edg') !== false) {  // Para Edge Chromium buscar "edg"
        $navegador = 'Microsoft Edge';
    } elseif (strpos($uaLower, 'opera') !== false || strpos($uaLower, 'opr') !== false) {
        $navegador = 'Opera';
    } elseif (strpos($uaLower, 'chrome') !== false) {
        $navegador = 'Chrome';
    } elseif (strpos($uaLower, 'safari') !== false && strpos($uaLower, 'chrome') === false) {
        $navegador = 'Safari';
    } else {
        $navegador = 'Desconocido';
    }



    return [
        'tipo_dispositivo' => $tipoDispositivo,
        'so_especifico' => $soEspecifico,
        'navegador' => $navegador,
    ];
}
