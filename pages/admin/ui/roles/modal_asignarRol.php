<div class="modal fade" id="modalAsignarRol" tabindex="-1" aria-labelledby="modalAsignarRolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formAsignarRol">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAsignarRolLabel">Asignar Rol a Empleados</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="asignar_idRol" name="id_rol">
                    <div class="mb-3">
                        <label class="form-label">Seleccione los empleados:</label>
                        <div id="contenedorEmpleados" class="row g-2">
                            <!-- Empleados se cargarán dinámicamente -->
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