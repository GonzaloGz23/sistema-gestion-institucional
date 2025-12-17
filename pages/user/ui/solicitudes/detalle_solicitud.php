<!-- DETALLE DE SOLICITUD -->
<style>
    #contenedorChat {
        overflow-y: auto;
        scrollbar-width: thin;
        /* Para Firefox */
        scrollbar-color: rgba(0, 0, 0, 0.3) transparent;
        /* Color y fondo */
    }

    #contenedorChat::-webkit-scrollbar {
        width: 8px;
        /* Ancho del scrollbar */
    }

    #contenedorChat::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 4px;
    }
</style>
<div class="container-fluid pb-2 bg-white" id="detalleSolicitud" style="display: none;">
    <button class="btn btn-link p-0 my-3" id="btnVolver">← Volver a la bandeja</button>

    <div class="mb-3 border-bottom pb-2">
        <!-- Fila principal: avatar + destino/asunto + estado -->
        <div class="row align-items-center">
            <!-- Avatar + textos -->
            <div class="col-12 col-sm-8 d-flex align-items-center gap-3">
                <img src="../../dist/assets/images/avatar/equipo_avatar.webp" class="rounded-circle" alt="Equipo"
                    width="48" height="48">

                <div>
                    <h5 id="detalleDestino" class="fw-semibold mb-1 text-dark">De: Equipo</h5>
                    <div id="detalleAsunto" class="text-muted small">Asunto de la solicitud</div>
                </div>
            </div>

            <!-- Estado -->
            <div class="col-12 col-sm-4 d-flex align-items-center justify-content-end gap-2 flex-wrap mt-3 mt-md-0">
                <div id="spinnerEstado" class="spinner-border spinner-border-sm text-primary" role="status"
                    style="display: none;"></div>

                <label for="estadoSolicitud" class="form-label fw-semibold mb-0">Estado:</label>
                <div id="contenedorSelectEstado">
                    <select class="form-select form-select-sm w-auto" id="estadoSolicitud">
                        <option value="pendiente">Pendiente</option>
                        <option value="resuelta">Resuelta</option>
                        <option value="rechazada">Rechazado</option>
                    </select>
                </div>

                <div id="contenedorBadgeEstado" class="d-none">
                    <span class="badge badge-estado" id="badgeEstado">...</span>
                </div>
            </div>
        </div>

        <!-- Etiqueta -->
        <div class="row mt-2">
            <div class="col">
                <span class="badge bg-info-soft me-1" id="badgeEtiqueta" style="display: none;"></span>
            </div>
        </div>
    </div>

    <h6 class="fw-semibold mt-3 mb-2">Mensajes
        <span id="indicadorNuevoMensaje" class="badge bg-primary text-white ms-2"
            style="display: none; cursor: pointer;">Nuevo</span>
    </h6>

    <!-- Chat de seguimiento -->
    <div id="contenedorChat" class="bg-light border rounded p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
        <!-- Se renderiza desde JS -->
    </div>

    <!-- Formulario de respuesta -->
    <form id="formRespuesta" class="d-flex flex-column border rounded px-2 py-2 gap-2">
        <div id="archivosSeleccionados" class="d-flex flex-wrap gap-2"></div>

        <div class="d-flex align-items-end gap-2 align-items-center">
            <label class="btn btn-light btn-sm mb-0" title="Adjuntar archivo">
                <i class="bi bi-paperclip"></i>
                <input type="file" id="inputAdjuntosChat" multiple hidden />
            </label>
            <textarea class="form-control border-0" rows="1" placeholder="Escribí tu mensaje..."
                style="resize: none;"></textarea>
            <button class="btn btn-primary btn-sm" type="submit">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </form>
</div>

<!-- MENÚ CONTEXTUAL PARA MENSAJES DEL CHAT -->
<div id="menuContextualMensaje" class="dropdown-menu shadow" style="position: absolute; display: none; z-index: 2000;">
    <button class="dropdown-item text-danger" id="opcionEliminarMensaje">Eliminar</button>
</div>