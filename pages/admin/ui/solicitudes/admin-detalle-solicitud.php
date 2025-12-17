<!-- DETALLE DE SOLICITUD - MODO ADMIN (SOLO LECTURA) -->
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

            <!-- Estado (Solo lectura en modo admin) -->
            <div class="col-12 col-sm-4 d-flex align-items-center justify-content-end gap-2 flex-wrap mt-3 mt-md-0">
                <label class="form-label fw-semibold mb-0">Estado:</label>
                
                <!-- Selector oculto inicialmente (no se usa en admin pero se mantiene estructura) -->
                <div id="contenedorSelectEstado" style="display: none;">
                    <select class="form-select form-select-sm w-auto" id="estadoSolicitud" disabled>
                        <option value="pendiente">Pendiente</option>
                        <option value="resuelta">Resuelta</option>
                        <option value="rechazada">Rechazado</option>
                    </select>
                </div>

                <!-- Badge de estado (solo lectura) -->
                <div id="contenedorBadgeEstado" class="d-none">
                    <span class="badge badge-estado" id="badgeEstado">...</span>
                </div>
            </div>
        </div>

        <!-- Etiqueta (si existe) -->
        <div class="row mt-2">
            <div class="col">
                <span class="badge bg-info-soft me-1" id="badgeEtiqueta" style="display: none;"></span>
            </div>
        </div>
    </div>

    <h6 class="fw-semibold mt-3 mb-2">
        <i class="bi bi-chat-dots me-2"></i>Historial de Mensajes
        <span class="badge bg-light text-muted ms-2">Solo lectura</span>
    </h6>

    <!-- Chat de seguimiento (solo lectura) -->
    <div id="contenedorChat" class="bg-light border rounded p-3 mb-3" style="max-height: 400px; overflow-y: auto;">
        <!-- Se renderiza desde JS -->
    </div>
</div>