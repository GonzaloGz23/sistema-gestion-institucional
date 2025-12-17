<?php
// Incluir configuración de sesión y validar usuario
require_once "../../../config/session_config.php";
require_once "../../../config/database.php";
require_once '../../../../pages/common/functions.php';
// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!empty($_POST['titulo']) || !empty($_POST['texto']) || (isset($_POST['tareas']) && is_array($_POST['tareas']) && count($_POST['tareas']) > 0)) {
            # code...

            date_default_timezone_set('America/Argentina/Buenos_Aires');

            // Obtener datos del usuario de forma segura
            $usuarioActual = obtenerUsuarioActual();
            $id_empleado = $usuarioActual['id'];

            $titulo = (!empty($_POST['titulo'])) ? $_POST['titulo'] : 'sin titulo';

            $contenido = $_POST['texto'] ?? '';
            $fecha_creacion = date('Y-m-d H:i:s');
            $tipo_nota = $_POST['tipo'] ?? '';
            $pineado = $_POST['pineado'];
            $listaTareas = [];
            $arr_colaboradores = [];

            if ($tipo_nota == 'texto') {
                $tip_notas = 'nota';
            } else {
                $contenido = '';
                $tip_notas = 'lista';
            }


            $stmt = $pdo->prepare("INSERT INTO `notas` (`titulo`, `contenido`, `id_usuario`, `fecha_creacion`, `tipo_nota`, `esta_pineada`) VALUES (?,?,?, ?, ?, ?)");
            $stmt->execute([$titulo, $contenido, $id_empleado, $fecha_creacion, $tip_notas, $pineado]);

            $id_Notas = $pdo->lastInsertId();
            if ($tipo_nota == 'lista') {
                // 2. Insertar tareas
                $tareasJson = $_POST['tareas'] ?? '[]';
                $tareas = json_decode($tareasJson, true);

                if (!empty($tareas) && is_array($tareas)) {
                    $stmtTarea = $pdo->prepare("INSERT INTO `nota_lista` (`lista`, `chequeado`, `rela_notas`) VALUES (?, ?, ?)");

                    foreach ($tareas as $t) {
                        $descripcion = trim($t['texto'] ?? '');
                        $chequeado = intval($t['check'] ?? 0);

                        if ($descripcion !== '') {
                            $stmtTarea->execute([$descripcion, $chequeado, $id_Notas]);
                            $idTarea = $pdo->lastInsertId();

                            $listaTareas[] = [
                                'id_lista_tarea' => $idTarea,
                                'tarea' => $descripcion,
                                'list_check' => $chequeado,
                                'estadosnotas' => 1
                            ];
                        }
                    }
                }
            }


            // 3. Insertar colaboradores
            $colaboradores = $_POST['colaboradores'] ?? [];

            if (!empty($colaboradores)) {
                $stmtColab = $pdo->prepare("INSERT INTO `collaboradores` (`id_usuario`, `id_nota`) VALUES (?, ?)");
                foreach ($colaboradores as $colabId) {
                    $stmtColab->execute([$colabId, $id_Notas]);
                    $idcolaborador = $pdo->lastInsertId();



                    $consColaborador = $pdo->prepare("
            SELECT 
                CONCAT(
                    UPPER(LEFT(e.nombre, 1)),
                    UPPER(LEFT(e.apellido, 1))
                ) AS iniciales,
                e.nombre,
                e.apellido,
                c.estado
            FROM `collaboradores` c
            LEFT JOIN empleados e ON e.id_empleado = c.id_usuario
            WHERE c.id_collaborador=?
        ");
                    $consColaborador->execute([$idcolaborador]);
                    $colabInfo = $consColaborador->fetch(PDO::FETCH_ASSOC);

                    $arr_colaboradores[] = [
                        'id_colaborador' => $idcolaborador,
                        'estado' => $colabInfo['estado'],
                        'iniciales' => $colabInfo['iniciales'] ?? '',
                        'nombre'    => $colabInfo['nombre'] ?? '',
                        'apellido'  => $colabInfo['apellido'] ?? ''
                    ];
                }
            }

             //enviar notificacion
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
            where n.id_notas=$id_Notas
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

             echo json_encode([
                 'success' => true,
                 'nota' => [
                     'id_nota' => $id_Notas,
                     'pineada' => $pineado,
                     'titulo' => $titulo,
                     'nota' => $contenido,
                     'fecha_creacion' => $fecha_creacion,
                     'lista_tareas' => $listaTareas,
                     'colaboradores' => $arr_colaboradores
                 ]
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
