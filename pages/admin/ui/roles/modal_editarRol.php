<div class="modal fade" id="modalEditarRol" tabindex="-1" aria-labelledby="modalEditarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditarRol">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarRolLabel">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editar_idRol" name="id_rol">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Rol</label>
                        <input type="text" class="form-control" id="editar_nombreRol" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" id="editar_descripcionRol" name="descripcion"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Módulos asignados</label>
                        <div id="editar_contenedorModulos" class="row g-2">
                            <!-- Módulos se insertarán dinámicamente -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>