<!-- Modal Agregar Evento -->
<div class="modal fade" id="ModalAdd" tabindex="-1" aria-labelledby="ModalAddLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="../../backend/controller/usuario/calendario/agregar_evento.php"
        onsubmit="return validarFechas();">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalAddLabel">Agregar Evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">

          <!-- Fila 1: Título + selector de color -->
          <div class="row g-3 align-items-center mb-3">
            <div class="col-10">
              <label for="title" class="form-label">Título</label>
              <input type="text" placeholder="Nuevo evento" name="title" class="form-control" id="title" required>
            </div>
            <div class="col-2">
              <label class="form-label">Color</label>
              <div class="color-selector position-relative">
                <button type="button" id="colorActual" class="rounded-circle border border-2"
                  style="width: 32px; height: 32px; background-color: #ea4335;"></button>
                <div id="paletaColores" class="color-palette shadow" style="display: none;">
                  <!-- Botones generados por JS -->
                </div>
                <input type="hidden" name="color" id="color" value="#ea4335">
              </div>
            </div>
          </div>

          <!-- Tipo de evento -->
          <div class="mb-3">
            <label for="tipo-evento" class="form-label">Tipo de Evento</label>
            <select name="tipo-evento" class="form-select" id="tipo-evento" required>
              <option value="">Seleccionar tipo</option>
              <?php if ($usuarioActual->id_equipo == 2 && $usuarioActual->id_rol == 3): ?>
                <option value="Institucional">Institucional</option>
              <?php endif; ?>
              <option value="Equipo">Equipo</option>
              <option value="Individual">Individual</option>
              <option value="Personalizado">Compartido</option>
            </select>
          </div>

          <!-- Opciones si selecciona Personalizado -->
          <div id="personalizadoOpciones" class="mb-3 d-none">
            <label class="form-label">Asignar a:</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="personalizadoTipo" id="personalizadoEmpleados"
                  value="empleados">
                <label class="form-check-label" for="personalizadoEmpleados">Empleados</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="personalizadoTipo" id="personalizadoEquipos"
                  value="equipos">
                <label class="form-check-label" for="personalizadoEquipos">Equipos</label>
              </div>
            </div>
          </div>

          <!-- Select de empleados -->
          <div id="selectEmpleados" class="mb-3 d-none">
            <label for="empleadosSeleccionados" class="form-label">Seleccionar Empleados</label>
            <select multiple class="form-select" id="empleadosSeleccionados" name="empleadosSeleccionados[]">
              <!-- Opciones se cargarán dinámicamente -->
            </select>
          </div>

          <!-- Select de equipos -->
          <div id="selectEquipos" class="mb-3 d-none">
            <label for="equiposSeleccionados" class="form-label">Seleccionar Equipos</label>
            <select multiple class="form-select" id="equiposSeleccionados" name="equiposSeleccionados[]">
              <!-- Opciones se cargarán dinámicamente -->
            </select>
          </div>

          <!-- Fechas y horas -->
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-6">
              <label class="form-label">Desde</label>
              <input type="datetime-local" class="form-control" id="fechaHoraInicio">
            </div>
            <div class="col-md-6">
              <label class="form-label">Hasta</label>
              <input type="datetime-local" class="form-control" id="fechaHoraFin">
            </div>
          </div>

          <!-- Todo el día -->
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="checkTodoDia">
            <label class="form-check-label" for="checkTodoDia">Todo el día</label>
          </div>

          <!-- Descripción -->
          <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea name="description" class="form-control" id="description" rows="2"></textarea>
          </div>

          <!-- Campos ocultos  -->
          <input type="hidden" name="id_creador" id="idCreador">
          <input type="hidden" name="id_equipo_creador" id="idEquipoCreador">
          <input type="hidden" name="start" id="start">
          <input type="hidden" name="end" id="end">
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>