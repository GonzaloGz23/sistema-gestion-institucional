<!-- MODAL GESTIÓN DE ETIQUETAS -->
<div class="modal fade" id="modalTags" tabindex="-1" aria-labelledby="modalTagsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formTags">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTagsLabel">Gestionar etiquetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Nueva etiqueta..." id="inputNuevaTag" />
                        <button class="btn btn-primary" type="submit">Agregar</button>
                    </div>
                    <!-- AQUI va el mensaje de error -->
                    <div id="mensajeErrorTag" class="text-danger small mb-2" style="display: none;"></div>
                    <div id="contenedorTags" class="d-flex flex-wrap gap-2">
                        <!-- Etiquetas se agregan dinámicamente -->
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>