<?php include '../common/header.php'; ?>
<?php include '../common/navbar.php'; ?>
<?php include '../common/sidebar.php'; ?>

<main class="db-content" data-entity="<?php echo $usuarioActual->id_entidad; ?>" data-user="<?php echo $usuarioActual->id; ?>">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Gestión de capacitaciones</h2>
        </div>

        <div id="spinnerCarga" class="text-center my-3 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>

        <p id="mensajeNoCapacitaciones" class="text-center text-muted d-none">No hay capacitaciones cargadas en el sistema.</p>

        <div id="contenedorTabla" class="d-none">
            <div class="table-responsive">
                <table class="table table-hover" id="tablaCapacitaciones">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Equipo</th>
                            <th>Inicio de Cursada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCapacitacionesBody">
                        <!-- Las capacitaciones se insertarán aquí dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal único para gestionar Cursos -->
    <div class="modal fade" id="modalCapacitacion" tabindex="-1" role="dialog" aria-labelledby="modalCapacitacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light text-white">
                    <h5 class="modal-title" id="modalCapacitacionLabel">
                        <i class="bi bi-graduation-cap"></i> <span id="capacitacionNombreHeader">Detalles de la Capacitación</span>
                        <small class="ms-2">#<span id="capacitacionId"></span></small>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Navegación por pestañas -->
                    <ul class="nav nav-tabs nav-justified" id="capacitacionTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="categorizacion-tab" data-bs-toggle="tab" data-bs-target="#categorizacion" type="button" role="tab">
                                <i class="bi bi-tags"></i> <span class="d-none d-md-inline">Categorización</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="basicos-tab" data-bs-toggle="tab" data-bs-target="#basicos" type="button" role="tab">
                                <i class="bi bi-info-circle"></i> <span class="d-none d-md-inline">Datos Básicos</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="fechas-tab" data-bs-toggle="tab" data-bs-target="#fechas" type="button" role="tab">
                                <i class="bi bi-calendar-event"></i> <span class="d-none d-md-inline">Fechas</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contenido-tab" data-bs-toggle="tab" data-bs-target="#contenido" type="button" role="tab">
                                <i class="bi bi-book"></i> <span class="d-none d-md-inline">Contenido</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="gestion-tab" data-bs-toggle="tab" data-bs-target="#gestion" type="button" role="tab">
                                <i class="bi bi-gear"></i> <span class="d-none d-md-inline">Gestión</span>
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="capacitacionTabContent">
                        <!-- Pestaña Categorización -->
                        <div class="tab-pane fade show active" id="categorizacion" role="tabpanel">
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="lugar" class="form-label fw-bold">Título</label>
                                        <input type="text" name="categoria_titulo" class="form-control" id="titulo" placeholder="Solo si es modalidad presencial o mixto">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="alcance" class="form-label fw-bold">1. Alcance</label>
                                        <select name="categoria_alcance" class="form-select" id="alcance">
                                            <option value="interno">Interno</option>
                                            <option value="estatal">Estatal</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tipoCapacitacion" class="form-label fw-bold">2. Tipo de Capacitación</label>
                                        <select name="categoria_tipoCapacitacion" class="form-select" id="tipoCapacitacion">
                                            <option value="curso">Curso</option>
                                            <option value="taller">Taller</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="categoriaGeneral" class="form-label fw-bold">3. Categoría General *</label>
                                        <select name="categoria_categoriaGeneral" class="form-select" id="categoriaGeneral">
                                            <option value="">Cargando categorías...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="categoriaEspecifica" class="form-label fw-bold">4. Categoría Específica (opcional)</label>
                                        <select name="categoria_especifica" class="form-select" id="categoriaEspecifica" disabled>
                                            <option value="">Seleccione una categoría general primero</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="subcategoria" class="form-label fw-bold">5. Subcategoría (opcional)</label>
                                        <select name="categoria_subcategoria" class="form-select" id="subcategoria" disabled>
                                            <option value="">Seleccione una categoría específica primero</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="modalidad" class="form-label fw-bold">6. Modalidad</label>
                                        <select name="categoria_modalidad" class="form-select" id="modalidad">
                                            <option value="presencial">Presencial</option>
                                            <option value="virtual">Virtual</option>
                                            <option value="mixto">Mixta</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lugar" class="form-label fw-bold">7. Lugar</label>
                                        <input type="text" name="categoria_lugar" class="form-control" id="lugar" placeholder="Solo si es modalidad presencial o mixto">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pestaña Datos Básicos -->
                        <div class="tab-pane fade" id="basicos" role="tabpanel">
                            <div class="p-4">
                                <div class="row g-3">
                                   <input type="hidden" class="form-control" id="id_equipoActual" name="id_equipoActual" value="<?php  echo $usuarioActual->id_equipo; ?>">

                                    <div class="col-12">
                                        <label for="nombreCapacitacion" class="form-label fw-bold">1. Nombre de la Capacitación</label>
                                        <input type="text" class="form-control" id="nombreCapacitacion">
                                    </div>
                                    <div class="col-12">
                                        <label for="slogan" class="form-label fw-bold">2. Slogan</label>
                                        <input type="text" class="form-control" id="slogan">
                                    </div>
                                    <div class="col-12">
                                        <label for="objetivo" class="form-label fw-bold">3. Objetivo</label>
                                        <textarea class="form-control" id="objetivo" rows="3"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label for="descripcion" class="form-label fw-bold">4. En este taller aprenderás</label>
                                        <textarea class="form-control" id="descripcion" rows="4"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="destinatarios" class="form-label fw-bold">5. Destinatarios</label>
                                        <textarea class="form-control" id="destinatarios" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="requisitos" class="form-label fw-bold">6. Requisitos</label>
                                        <textarea class="form-control" id="requisitos" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pestaña Fechas y Ubicación -->
                        <div class="tab-pane fade" id="fechas" role="tabpanel">
                            <div class="p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="fechaInscripcion" class="form-label fw-bold">1. Inicio de Inscripción</label>
                                        <input type="date" class="form-control" id="fechaInscripcion">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fechaInicio" class="form-label fw-bold">2. Inicio de Cursada</label>
                                        <input type="date" class="form-control" id="fechaInicio">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="fechaFin" class="form-label fw-bold">3. Finalización de Cursada</label>
                                        <input type="date" class="form-control" id="fechaFin">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="duracionClase" class="form-label fw-bold">4. Duración de Clase (minutos)</label>
                                        <input type="number" class="form-control" id="duracionClase">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cantidadEncuentros" class="form-label fw-bold">5. Cantidad de Encuentros (total)</label>
                                        <input type="number" class="form-control" id="cantidadEncuentros">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="cupos" class="form-label fw-bold">6. Cupos</label>
                                        <input type="number" class="form-control" id="cupos">
                                    </div>
                                    
                                    <div class="col-12">
                                        <h6 class="fw-bold mt-3 mb-3">Días y Horarios de Cursada</h6>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="text-muted pe-1">Configurar los días y horarios de la cursada</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarHorario()">
                                                <i class="bi bi-plus"></i> <!-- Agregar -->
                                            </button>
                                        </div>
                                        <div id="horariosContainer">
                                            <!-- Los horarios se insertarán aquí dinámicamente -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pestaña Contenido -->
                        <div class="tab-pane fade" id="contenido" role="tabpanel">
                            <div class="p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0 text-primary">Contenido Programático</h5>
                                    <button class="btn btn-outline-primary" onclick="agregarTema()">
                                        <i class="bi bi-plus"></i> <!-- Agregar -->
                                    </button>
                                </div>
                                
                                <div class="alert alert-info mb-4">
                                    <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Los temas son obligatorios, los subtemas son opcionales y dependen de cada tema.
                                </div>
                                
                                <div id="temasContainer">
                                    <!-- Los temas se insertarán aquí dinámicamente -->
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pestaña Gestión -->
                        <div class="tab-pane fade" id="gestion" role="tabpanel">
                            <div class="p-4">
                                <div class="row g-4">
                                    <!-- Imagen del Curso -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0 fw-bold">🖼️ Imagen del Curso</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <img id="imagenCurso" src="../../images/default-course.webp" 
                                                         class="img-fluid rounded shadow" alt="Imagen del curso" style="max-height: 200px;"
                                                         onerror="this.src='../../images/default-course.webp';">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Cargar nueva imagen:</label>
                                                    <div class="input-group">
                                                        <input type="file" class="form-control" id="nuevaImagen" accept="image/*">
                                                        <button class="btn btn-secondary" type="button" id="btnSubirImagen" 
                                                                onclick="subirImagenAhora()" 
                                                                title="Seleccione una imagen primero"
                                                                data-bs-toggle="tooltip">
                                                            <i class="bi bi-save"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-muted">Formatos aceptados: JPG, PNG, GIF. Tamaño máximo: 2MB</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Información del Equipo -->
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="mb-0 fw-bold">👥 Equipo Creador</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Equipo:</label>
                                                    <input type="text" class="form-control" id="equipoCreador" readonly>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Link de Inscripción:</label>
                                                    <div class="input-group">
                                                        <input type="url" class="form-control" id="linkInscripcion" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="copiarLink()">
                                                            <i class="bi bi-clipboard"></i>
                                                        </button>
                                                    </div>
                                                    <small class="text-muted">Este link se genera automáticamente</small>
                                                </div>
                                                <div class="mb-3">
                                                    <button class="btn btn-primary w-100" onclick="abrirLink()">
                                                        <i class="bi bi-box-arrow-up-right"></i> Abrir Link de Inscripción
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Estado y Acciones -->
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="mb-0 fw-bold">⚙️ Estado y Acciones</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Estado Actual:</label>
                                                            <div>
                                                                <span id="estadoBadge" class="badge bg-warning fs-6">En Espera</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Cambiar Estado:</label>
                                                            <div class="d-grid gap-2">
                                                                <button id="btnEnRevision" class="btn btn-warning btn-sm" onclick="cambiarEstadoManual('en_revision')">
                                                                    <i class="bi bi-pencil"></i> Pasar a Revisión
                                                                </button>
                                                                <button id="btnAprobado" class="btn btn-success btn-sm" onclick="cambiarEstadoManual('aprobado')">
                                                                    <i class="bi bi-check-circle"></i> Aprobar
                                                                </button>
                                                                <button id="btnEnEspera" class="btn btn-secondary btn-sm" onclick="cambiarEstadoManual('en_espera')">
                                                                    <i class="bi bi-clock"></i> Volver a Espera
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Publicación:</label>
                                                            <div class="d-grid gap-2">
                                                                <button class="btn btn-success btn-sm" onclick="cambiarEstadoPublicacion()" id="btnPublicar">
                                                                    <i class="bi bi-eye"></i> <span id="btnPublicarTexto">Publicar</span>
                                                                </button>
                                                                <small class="text-muted">Controla la visibilidad pública de la capacitación</small>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Texto Plano:</label>
                                                            <div class="d-grid gap-2">
                                                                <button class="btn btn-info btn-sm" onclick="mostrarTextoPlano()" id="btnTextoPlano" disabled>
                                                                    <i class="bi bi-clipboard-data"></i> Ver Texto Plano
                                                                </button>
                                                                <small class="text-muted">Disponible solo para capacitaciones aprobadas</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" id="btnGuardarCambios" class="btn btn-primary" onclick="guardarCambios(event)">
                            Guardar
                        </button>
                    </div>
                
            </div>
        </div>
    </div>

</main>

 <?php if(!in_array($usuarioActual->id_equipo,[11,10])){  ?>
<button id="btnNuevaSolicitud" onclick="verModalCreacion()"  class="btn btn-primary rounded-3 position-fixed shadow" style="bottom: 1rem; right: 16px; z-index: 99; width: 50px; height: 50px;" data-bs-toggle="modal" data-bs-target="#modalNuevaSolicitud">
        <i class="bi bi-plus-lg"></i>
    </button>
<?php } ?>
<!-- Estilos CSS para modo responsivo -->
<style>
/* Estilos para el modo acordeón/móvil */
.accordion-mode .section-header {
    border-radius: 8px 8px 0 0;
    /*border: 1px solid #dee2e6;*/
    border-bottom: none;
}

.accordion-mode .section-content {
    /*border: 1px solid #dee2e6;*/
    border-top: none;
    border-radius: 0 0 8px 8px;
    margin-bottom: 1rem;
}

.accordion-mode .section-content .p-4 {
    border-radius: 0 0 8px 8px;
    background-color: var(--bs-body-bg);
}

.section-separator {
    height: 20px;
    background: transparent;
}

/* Mejorar spacing en móvil */
@media (max-width: 991px) {
    .accordion-mode .section-content {
        margin-bottom: 2rem;
    }
    
    .accordion-mode .section-header h5 {
        font-size: 1.1rem;
    }
    
    /* Ajustar padding del modal en móvil */
    .modal-fullscreen .modal-body {
        padding: 0.5rem;
    }
    
    .accordion-mode .section-content .p-4 {
        padding: 1.5rem !important;
    }
}

/* Tema oscuro */
[data-theme="dark"] .accordion-mode .section-header {
    background-color: var(--bs-primary) !important;
    border-color: var(--bs-border-color);
}

[data-theme="dark"] .accordion-mode .section-content {
    border-color: var(--bs-border-color);
    background-color: var(--bs-body-bg);
}
</style>

<?php include '../common/scripts.php'; ?>
<!-- DataTables Bootstrap 5 -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/libs/dataTables/datatable-mobile.css">
<script src="<?= BASE_URL ?>assets/libs/dataTables/datatables.min.js"></script>
<!-- Cargar los scripts -->
<script src="../common/js/categorias-manager.js"></script> <!-- Gestor de categorías (COMÚN) -->
<script src="js/revision-capacitaciones/revision-data.js"></script> <!-- revisión-data.js -->
<script src="js/revision-capacitaciones/revision-ui.js"></script> <!-- revisión-ui.js -->
<?php include '../common/footer.php'; ?>