<?php
// Incluir configuración de sesión y validar usuario
require_once '../../../../backend/config/session_config.php';
include '../../../../backend/config/database.php';

// Verificar autenticación
if (!verificarUsuarioAutenticado()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? '';

    if ($tipo === 'texto') {
?>

        <div class="form-group mt-3">
            <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                id="nota"
                name="nota"
                placeholder="Escribir una nota"
                rows="1"
                style="overflow:hidden; resize:none;"></textarea>
        </div>

    <?php
    } elseif ($tipo === 'lista') {

    ?>
        <div>

            <div id="checkboxDinamicos"></div>

            <div class="input-group mb-2">
                <div class="input-group-text bg-transparent border-0">
                    <input type="checkbox" id="check_init" readonly class="form-check-input mt-0 tarea-checkbox">
                </div>
                <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                    id="inp_listar"
                    name="inp_listar"
                    placeholder="Nuevo Elemento"
                    rows="1"
                    style="overflow:hidden; resize:none;"></textarea>

            </div>
            <div class="text-center mb-3">
                <span type="button" id="btnAgregarItem" class="text-primary" style="cursor:pointer;">
                    <i class="bi bi-plus"></i> Agregar
                </span>
            </div>

        </div>
<?php } elseif ($tipo == "edit_nota") {
        // Obtener datos del usuario actual
        $usuarioActual = obtenerUsuarioActual();
        $idUsuario = $usuarioActual["id"];
        //echo $idUsuario;
        $sql = "
                            SELECT 
                                n.id_notas AS id_nota,
                                n.titulo AS titulo,
                                n.contenido AS nota,
                                n.fecha_creacion AS fecha_creacion,
                                n.id_usuario AS creador,
                                n.tipo_nota tiponota,
                                n.esta_pineada AS pineada,
                                e.id_empleado AS id_empleado,
                                CONCAT(
                                    UPPER(LEFT(e.nombre, 1)),
                                    UPPER(LEFT(e.apellido, 1))
                                ) AS iniciales,
                                e.nombre AS nombre,
                                e.apellido AS apellido,
                                nl.id_nota_lista id_lista_tarea,
                                nl.lista AS tarea,
                                nl.chequeado AS list_check,
                                nl.estados estadosnotas,
                                c.estado estadoColaborador
                            FROM notas n
                            LEFT JOIN collaboradores c ON c.id_nota = n.id_notas
                            LEFT JOIN empleados e ON e.id_empleado = c.id_usuario
                            LEFT JOIN nota_lista nl ON nl.rela_notas = n.id_notas
                                 and nl.estados=1
                            WHERE n.estado=1 and n.id_notas=:idNota  
                            AND (n.id_usuario = :idUsuario1 OR n.id_notas IN (SELECT id_nota FROM collaboradores WHERE id_usuario = :idUsuario2   and collaboradores.estado=1))
                            ORDER BY n.esta_pineada DESC, n.id_notas DESC, nl.id_nota_lista ASC;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'idUsuario1' => $idUsuario,
            'idUsuario2' => $idUsuario,
            "idNota" => $_POST["id_nota"]
        ]);

        $result = [];


        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $row['id_nota'];

            if (!isset($result[$id])) {
                $result[$id] = [
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

            if (!empty($row['id_empleado'])) {
                $existe = false;
                foreach ($result[$id]['colaboradores'] as $col) {
                    if ($col['id_empleado'] == $row['id_empleado']) {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $result[$id]['colaboradores'][] = [
                        'id_empleado' => $row['id_empleado'],
                        'nombre' => $row['nombre'],
                        'apellido' => $row['apellido'],
                        'iniciales' => $row['iniciales'],
                        'estadoColaborador' => $row['estadoColaborador']
                    ];
                }
            }

            // Evitar tareas duplicadas
            if (!empty($row['id_lista_tarea'])) {
                $existe = false;
                foreach ($result[$id]['lista_tareas'] as $tarea) {
                    if ($tarea['id_lista_tarea'] == $row['id_lista_tarea']) {
                        $existe = true;
                        break;
                    }
                }
                if (!$existe) {
                    $result[$id]['lista_tareas'][] = [
                        'id_lista_tarea' => $row['id_lista_tarea'],
                        'tarea' => $row['tarea'],
                        'list_check' => $row['list_check'],
                        'estadosnotas' => $row['estadosnotas']
                    ];
                }
            }
        }


        echo json_encode(array(
            "data" => $result,
            "status" => true
        ));
    }
} ?>