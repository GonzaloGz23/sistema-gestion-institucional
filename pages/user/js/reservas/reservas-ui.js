const renderReservas = (reservas) => {
  const ahora = new Date();
  const contenedorActivas = document.getElementById("contenedorReservasActivas");
  const contenedorHistoricas = document.getElementById("contenedorReservasHistoricas");

  contenedorActivas.innerHTML = "";
  contenedorHistoricas.innerHTML = "";

  const activas = reservas.filter(r => new Date(`${r.fecha}T${r.hora_fin}`) > ahora);
  const historicas = reservas
    .filter(r => new Date(`${r.fecha}T${r.hora_fin}`) <= ahora)
    .sort((a, b) => new Date(`${b.fecha}T${b.hora_fin}`) - new Date(`${a.fecha}T${a.hora_fin}`))
    .slice(0, 5);

  const renderGrupo = (lista, contenedor, esPasada) => {
    const equipoActual = document.querySelector("main.db-content").dataset.team;

    if (lista.length === 0) {
      contenedor.innerHTML = `<p class="text-muted">No hay reservas ${esPasada ? 'finalizadas' : 'activas'}.</p>`;
      return;
    }

    lista.forEach((reserva) => {
      const card = document.createElement("div");

      const fechaFormateada = reserva.fecha.split("-").reverse().join("-");
      const horaInicio = reserva.hora_inicio.substring(0, 5);
      const horaFin = reserva.hora_fin.substring(0, 5);

      const finReserva = new Date(`${reserva.fecha}T${reserva.hora_fin}`);
      const yaPaso = finReserva < new Date();

      const inicioReserva = new Date(`${reserva.fecha}T${reserva.hora_inicio}`);
      const yaEmpezo = inicioReserva <= new Date();

      const puedeModificar = reserva.id_equipo == equipoActual && !esPasada;

      card.className = `card mb-2 ${esPasada ? 'border-secondary bg-light text-muted' : ''}`;

      card.innerHTML = `
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">
                ${reserva.espacio} 
                <span class="text-muted">(${reserva.edificio})</span>
              </h6>
              <span class="badge ${esPasada ? 'bg-secondary' : 'bg-primary'}">${fechaFormateada}</span>
            </div>
    
            <div class="small mb-1">⏰ ${horaInicio} - ${horaFin}</div>
            ${reserva.detalle ? `<div class="small">📄 ${reserva.detalle}</div>` : ''}
            <div class="small">👥 ${reserva.equipo || 'Sin equipo asignado'}</div>
    
            ${puedeModificar ? `
                <div class="mt-3 text-end">
                  ${!yaEmpezo ? `
                    <button class="btn btn-outline-primary btn-sm me-2" onclick='editarReserva(${JSON.stringify(reserva).replace(/"/g, "&quot;")})'>
                      <i class="bi bi-pencil"></i> Editar
                    </button>
                  ` : ''}
                  <button class="btn btn-outline-danger btn-sm" onclick="cancelarReserva(${reserva.id_reserva})">
                    <i class="bi bi-x-circle"></i> Cancelar
                  </button>
                </div>
              ` : esPasada ? `
                <div class="mt-3 small text-end fst-italic">Reserva finalizada</div>
              ` : ''
        }
          </div>
        `;

      contenedor.appendChild(card);
    });
  };

  renderGrupo(activas, contenedorActivas, false);
  renderGrupo(historicas, contenedorHistoricas, true);
};

const mostrarModalReserva = async () => {
  const modalBody = document.getElementById("modalReservaBody");
  const modalLabel = document.getElementById("modalReservaLabel");
  modalLabel.textContent = "Nueva Reserva";
  const hoy = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

  modalBody.innerHTML = `
    <form id="formReserva">
      <div class="mb-3">
        <label for="espacio" class="form-label">Espacio</label>
        <select class="form-select" id="espacio" required></select>
      </div>
      <div class="mb-3">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" class="form-control" id="fecha" required min="${hoy}">
      </div>
      <div id="horariosOcupados" class="alert alert-warning d-none">
        <strong>Horarios ya reservados:</strong>
        <ul id="listaHorarios" class="mb-0"></ul>
      </div>
      <div class="mb-3">
        <label for="horaInicio" class="form-label">Hora de Inicio</label>
        <input type="time" class="form-control" id="horaInicio" required>
      </div>
      <div class="mb-3">
        <label for="horaFin" class="form-label">Hora de Fin</label>
        <input type="time" class="form-control" id="horaFin" required>
      </div>
      <div class="mb-3">
        <label for="detalle" class="form-label">Detalle</label>
        <input type="text" class="form-control" id="detalle" placeholder="Motivo o descripción">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Reservar</button>
      </div>
    </form>
  `;

  const modal = new bootstrap.Modal(document.getElementById("modalReserva"));
  modal.show();

  await prepararFormularioReserva();
};

const prepararFormularioReserva = async (esEdicion = false, idReserva = null, idEspacioSeleccionado = null) => {
  const form = document.getElementById("formReserva");
  const selectEspacio = document.getElementById("espacio");
  const inputFecha = document.getElementById("fecha");

  let reservasOcupadas = [];

  // Cargar espacios agrupados por edificio
  const espacios = await cargarEspaciosDisponibles();
  if (espacios.length === 0) {
    selectEspacio.innerHTML = '<option value="">No hay espacios habilitados</option>';
    selectEspacio.disabled = true;
  } else {
    selectEspacio.innerHTML = '';
    const agrupados = {};
    espacios.forEach(e => {
      if (!agrupados[e.edificio]) agrupados[e.edificio] = [];
      agrupados[e.edificio].push(e);
    });
    for (const edificio in agrupados) {
      const group = document.createElement("optgroup");
      group.label = edificio;
      agrupados[edificio].forEach(e => {
        const option = document.createElement("option");
        option.value = e.id_espacio;
        option.textContent = e.alias;
        if (idEspacioSeleccionado && e.id_espacio == idEspacioSeleccionado) {
          option.selected = true;
        }
        group.appendChild(option);
      });
      selectEspacio.appendChild(group);
    }
    selectEspacio.disabled = false;
  }

  const mostrarHorariosOcupados = (lista) => {
    const container = document.getElementById("horariosOcupados");
    const ul = document.getElementById("listaHorarios");
    ul.innerHTML = "";
    if (lista.length === 0) {
      container.classList.add("d-none");
      return;
    }
    lista.forEach(r => {
      const item = document.createElement("li");
      const horaInicio = r.hora_inicio.substring(0, 5);
      const horaFin = r.hora_fin.substring(0, 5);
      item.textContent = `${horaInicio} - ${horaFin} - ${r.equipo || 'Reservado'}`;
      ul.appendChild(item);
    });
    container.classList.remove("d-none");
  };

  const actualizarHorarios = async () => {
    const id = selectEspacio.value;
    const fecha = inputFecha.value;
    reservasOcupadas = [];

    if (id && fecha) {
      reservasOcupadas = await cargarHorariosOcupados(id, fecha);
      mostrarHorariosOcupados(reservasOcupadas);
    }
  };

  selectEspacio.addEventListener("change", actualizarHorarios);
  inputFecha.addEventListener("change", actualizarHorarios);

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const btnSubmit = form.querySelector("button[type='submit']");
    btnSubmit.disabled = true;
    const originalHTML = btnSubmit.innerHTML;

    btnSubmit.innerHTML = `
      <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
      Guardando...
    `;

    // ⏳ Le da tiempo al navegador para renderizar el spinner
    await new Promise(r => setTimeout(r, 50));

    const horaInicio = document.getElementById("horaInicio").value;
    const horaFin = document.getElementById("horaFin").value;

    if (horaInicio >= horaFin) {
      mostrarAlerta("warning", "La hora de fin debe ser posterior a la de inicio.", "Horario inválido");
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = originalHTML;
      return;
    }

    const hayConflicto = reservasOcupadas.some(r =>
      horaInicio < r.hora_fin && r.hora_inicio < horaFin
    );

    if (hayConflicto) {
      mostrarAlerta("error", "El espacio ya está reservado en ese rango horario.", "Conflicto de horario");
      btnSubmit.disabled = false;
      btnSubmit.innerHTML = originalHTML;
      return;
    }

    const reservaData = {
      id_espacio: selectEspacio.value,
      id_empleado: document.querySelector("main.db-content").dataset.user,
      id_equipo: document.querySelector("main.db-content").dataset.team,
      fecha: inputFecha.value,
      hora_inicio: horaInicio,
      hora_fin: horaFin,
      detalle: document.getElementById("detalle").value,
      id_reserva: idReserva
    };

    const result = esEdicion
      ? await editarReservaBackend(reservaData)
      : await enviarReserva(reservaData);

    btnSubmit.disabled = false;
    btnSubmit.innerHTML = originalHTML;

    if (result.success) {
      mostrarAlerta("success", result.message, "Reserva confirmada");
      bootstrap.Modal.getInstance(document.getElementById("modalReserva")).hide();
      form.reset();

      const nuevasReservas = await cargarReservas();
      renderReservas(nuevasReservas);
    } else {
      mostrarAlerta("error", result.message, "Error al registrar");
    }
  });
};

document.addEventListener("DOMContentLoaded", async () => {
  const reservas = await cargarReservas();
  renderReservas(reservas);

  const btnFlotante = document.getElementById("btnFloatingReserva");
  if (btnFlotante) {
    btnFlotante.addEventListener("click", mostrarModalReserva);
  }

  // 🔄 Ajuste dinámico del botón flotante de reservas
  const ajustarBotonFlotanteReserva = () => {
    const barraInferior = document.getElementById('barraInferior');
    const boton = document.getElementById('btnFloatingReserva');

    if (!barraInferior || !boton) return;

    const visible = window.getComputedStyle(barraInferior).display !== 'none';
    boton.style.bottom = visible ? '72px' : '1rem'; // subirlo si hay barra
  };

  // Ejecutar al cargar y al redimensionar
  ajustarBotonFlotanteReserva();
  window.addEventListener('resize', ajustarBotonFlotanteReserva);

  // Ocultar botón si hay un modal abierto
  const botonFlotante = document.getElementById('btnFloatingReserva');

  if (botonFlotante) {
    document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('show.bs.modal', () => {
        botonFlotante.style.display = 'none';
      });

      modal.addEventListener('hidden.bs.modal', () => {
        ajustarBotonFlotanteReserva();
        botonFlotante.style.display = 'block';
      });
    });
  }


});

const editarReserva = async (reserva) => {
  const modalBody = document.getElementById("modalReservaBody");
  const modalLabel = document.getElementById("modalReservaLabel");
  modalLabel.textContent = "Editar Reserva";

  const hoy = new Date().toISOString().split("T")[0]; // Para min en fecha

  modalBody.innerHTML = `
    <form id="formReserva" data-id="${reserva.id_reserva}">
      <div class="mb-3">
        <label for="espacio" class="form-label">Espacio</label>
        <select class="form-select" id="espacio" required></select>
      </div>
      <div class="mb-3">
        <label for="fecha" class="form-label">Fecha</label>
        <input type="date" class="form-control" id="fecha" required value="${reserva.fecha}" min="${hoy}">
      </div>
      <div id="horariosOcupados" class="alert alert-warning d-none">
        <strong>Horarios ya reservados:</strong>
        <ul id="listaHorarios" class="mb-0"></ul>
      </div>
      <div class="mb-3">
        <label for="horaInicio" class="form-label">Hora de Inicio</label>
        <input type="time" class="form-control" id="horaInicio" required value="${reserva.hora_inicio.substring(0, 5)}">
      </div>
      <div class="mb-3">
        <label for="horaFin" class="form-label">Hora de Fin</label>
        <input type="time" class="form-control" id="horaFin" required value="${reserva.hora_fin.substring(0, 5)}">
      </div>
      <div class="mb-3">
        <label for="detalle" class="form-label">Detalle</label>
        <input type="text" class="form-control" id="detalle" value="${reserva.detalle || ''}">
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  `;

  const modal = new bootstrap.Modal(document.getElementById("modalReserva"));
  modal.show();

  await prepararFormularioReserva(true, reserva.id_reserva, reserva.id_espacio);
};


const cancelarReserva = async (id) => {
  await confirmarAccion("¿Cancelar reserva? Esta acción no se puede deshacer.", async () => {
    // Bloquea todos los botones con ese onclick
    const botones = document.querySelectorAll(`button[onclick="cancelarReserva(${id})"]`);
    botones.forEach(btn => {
      btn.disabled = true;
      btn.innerHTML = `
        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
        Cancelando...
      `;
    });

    const result = await eliminarReserva(id);

    if (result.success) {
      mostrarAlerta("success", result.message, "Reserva cancelada");
      const reservasActualizadas = await cargarReservas();
      renderReservas(reservasActualizadas);
    } else {
      mostrarAlerta("error", result.message || "No se pudo cancelar la reserva.", "Error");
      // Restaurar botón solo si hubo error
      botones.forEach(btn => {
        btn.disabled = false;
        btn.innerHTML = `
          <i class="bi bi-x-circle"></i> Cancelar
        `;
      });
    }
  });
};

