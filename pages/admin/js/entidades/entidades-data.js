const cargarEntidades = async () => {
    const tablaBody = document.getElementById("tablaEntidadesBody");
    const contenedorTabla = document.getElementById("contenedorTabla");
    const mensajeNoEntidades = document.getElementById("mensajeNoEntidades");
    const spinnerCarga = document.getElementById("spinnerCarga");

    try {
        spinnerCarga.classList.remove("d-none");

        const response = await fetch("../../backend/controller/admin/entidades/listar_entidades.php");
        const result = await response.json();

        spinnerCarga.classList.add("d-none");

        // **LIMPIAR LA TABLA ANTES DE AGREGAR NUEVOS REGISTROS**
        tablaBody.innerHTML = "";

        if (result.success && result.data.length > 0) {
            result.data.forEach((entidad, index) => {
                const fila = `
                    <tr>
                        <th scope="row">${index + 1}</th>
                        <td>${entidad.nombre}</td>
                        <td>${entidad.estado === "habilitado" ? "✅ Habilitada" : "❌ Deshabilitada"}</td>
                        <td>${entidad.cantidad_edificios}</td> 
                        <td>${entidad.cantidad_areas}</td>
                        <td>
                            <button 
                                class="btn btn-primary btn-sm me-2 mb-1" 
                                title="Editar" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEntidad" 
                                data-action="editar" 
                                data-id="${entidad.id_entidad}" 
                                data-nombre="${entidad.nombre}" 
                                data-descripcion="${entidad.descripcion || ''}" 
                                data-estado="${entidad.estado}">
                                <i class="bi bi-pencil"></i>
                            </button>
                             <button 
                                class="btn ${entidad.estado === "habilitado" ? "btn-warning" : "btn-success"} btn-sm me-2 mb-1" 
                                title="${entidad.estado === "habilitado" ? "Deshabilitar" : "Habilitar"}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEntidad"
                                data-action="deshabilitar" 
                                data-id="${entidad.id_entidad}" 
                                data-estado="${entidad.estado}">
                                <i class="bi ${entidad.estado === "habilitado" ? "bi-eye-slash" : "bi-eye"}"></i>
                            </button>
                            <button 
                                class="btn btn-danger btn-sm mb-1" 
                                title="Eliminar" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEntidad"
                                data-action="eliminar" 
                                data-id="${entidad.id_entidad}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tablaBody.innerHTML += fila;
            });

            // Mostrar la tabla y ocultar el mensaje vacío
            contenedorTabla.classList.remove("d-none");
            mensajeNoEntidades.classList.add("d-none");
        } else {
            // Mostrar el mensaje de "No hay entidades" y ocultar la tabla
            mensajeNoEntidades.classList.remove("d-none");
            contenedorTabla.classList.add("d-none");
        }
    } catch (error) {
        console.error("Error al cargar entidades:", error);
        mensajeNoEntidades.classList.remove("d-none");
        contenedorTabla.classList.add("d-none");
    }
};

const agregarEntidad = async () => {
    const form = document.getElementById("formEntidad");
    const formData = new FormData(form);

    try {
        const response = await fetch("../../backend/controller/admin/entidades/agregar_entidades.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success","Entidad agregada correctamente","¡Éxito!");
            document.getElementById("modalEntidad").querySelector(".btn-close").click(); // Cierra el modal
            cargarEntidades(); // Recarga la tabla
        } else {
            mostrarAlerta("info",result.message,"¡Atención!");
        }
    } catch (error) {
        console.error("Error al agregar entidad:", error);
        mostrarAlerta("error","Hubo un problema al agregar la entidad","¡Error!");
    }
};

const editarEntidad = async () => {
    const form = document.getElementById("formEntidad");
    const formData = new FormData(form);
    const idEntidad = form.getAttribute("data-id");

    formData.append("id", idEntidad);

    try {
        const response = await fetch("../../backend/controller/admin/entidades/editar_entidad.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success","Entidad actualizada correctamente","¡Éxito!");
            document.getElementById("modalEntidad").querySelector(".btn-close").click();
            cargarEntidades();
        } else {
            mostrarAlerta("info",result.message,"¡Atención!");
        }
    } catch (error) {
        console.error("Error al editar entidad:", error);
        mostrarAlerta("error","Hubo un problema al editar la entidad","¡Error!");
    }
};

const habilitarDeshabilitarEntidad = async (id, estadoActual) => {
    const formData = new FormData();
    formData.append("id", id);
    formData.append("estado", estadoActual);

    try {
        const response = await fetch("../../backend/controller/admin/entidades/habilitar_deshabilitar_entidad.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success","Entidad actualizada correctamente","¡Éxito!");
            // **Cerrar el modal**
            document.getElementById("modalEntidad").querySelector(".btn-close").click();

            // **Recargar la tabla**
            cargarEntidades();
        } else {
            mostrarAlerta("info",result.message,"¡Atención!");
        }
    } catch (error) {
        console.error("Error al actualizar entidad:", error);
        mostrarAlerta("error","Hubo un problema al actualizar la entidad","¡Error!");
    }
};

const eliminarEntidad = async (id) => {
    const formData = new FormData();
    formData.append("id", id);

    try {
        const response = await fetch("../../backend/controller/admin/entidades/eliminar_entidad.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success","Entidad eliminada correctamente","¡Éxito!");

            // **Cerrar el modal**
            document.getElementById("modalEntidad").querySelector(".btn-close").click();

            // **Recargar la tabla**
            cargarEntidades();
        } else {
            mostrarAlerta("info",result.message,"¡Atención!");
        }
    } catch (error) {
        console.error("Error al eliminar entidad:", error);
        mostrarAlerta("error","Hubo un problema al eliminar la entidad","¡Error!");
    }
};


