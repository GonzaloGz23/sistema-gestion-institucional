<!-- Modal Editar Evento -->
<div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="ModalEditLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditar" method="POST" action="../../backend/controller/usuario/calendario/editar_evento.php">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalEditLabel">Editar Evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">

          <!-- Título + Selector de color -->
          <div class="row g-3 align-items-center mb-3">
            <div class="col-10">
              <label for="editTitle" class="form-label">Título</label>
              <input type="text" name="title" id="editTitle" class="form-control" required>
            </div>
            <div class="col-2">
              <label class="form-label">Color</label>
              <div class="edit-color-selector position-relative">
                <button type="button" id="editColorActual" class="rounded-circle border border-2"
                  style="width: 32px; height: 32px; background-color: #4285f4;"></button>
                <div id="editPaletaColores" class="color-palette shadow" style="display: none;">
                  <!-- Botones generados por JS -->
                </div>
                <input type="hidden" name="color" id="editColor" value="#4285f4">
              </div>
            </div>
          </div>

          <!-- Tipo de evento -->
          <div class="mb-3">
            <label for="editTipoEvento" class="form-label">Tipo de Evento</label>
            <select name="tipo-evento" class="form-select" id="editTipoEvento" disabled>
              <option value="">Seleccionar tipo</option>
              <option value="Institucional">Institucional</option>
              <option value="Equipo">Equipo</option>
              <option value="Individual">Individual</option>
              <option value="Personalizado">Personalizado</option>
            </select>
            <input type="hidden" name="tipo-evento" id="editTipoEventoHidden">
          </div>

          <!-- Opciones si es Personalizado -->
          <div id="editPersonalizadoOpciones" class="mb-3 d-none">
            <label class="form-label">Asignar a:</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="editPersonalizadoTipo"
                  id="editPersonalizadoEmpleados" value="empleados" disabled>
                <label class="form-check-label" for="editPersonalizadoEmpleados">Empleados</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="editPersonalizadoTipo" id="editPersonalizadoEquipos"
                  value="equipos" disabled>
                <label class="form-check-label" for="editPersonalizadoEquipos">Equipos</label>
              </div>
            </div>
          </div>

          <!-- Select de empleados -->
          <div id="editSelectEmpleados" class="mb-3 d-none">
            <label for="editEmpleadosSeleccionados" class="form-label">Seleccionar Empleados</label>
            <select multiple class="form-select" id="editEmpleadosSeleccionados" name="empleadosSeleccionados[]">
              <!-- Opciones cargadas dinámicamente -->
            </select>
          </div>

          <!-- Select de equipos -->
          <div id="editSelectEquipos" class="mb-3 d-none">
            <label for="editEquiposSeleccionados" class="form-label">Seleccionar Equipos</label>
            <select multiple class="form-select" id="editEquiposSeleccionados" name="equiposSeleccionados[]">
              <!-- Opciones cargadas dinámicamente -->
            </select>
          </div>

          <!-- Fechas y horas -->
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">
              <label class="form-label">Desde</label>
              <input type="datetime-local" class="form-control" id="editFechaHoraInicio">
            </div>
            <div class="col-md-6">
              <label class="form-label">Hasta</label>
              <input type="datetime-local" class="form-control" id="editFechaHoraFin">
            </div>
          </div>

          <!-- Todo el día -->
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="editCheckTodoDia">
            <label class="form-check-label" for="editCheckTodoDia">Todo el día</label>
          </div>

          <!-- Descripción -->
          <div class="mb-3">
            <label for="editDescription" class="form-label">Descripción</label>
            <textarea name="description" class="form-control" id="editDescription" rows="2"></textarea>
          </div>

          <!-- Campos ocultos -->
          <input type="hidden" name="start" id="editStart">
          <input type="hidden" name="end" id="editEnd">
          <input type="hidden" name="id" id="editId">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="btnEliminarEvento">Eliminar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </div>
      </form>
      <form id="formEliminar" action="../../backend/controller/usuario/calendario/eliminar_evento.php" method="POST"
        class="d-none">
        <input type="hidden" name="id" id="eliminarId">
      </form>
    </div>
  </div>
</div>