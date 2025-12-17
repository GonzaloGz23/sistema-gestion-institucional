<div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalCrearRolLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formCrearRol">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCrearRolLabel">Crear Nuevo Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="nombreRol" class="form-label">Nombre del Rol</label>
            <input type="text" class="form-control" id="nombreRol" name="nombre" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Módulos disponibles</label>
            <div id="contenedorModulos" class="row g-2">
              <!-- Aquí se insertarán los módulos con checkboxes vía JS -->
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar Rol</button>
        </div>
      </form>
    </div>
  </div>
</div>
