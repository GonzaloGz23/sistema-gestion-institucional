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
                // Recibir tareas como JSON
                $tareasJson = $_POST['tareas'] ?? '[]';
                $tareas = json_decode($tareasJson, true);

                if (!empty($tareas) && is_array($tareas)) {
                    // Preparar inserción según tu tabla
                    $stmtTarea = $pdo->prepare(
                        "INSERT INTO `nota_lista` (`lista`, `chequeado`, `rela_notas`, `estados`, `orden`) 
             VALUES (?, ?, ?, 1, ?)"
                    );

                    foreach ($tareas as $t) {
                        $descripcion = trim($t['texto'] ?? '');
                        $chequeado = intval($t['check'] ?? 0);
                        $orden = intval($t['orden'] ?? 1); // Si el front no envía orden, usar 1

                        if ($descripcion !== '') {
                            $stmtTarea->execute([$descripcion, $chequeado, $id_Notas, $orden]);
                            $idTarea = $pdo->lastInsertId();

                            $listaTareas[] = [
                                'id_lista_tarea' => $idTarea,
                                'tarea' => $descripcion,
                                'list_check' => $chequeado,
                                'estadosnotas' => 1,
                                'orden' => $orden
                            ];
                        }
                    }
                }
            }


            // 3. Insertar colaboradores

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
