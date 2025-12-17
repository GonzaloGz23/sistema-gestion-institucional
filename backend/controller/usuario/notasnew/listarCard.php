 <?php
 require_once "../../../config/session_config.php";
 require_once "../../../config/database.php";
 if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener datos del usuario actual
$usuarioActual = obtenerUsuarioActual();
    $idUsuario = $usuarioActual['id'];
    $sql = "
            SELECT 
        n.id_notas AS id_nota,
        n.titulo AS titulo,
        n.contenido AS nota,
        n.fecha_creacion AS fecha_creacion,
        n.id_usuario AS creador,
        n.tipo_nota AS tiponota,
        n.esta_pineada AS pineada,
        e.id_empleado AS id_empleado,
        CONCAT(UPPER(LEFT(e.nombre, 1)), UPPER(LEFT(e.apellido, 1))) AS iniciales,
        e.nombre AS nombre,
        e.apellido AS apellido,
        nl.id_nota_lista AS id_lista_tarea,
        nl.lista AS tarea,
        nl.chequeado AS list_check,
        nl.estados AS estadosnotas,
        c.estado AS estadoColaborador,
        ROW_NUMBER() OVER (PARTITION BY n.id_notas ORDER BY nl.id_nota_lista ASC) AS orden_tarea
    FROM notas n
    LEFT JOIN collaboradores c 
        ON c.id_nota = n.id_notas
        AND c.estado = 1              -- ✅ Filtra solo colaboradores activos
    LEFT JOIN empleados e 
        ON e.id_empleado = c.id_usuario
    LEFT JOIN nota_lista nl 
        ON nl.rela_notas = n.id_notas
        AND nl.estados = 1  
    WHERE n.estado = 1
    AND (
        n.id_usuario = :idUsuario1
        OR n.id_notas IN (
            SELECT id_nota 
            FROM collaboradores 
            WHERE id_usuario = :idUsuario2
            AND estado = 1
        )
    )
    ORDER BY n.esta_pineada DESC, n.id_notas DESC, nl.orden ASC;

                        ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'idUsuario1' => $idUsuario,
        'idUsuario2' => $idUsuario
    ]);

    $result = [];

    // Armamos un array agrupando por nota
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $idNota = $row['id_nota'];

        // Si la nota no existe en el array, la inicializamos
        if (!isset($result[$idNota])) {
            $result[$idNota] = [
                'id_nota' => $row['id_nota'],
                'titulo' => $row['titulo'],
                'nota' => $row['nota'],
                'fecha_creacion' => $row['fecha_creacion'],
                'creador' => $row['creador'],
                'pineada' => $row['pineada'],
                'tiponota' => $row['tiponota'],
                'colaboradores' => [],
                'lista_tareas' => []
            ];
        }

        // Colaboradores
        if (!empty($row['id_empleado'])) {
            $result[$idNota]['colaboradores'][$row['id_empleado']] = [
                'id_empleado' => $row['id_empleado'],
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'iniciales' => $row['iniciales'],
                'estadoColaborador' => $row['estadoColaborador']
            ];
        }

        // Tareas
        if (!empty($row['id_lista_tarea'])) {
            $result[$idNota]['lista_tareas'][$row['id_lista_tarea']] = [
                'id_lista_tarea' => $row['id_lista_tarea'],
                'tarea' => $row['tarea'],
                'list_check' => $row['list_check'],
                'estadosnotas' => $row['estadosnotas'],
                'orden_tarea' => $row['orden_tarea']
            ];
        }
    }

    // Convertimos los arrays asociativos internos en arrays indexados
    foreach ($result as &$nota) {
        $nota['colaboradores'] = array_values($nota['colaboradores']);
        $nota['lista_tareas'] = array_values($nota['lista_tareas']);
    }

    // Devolver JSON
    header('Content-Type: application/json');
    echo json_encode(array_values($result));

    ?>