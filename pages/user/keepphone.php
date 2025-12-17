<!-- require_once '../../backend/config/session.php'; // Verificar acceso del admin -->
<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>
<style>
    /* Bootstrap 5.3: modo claro / modo oscuro */
    :root[data-bs-theme="light"] .show-dark {
        display: none !important;
    }

    :root[data-bs-theme="dark"] .show-dark {
        display: inline-block !important;
    }

    :root[data-bs-theme="dark"] .show-light {
        display: none !important;
    }

    :root[data-bs-theme="light"] .show-light {
        display: inline-block !important;
    }

    .scroll-container {
        max-height: 200px;
        overflow-y: auto;
    }

    #menu-eliminar {
        min-width: 150px;
        cursor: default;
    }

    .input-fondo-transparente {
        background-color: transparent !important;
        /* fuerza fondo transparente */

        /* texto negro en modo claro */
        outline: none;
        box-shadow: none !important;
        border: none;
        transition: color 0.3s ease;
    }

    /* Evitar fondo o sombra en foco */
    .input-fondo-transparente:focus {
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none;
        border: none;
    }

    /* Modo oscuro: texto blanco */
    body.dark .input-fondo-transparente {
        color: #fff !important;
    }

    /*  .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        grid-auto-rows: 10px;
        gap: 20px;
    }

    .grid-item {
        background: #eee;
        padding: 1rem;
        border-radius: 8px;
        grid-row-end: span 30;
        /* Ajusta según el contenido */
    */ #menu-contextual {
        min-width: 120px;
    }

    @media (hover: none) and (pointer: coarse) {
        #menu-contextual {
            font-size: 1.2rem;
        }
    }

    .modo-lista {
        column-count: 1 !important;
    }


    .masonry-container {
        column-count: 3;
        column-gap: 1rem;
        max-height: 100%;
        /* sin límite general del contenedor */
    }

    @media (max-width: 600px) {
        .masonry-container {
            column-count: 1;
        }
    }

    .masonry-item {
        break-inside: avoid;
        margin-bottom: 1rem;
        display: inline-block;
        width: 100%;
    }

    .limite-lista,
    .limite-texto {
        max-height: 200px;
        /* ajustá según tu diseño */
        overflow: hidden;
        position: relative;
    }

    .limite-colaboradores {
        max-height: 60px;
        /* limita la altura de los avatares */
        overflow: hidden;
        position: relative;
    }

    textarea.form-control {
        min-height: unset !important;
        max-height: unset !important;
        overflow: hidden !important;
        resize: none !important;
    }
</style>
<div class="db-content">

    <div class="container mb-4">

        <div class="row gy-4 mb-4">
            <div class="row gy-4 ">
                <div class="d-flex w-full justify-content-center ">
                    <div class="shadow-lg p-3  bg-white rounded rounded-pill">
                        <div class="row ">
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <input type="text" class="form-control border-0 flex-grow-1 input-fondo-transparente" id="buscador" name="buscador" placeholder="Buscar tus notas">
                                    <span id="listar" class="input-group-text border-0 bg-white input-check"><i class="bi bi-view-stacked fs-3 changeModIcon"></i></span>
                                </div>
                            </div>


                        </div>


                    </div>

                </div>
            </div>
        </div>
        <div class="row gy-4">
            <div class="row ">
                <div class="d-flex w-full justify-content-center gy-4">
                    <div class="col-12">
                        <?php
                        $idUsuario = $usuarioActual->id;
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
                            WHERE n.estado=1 
                            AND (
                                    n.id_usuario = :idUsuario1
                                OR n.id_notas IN (
                                        SELECT id_nota FROM collaboradores WHERE id_usuario = :idUsuario2   and collaboradores.estado=1
                                    )
                            )
                            ORDER BY n.esta_pineada DESC, n.id_notas DESC, nl.id_nota_lista ASC

                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            'idUsuario1' => $idUsuario,
                            'idUsuario2' => $idUsuario
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


                        if (!empty($result)) {
                        ?>
                            <div id="contenedorNotas" class="masonry-container">
                                <?php foreach ($result as $t): ?>
                                    <?php
                                    $pinActivo = ($t['pineada'] == 1);
                                    $iconoPin = $pinActivo ? 'bi-pin-angle-fill' : 'bi-pin-angle';
                                    ?>
                                    <div class="masonry-item este-es" data-id-nota="<?= $t['id_nota'] ?>">
                                        <input type="hidden" data-id-nota-input="<?= $t['id_nota'] ?>">
                                        <div class="card mb-2 border-top border-4 border-0">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="mb-0 text-secondary fs-6"><small><?= $t['fecha_creacion'] ?></small></span>
                                                    <button type="button"
                                                        class="btn btn-link p-0 ms-3 btnCambiarPin text-secondary"
                                                        title="Pin"
                                                        aria-pressed="<?= $pinActivo ? 'true' : 'false' ?>"
                                                        data-id-nota="<?= $t['id_nota'] ?>"
                                                        data-pineada="<?= $t['pineada'] ?>">
                                                        <i class="bi <?= $iconoPin ?> fs-3 icon-pin"></i>
                                                    </button>
                                                </div>


                                                <p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto h4" data-id-titulo="<?= $t['id_nota'] ?> title=" <?= htmlspecialchars($t['titulo']) ?>""><?= htmlspecialchars($t['titulo']) ?></p>

                                                <?php if (!empty($t['lista_tareas'])): ?>
                                                    <div class="limite-lista">
                                                        <?php foreach ($t['lista_tareas'] as $op): ?>
                                                            <?php if ($op['estadosnotas'] != 0): ?>
                                                                <?php
                                                                $checked = $op['list_check'] ? 'checked' : '';
                                                                $tachado = $op['list_check'] ? 'text-decoration-line-through' : '';
                                                                ?>
                                                                <div class="d-flex align-items-center mb-2 tarea-item" data-id="<?= $op['id_lista_tarea'] ?>">
                                                                    <input type="checkbox" class="form-check-input me-2" <?= $checked ?> disabled>
                                                                    <p class="mb-0 flex-grow-1 input-fondo-transparente tarea-texto <?= $tachado ?>"><?= htmlspecialchars($op['tarea']) ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="limite-texto">
                                                        <p class="form-control border-0 px-2 input-fondo-transparente mb-0"><?= htmlspecialchars($t['nota']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                <?php
                                                $colaboradoresVisibles = array_filter($t['colaboradores'], function ($col) {
                                                    return $col['estadoColaborador'] != 0;
                                                });
                                                ?>

                                                <div class="avatar-group lista-colaboradores-nota limite-colaboradores my-2" data-id-nota="<?= $t['id_nota'] ?>">
                                                    <?php if (!empty($colaboradoresVisibles)): ?>
                                                        <?php foreach ($colaboradoresVisibles as $op): ?>
                                                            <span class="avatar avatar-sm avatar-primary-soft"
                                                                data-id-usuario="<?= $op['id_empleado'] ?>"
                                                                title="<?= $op['nombre'] . ' ' . $op['apellido'] ?>">
                                                                <span class="avatar-initials rounded-circle"><?= htmlspecialchars($op['iniciales']) ?></span>
                                                            </span>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="text-muted small my-2">
                                                            <i class="bi bi-person"></i> Sin colaboradores asignados
                                                        </div>
                                                    <?php endif; ?>
                                                </div>



                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>


                        <?php


                        } else {
                            echo '<div class="text-center text-muted">No hay notas para mostrar.</div>';
                        }

                        ?>
                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="modal-colaborador" tabindex="-1" aria-labelledby="modalTextoLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalCenterTitle">colaboradores</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                        </button>
                    </div>
                    <div class="modal-body">

                    </div>
                </div>
            </div>
        </div>

        <button
            class="btn btn-primary rounded position-fixed end-0 me-4 shadow  fs-3"
            style="bottom: 55px; z-index: 1050;"
            data-bs-toggle="modal"
            data-bs-target="#modalFlotante">
            <i class="bi bi-plus-lg"></i>
        </button>
        <!-- Modal flotante -->
        <!-- Modal flotante (modificado) -->
        <div class="modal fade" id="modalFlotante" tabindex="-1" aria-labelledby="labelMenuFlotante">
            <div class="modal-dialog modal-sm"
                style="position: fixed; bottom: 110px; right: 28px; margin: 0; z-index: 1060;">
                <div class="modal-content bg-transparent border-0">

                    <!-- Botón Texto -->
                    <button type="button"
                        class="btn btn-primary show-light d-block text-end mb-2 fs-5 rounded cargar-contenido"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTexto"
                        data-tipo="texto">
                        Texto <i class="bi bi-textarea-t fs-3 ms-2"></i>
                    </button>

                    <!-- Botón Lista -->
                    <button type="button"
                        class="btn btn-primary show-light d-block text-end mb-2 fs-5 rounded cargar-contenido"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTexto"
                        data-tipo="lista">
                        Lista <i class="bi bi-card-checklist fs-3 ms-2"></i>
                    </button>

                    <!-- Si usas modo oscuro, repite lo mismo para show-dark botones -->
                    <button type="button" class="btn btn-outline-primary d-none show-dark d-block text-end mb-2 fs-5 rounded"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTexto"
                        data-tipo="texto">
                        Texto <i class="bi bi-textarea-t fs-3 ms-2"></i>
                    </button>

                    <button type="button" class="btn btn-outline-primary d-none show-dark d-block text-end mb-2 fs-5 rounded"
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTexto"
                        data-tipo="lista">
                        Lista <i class="bi bi-card-checklist fs-3 ms-2"></i>
                    </button>

                </div>
            </div>
        </div>

        <!-- Modal para Texto -->
        <!-- Modal para Texto -->
        <div class="modal fade modalEditarNota" id="modalEditarNota" tabindex="-1" aria-labelledby="modalEditarNotaLabel">
            <div class="modal-dialog modal-lg modal-fullscreen-xl-down" style="height: fit-content!important;">
                <div class="modal-content">
                    <form id="form-nota">



                        <div class="modal-body">
                            <!-- Aquí se carga dinámicamente el contenido editable -->
                            <div class=" d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-secondary border-0" data-bs-dismiss="modal" aria-label="Cerrar"><i class="bi bi-arrow-left fs-3"></i></button>
                                <div>

                                    <i id="pinToggle" class="bi bi-pin fs-3  " style="cursor:pointer"></i>

                                </div>
                            </div>
                            <div id="contenedor_text" class="contenedor_text">
                                <div class="row ">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center">

                                            <textarea class="form-control border-0 flex-grow-1 input-fondo-transparente p-2"
                                                id="titulo_edit"
                                                name="titulo_edit"
                                                placeholder="Titulo"
                                                rows="1"
                                                style="overflow: hidden; resize: none;"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12 tipo-contenido-edit" id="tipo-contenido-edit">

                                    </div>


                                </div>


                            </div>
                            <div class=" d-flex justify-content-between align-items-center">
                                <div id="conteiner-colaboradores">

                                </div>

                                <span class="ms-2">
                                    <i id="abrirModalBtn" class="bi bi-person-add col-edit-change fs-3" style="cursor: pointer;"></i>
                                </span>
                            </div>
                            <div class="id_buttonm">

                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
        <div class="modal fade" id="modalcolaborador" tabindex="-1" aria-labelledby="modalTextoLabel">
            <div class="modal-dialog modal-lg modal-fullscreen-xl-down"> <!-- Aquí agregas modal-fullscreen -->
                <div class="modal-content">

                    <div class="modal-header">

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                        </button>
                    </div>
                    <div class="modal-body" id="modal-content">


                    </div>
                </div>
            </div>
        </div>





    </div>
</div> <!-- cierre db-content -->
<script src="<?= BASE_URL ?>assets/libs/jquery/jquery-3.7.0.min.js"></script>
<link href="<?= BASE_URL ?>assets/libs/select2/select2.min.css" rel="stylesheet" />
<link href="<?= BASE_URL ?>assets/libs/select2/select2-custom.min.css" rel="stylesheet" />
<script src="<?= BASE_URL ?>assets/libs/select2/select2.min.js"></script>
<?php include '../common/scripts.php'; ?>
<script src="<?= ROOT_PATH ?>pages/user/js/autosize-textarea.js"></script>

<script src="./js/notas/keepphone.js"></script>


<?php include '../common/footer.php'; ?>