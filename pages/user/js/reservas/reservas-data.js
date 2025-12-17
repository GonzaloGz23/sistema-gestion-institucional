// Cargar reservas existentes
window.cargarReservas = async () => {
  const spinner = document.getElementById("spinnerCarga");
  const mensaje = document.getElementById("mensajeSinReservas");

  try {
    spinner.classList.remove("d-none");
    mensaje.classList.add("d-none");

    const response = await fetch("../../backend/controller/usuario/reservas/listar_reservas.php");
    const result = await response.json();

    spinner.classList.add("d-none");

    if (result.success && result.data.length > 0) {
      return result.data;
    } else {
      mensaje.classList.remove("d-none");
      return [];
    }
  } catch (error) {
    console.error("Error al cargar reservas:", error);
    spinner.classList.add("d-none");
    mensaje.textContent = "Ocurrió un error al cargar las reservas.";
    mensaje.classList.remove("d-none");
    return [];
  }
};

// Cargar espacios disponibles agrupados por edificio
window.cargarEspaciosDisponibles = async () => {
  try {
    const response = await fetch("../../backend/controller/admin/espacios/listar_espacios.php");
    const result = await response.json();

    if (result.success && Array.isArray(result.data)) {
      return result.data.filter(e => e.estado === "habilitado");
    }

    return [];
  } catch (error) {
    console.error("Error al cargar espacios reservables:", error);
    return [];
  }
};

// Cargar horarios ocupados de un espacio en una fecha
window.cargarHorariosOcupados = async (idEspacio, fecha) => {
  try {
    const response = await fetch(`../../backend/controller/usuario/reservas/horarios_ocupados.php?id_espacio=${idEspacio}&fecha=${fecha}`);
    const result = await response.json();
    if (result.success && Array.isArray(result.data)) {
      return result.data;
    }
    return [];
  } catch (error) {
    console.error("Error al cargar horarios ocupados:", error);
    return [];
  }
};

// Enviar una nueva reserva
window.enviarReserva = async (reservaData) => {
  const formData = new FormData();

  formData.append("id_espacio", reservaData.id_espacio);
  formData.append("id_empleado", reservaData.id_empleado);
  formData.append("id_equipo", reservaData.id_equipo || "");
  formData.append("fecha", reservaData.fecha);
  formData.append("hora_inicio", reservaData.hora_inicio);
  formData.append("hora_fin", reservaData.hora_fin);
  formData.append("detalle", reservaData.detalle || "");

  try {
    const response = await fetch("../../backend/controller/usuario/reservas/agregar_reserva.php", {
      method: "POST",
      body: formData
    });

    return await response.json();

  } catch (error) {
    console.error("Error al enviar reserva:", error);
    return {
      success: false,
      message: "No se pudo conectar con el servidor."
    };
  }
};

// Eliminar una reserva
window.eliminarReserva = async (id) => {
  const formData = new FormData();
  formData.append("id_reserva", id);
  formData.append("id_equipo", document.querySelector("main.db-content").dataset.team);

  try {
    const response = await fetch("../../backend/controller/usuario/reservas/cancelar_reserva.php", {
      method: "POST",
      body: formData
    });

    return await response.json();
  } catch (error) {
    console.error("Error al cancelar reserva:", error);
    return {
      success: false,
      message: "Error al cancelar la reserva."
    };
  }
};

// Editar reserva
window.editarReservaBackend = async (reservaData) => {
  const formData = new FormData();
  formData.append("id_reserva", reservaData.id_reserva);
  formData.append("id_espacio", reservaData.id_espacio);
  formData.append("id_empleado", reservaData.id_empleado);
  formData.append("id_equipo", reservaData.id_equipo || "");
  formData.append("fecha", reservaData.fecha);
  formData.append("hora_inicio", reservaData.hora_inicio);
  formData.append("hora_fin", reservaData.hora_fin);
  formData.append("detalle", reservaData.detalle || "");

  try {
    const response = await fetch("../../backend/controller/usuario/reservas/editar_reserva.php", {
      method: "POST",
      body: formData
    });

    return await response.json();
  } catch (error) {
    console.error("Error al editar reserva:", error);
    return {
      success: false,
      message: "Error al editar la reserva."
    };
  }
};

