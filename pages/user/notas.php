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

    /* Contenedores con scroll */
    .scroll-container {
        max-height: 200px;
        overflow-y: auto;
    }

    .modal-colaborador {
        z-index: 2000;
        /* más alto que modalNota */
    }

    /* Menú contextual */
    #menu-eliminar,
    #menu-contextual {
        min-width: 150px;
        cursor: default;
    }
.tachado {
  text-decoration: line-through;
  color: gray;
  opacity: 0.8;
}
    @media (hover: none) and (pointer: coarse) {
        #menu-contextual {
            font-size: 1.2rem;
        }
    }

    /* Inputs transparentes */
    .input-fondo-transparente {
        background-color: transparent !important;
        outline: none;
        box-shadow: none !important;
        border: none;
        transition: color 0.3s ease;
    }

    .input-fondo-transparente:focus {
        background-color: transparent !important;
        box-shadow: none !important;
        outline: none;
        border: none;
    }

    /* Modo oscuro */
    body.dark .input-fondo-transparente {
        color: #fff !important;
    }

    /* Modo lista */
  .menu-contextual {
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

.tarea-texto.tachado {
  text-decoration: line-through;
  color: gray;
  opacity: 0.8;
}

    /* Limites de lista, texto y colaboradores */
    .limite-lista,
    .limite-texto {
        max-height: 200px;
        overflow: hidden;
        position: relative;
    }

    .limite-colaboradores {
        max-height: 60px;
        overflow: hidden;
        position: relative;
    }

    /* Textareas */
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

                        <div id="contenedorNotas" class="masonry-container">

                        </div>


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
                        data-tipo="texto">
                        Texto <i class="bi bi-textarea-t fs-3 ms-2"></i>
                    </button>

                    <!-- Botón Lista -->
                    <button type="button"
                        class="btn btn-primary show-light d-block text-end mb-2 fs-5 rounded cargar-contenido"
                        data-tipo="lista">
                        Lista <i class="bi bi-card-checklist fs-3 ms-2"></i>
                    </button>

                    <!-- Si usas modo oscuro, repite lo mismo para show-dark botones -->
                    <button type="button" class="btn btn-outline-primary d-none show-dark d-block text-end mb-2 fs-5 rounded cargar-contenido"
                        data-tipo="texto">
                        Texto <i class="bi bi-textarea-t fs-3 ms-2"></i>
                    </button>

                    <button type="button" class="btn btn-outline-primary d-none show-dark d-block text-end mb-2 fs-5 rounded cargar-contenido"
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
       


        <div class="modal fade" id="modalNota" tabindex="-1" aria-labelledby="modalNotaLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-fullscreen-xl-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="d-flex justify-content-start">
                           <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Cerrar">
    <i class="bi bi-arrow-left fs-3"></i>
</button>
                        </div>
                       <!--  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button> -->
                    </div>
                    <div class="modal-body">
                        <h5 class="modal-title" id="modalTitulo"></h5>
                        <p id="modalContenido" class="mb-3"></p>
                        <div id="modalListaTareas" class="mb-3"></div>
                        <div id="contcolaborador"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="modalcolaborador" tabindex="-1" aria-labelledby="modalTextoLabel">
            <div class="modal-dialog modal-lg modal-fullscreen-xl-down">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="modal-content">
                        <!-- Lista de colaboradores actuales -->
                        <div id="contenedor-select-colaborador"></div>
                        <div id="lista-colaboradores" class="list-group mb-3"></div>
                        <!-- Contenedor para el select dinámico -->
                    </div>
                </div>
            </div>
        </div>
<div class="modal fade" id="modalCrearNota" tabindex="-1">
    <div class="modal-dialog modal-lg modal-fullscreen-xl-down">
        <div class="modal-content">
            <div class="modal-header">
               <div class="d-flex justify-content-start">
                           <button type="button" class="btn" data-bs-dismiss="modal" aria-label="Cerrar">
    <i class="bi bi-arrow-left fs-3"></i>
</button>
                        </div>
                <!-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> -->
            </div>
            <div class="modal-body"></div>
            
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

<script src="./js/notas/notas.js"></script>


<?php include '../common/footer.php'; ?>