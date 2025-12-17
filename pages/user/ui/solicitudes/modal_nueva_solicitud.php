<!-- MODAL NUEVA SOLICITUD -->
<div class="modal fade" id="modalNuevaSolicitud" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formNuevaSolicitud">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body d-grid gap-3">

                    <!-- 📌 Equipos destinatarios -->
                    <div>
                        <h6 class="fw-bold">Equipos destinatarios</h6>
                        <select class="form-select" id="selectEquipos" multiple name="equipos[]">
                            <!-- Opciones dinámicas via JS -->
                        </select>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="enviarATodos">
                            <label class="form-check-label" for="enviarATodos">Enviar a todos los equipos</label>

                           
                        </div>
                   <div class="form-check mt-2">
    <input class="form-check-input" type="checkbox" id="privado_checkbox"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="La solicitud solo será visible para su emisor y para el equipo al que se lo envió.">
    <label class="form-check-label" for="privado_checkbox"
           data-bs-toggle="tooltip" data-bs-placement="top"
           title="La solicitud solo será visible para su emisor y para el equipo al que se lo envió.">
        Privado
    </label>

    <input type="hidden" id="privado_valor_enviado" name="privado" value="0">
</div>
                    </div>

                    <!-- ✏️ Contenido -->
                    <div>
                        <h6 class="fw-bold mt-2">Contenido</h6>

                        <label class="form-label">Asunto:</label>
                        <input type="text" class="form-control" id="inputAsunto" placeholder="Asunto">

                        <label class="form-label mt-2">Mensaje inicial:</label>
                        <textarea class="form-control" rows="4" id="inputMensaje" placeholder="Escriba el mensaje..."></textarea>
                        <label class="form-label mt-2">
                            Archivo/s adjunto (opcional)
                            <i class="bi bi-info-circle text-muted ms-1"
                                title="Se permiten archivos Word, Excel, PowerPoint, PDF, TXT e imágenes (JPG, PNG). Máximo 10MB por archivo."></i>
                        </label>
                        <input type="file" class="form-control" id="inputArchivo" name="archivo[]" multiple>
                    </div>

                    <!-- ⚠️ Mensaje de error -->
                    <div id="mensajeErrorSolicitud" class="text-danger small" style="display: none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>


