const cargarAreasDisponibles = async (idSeleccionada = null) => {
    const selectAreas = document.getElementById("areaEquipo");
    const mensajeSinAreas = document.getElementById("mensajeSinAreas");
    const btnGuardarEquipo = document.getElementById("modalEquipoConfirm");

    try {
        const response = await fetch("../../backend/controller/admin/areas/listar_areas.php");
        const result = await response.json();

        selectAreas.innerHTML = ""; // Limpiar el select antes de agregar opciones

        if (result.success && result.data.length > 0) {
            const areasHabilitadas = result.data.filter(area => area.estado === "habilitado");

            if (areasHabilitadas.length > 0) {
                areasHabilitadas.forEach((area) => {
                    const option = document.createElement("option");
                    option.value = area.id_area;
                    option.textContent = area.alias;
                    if (idSeleccionada && area.id_area == idSeleccionada) {
                        option.selected = true;
                    }
                    selectAreas.appendChild(option);
                });

                selectAreas.classList.remove("d-none");
                btnGuardarEquipo.disabled = false; // Habilitar el botón de guardar 

                if (mensajeSinAreas) {
                    mensajeSinAreas.classList.add("d-none"); // Ocultar mensaje de error
                }
            } else {
                selectAreas.classList.add("d-none");
                btnGuardarEquipo.disabled = true; // Deshabilitar el botón de guardar
                if (mensajeSinAreas) {
                    mensajeSinAreas.classList.remove("d-none"); // Mostrar mensaje de error
                }
            }
        } else {
            selectAreas.classList.add("d-none");
            btnGuardarEquipo.disabled = true;

            if (mensajeSinAreas) {
                mensajeSinAreas.classList.remove("d-none");
            }
        }
    } catch (error) {
        console.error("Error al cargar áreas disponibles:", error);
        selectAreas.classList.add("d-none");
        btnGuardarEquipo.disabled = true;
        if (mensajeSinAreas) {
            mensajeSinAreas.classList.remove("d-none");
        }
    }
};

const cargarEquipos = async () => {
    const tablaBody = document.getElementById("tablaEquiposBody");
    const contenedorTabla = document.getElementById("contenedorTabla");
    const mensajeNoEquipos = document.getElementById("mensajeNoEquipos");
    const spinnerCarga = document.getElementById("spinnerCarga");
    const tabla = document.getElementById("tablaEquipos");

    try {
        spinnerCarga.classList.remove("d-none");

        const response = await fetch("../../backend/controller/admin/equipos/listar_equipos.php");
        const result = await response.json();

        spinnerCarga.classList.add("d-none");

        // 🔥 Si ya hay una instancia DataTable, destruirla
        if ($.fn.DataTable.isDataTable(tabla)) {
            $(tabla).DataTable().destroy();
        }

        tablaBody.innerHTML = ""; // Limpiar la tabla antes de agregar nuevos registros

        if (result.success && result.data.length > 0) {
            result.data.forEach((equipo, index) => {
                const fila = `
                    <tr>
                        <th scope="row">${index + 1}</th>
                        <td>${equipo.alias}</td>
                        <td>${equipo.area || "Sin área"}</td>
                        <td>${equipo.estado === "habilitado" ? " <span class='badge bg-primary-soft'>Habilitado</span>" : "<span class='badge bg-secondary-soft'>Deshabilitado</span>"}</td>
                        <td>
                            <button class="btn btn-primary btn-sm m-1" title="Editar" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEquipo"
                                data-action="editar" 
                                data-id="${equipo.id_equipo}" 
                                data-nombre="${equipo.alias}"
                                data-area="${equipo.id_area || ''}"
                                data-estado="${equipo.estado}">
                                <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                    <path d="M2 16H3.425L13.2 6.225L11.775 4.8L2 14.575V16ZM1 18C0.716667 18 0.479333 17.904 0.288 17.712C0.0966668 17.52 0.000666667 17.2827 0 17V14.575C0 14.3083 0.0500001 14.054 0.15 13.812C0.25 13.57 0.391667 13.3577 0.575 13.175L13.2 0.575C13.4 0.391667 13.621 0.25 13.863 0.15C14.105 0.0500001 14.359 0 14.625 0C14.891 0 15.1493 0.0500001 15.4 0.15C15.6507 0.25 15.8673 0.4 16.05 0.6L17.425 2C17.625 2.18333 17.7707 2.4 17.862 2.65C17.9533 2.9 17.9993 3.15 18 3.4C18 3.66667 17.954 3.921 17.862 4.163C17.77 4.405 17.6243 4.62567 17.425 4.825L4.825 17.425C4.64167 17.6083 4.429 17.75 4.187 17.85C3.945 17.95 3.691 18 3.425 18H1ZM12.475 5.525L11.775 4.8L13.2 6.225L12.475 5.525Z" fill="currentColor"/>
                                    </svg>
                            </button>
                            <button 
                               class="btn ${equipo.estado === "habilitado" ? "btn-warning" : "btn-success"} btn-sm m-1" 
                                title="${equipo.estado === "habilitado" ? "Deshabilitar" : "Habilitar"}" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEquipo"
                                data-action="deshabilitar" 
                                data-id="${equipo.id_equipo}" 
                                data-estado="${equipo.estado}">
                                ${
                                  equipo.estado === "habilitado"
                                    ? `<svg viewBox="0 0 14 12" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                        <path d="M13.9722 5.85429C13.4391 4.57321 12.5096 3.46334 11.2981 2.66143L13.5335 0.604286L12.8755 0L0.466481 11.3957L1.1245 12L3.50457 9.81857C4.56709 10.3868 5.7706 10.6952 7 10.7143C8.52114 10.6618 9.99217 10.201 11.2313 9.38911C12.4704 8.57716 13.4233 7.44958 13.9722 6.14571C14.0093 6.05155 14.0093 5.94845 13.9722 5.85429ZM7 8.78571C6.35673 8.78544 5.73042 8.59622 5.21262 8.24571L6.06664 7.47C6.42076 7.64818 6.82824 7.71611 7.22795 7.6636C7.62767 7.61109 7.99807 7.44098 8.28359 7.17877C8.56911 6.91657 8.75435 6.57642 8.81153 6.20934C8.86871 5.84226 8.79474 5.46806 8.60071 5.14286L9.4454 4.36714C9.77417 4.78183 9.97167 5.27192 10.0161 5.78325C10.0604 6.29458 9.94999 6.80723 9.69692 7.26451C9.44384 7.72179 9.058 8.10589 8.58204 8.37436C8.10609 8.64282 7.55856 8.78519 7 8.78571ZM1.64718 8.49L3.98058 6.34714C3.96862 6.23177 3.96394 6.11587 3.96658 6C3.96781 5.26153 4.2878 4.55363 4.85641 4.03145C5.42502 3.50928 6.19587 3.21542 7 3.21429C7.12323 3.21504 7.24632 3.22219 7.36868 3.23571L9.13273 1.62C8.44614 1.40352 7.7259 1.29063 7 1.28571C5.47886 1.33825 4.00783 1.79895 2.76871 2.61089C1.52959 3.42284 0.576693 4.55042 0.0278023 5.85429C-0.00926742 5.94845 -0.00926742 6.05155 0.0278023 6.14571C0.386705 7.02091 0.937937 7.8189 1.64718 8.49Z" fill="currentColor"/>
                                      </svg>`
                                    : `<svg viewBox="0 0 56 38" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                        <path d="M28 11.5C26.0109 11.5 24.1032 12.2902 22.6967 13.6967C21.2902 15.1032 20.5 17.0109 20.5 19C20.5 20.9891 21.2902 22.8968 22.6967 24.3033C24.1032 25.7098 26.0109 26.5 28 26.5C29.9891 26.5 31.8968 25.7098 33.3033 24.3033C34.7098 22.8968 35.5 20.9891 35.5 19C35.5 17.0109 34.7098 15.1032 33.3033 13.6967C31.8968 12.2902 29.9891 11.5 28 11.5ZM28 31.5C24.6848 31.5 21.5054 30.183 19.1612 27.8388C16.817 25.4946 15.5 22.3152 15.5 19C15.5 15.6848 16.817 12.5054 19.1612 10.1612C21.5054 7.81696 24.6848 6.5 28 6.5C31.3152 6.5 34.4946 7.81696 36.8388 10.1612C39.183 12.5054 40.5 15.6848 40.5 19C40.5 22.3152 39.183 25.4946 36.8388 27.8388C34.4946 30.183 31.3152 31.5 28 31.5ZM28 0.25C15.5 0.25 4.825 8.025 0.5 19C4.825 29.975 15.5 37.75 28 37.75C40.5 37.75 51.175 29.975 55.5 19C51.175 8.025 40.5 0.25 28 0.25Z" fill="currentColor"/>
                                      </svg>`
                                }
                            </button>
                            <button class="btn btn-danger btn-sm m-1" title="Eliminar" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalEquipo"
                                data-action="eliminar" 
                                data-id="${equipo.id_equipo}">
                                <svg viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                  <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="currentColor"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
                tablaBody.innerHTML += fila;
            });

            contenedorTabla.classList.remove("d-none");
            mensajeNoEquipos.classList.add("d-none");

            // 🔥 Inicializar DataTable ahora que se llenó la tabla
            $(tabla).DataTable({
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                language: {
                    url: "../../dist/assets/libs/dataTables/es-ES.json"
                }
            });
        } else {
            mensajeNoEquipos.classList.remove("d-none");
            contenedorTabla.classList.add("d-none");
        }
    } catch (error) {
        console.error("Error al cargar equipos:", error);
        mensajeNoEquipos.classList.remove("d-none");
        contenedorTabla.classList.add("d-none");
    }
};


const agregarEquipo = async () => {
    const form = document.getElementById("formEquipo");
    const formData = new FormData(form);

    try {
        const response = await fetch("../../backend/controller/admin/equipos/agregar_equipos.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success", "Equipo agregado correctamente", "¡Éxito!");

            document.getElementById("modalEquipo").querySelector(".btn-close").click();
            cargarEquipos();
        } else {
            mostrarAlerta("info", result.message, "¡Atención!");
        }
    } catch (error) {
        console.error("Error al agregar equipo:", error);
        mostrarAlerta("error", "Hubo un problema al agregar el equipo", "¡Error!");
    }
};

const editarEquipo = async () => {
    const form = document.getElementById("formEquipo");
    const formData = new FormData(form);
    const idEquipo = form.getAttribute("data-id");

    formData.append("id", idEquipo);

    try {
        const response = await fetch("../../backend/controller/admin/equipos/editar_equipos.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success", "Equipo actualizado correctamente", "¡Éxito!");

            document.getElementById("modalEquipo").querySelector(".btn-close").click();
            cargarEquipos();
        } else {
            mostrarAlerta("info", result.message, "¡Atención!");
        }
    } catch (error) {
        console.error("Error al editar equipo:", error);
        mostrarAlerta("error", "Hubo un problema al editar el equipo", "¡Error!");
    }
};

const habilitarDeshabilitarEquipo = async (id, estadoActual) => {
    const formData = new FormData();
    formData.append("id", id);
    formData.append("estado", estadoActual);

    try {
        const response = await fetch("../../backend/controller/admin/equipos/habilitar_deshabilitar_equipos.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success", "Equipo actualizado correctamente", "¡Éxito!");

            document.getElementById("modalEquipo").querySelector(".btn-close").click();
            cargarEquipos();
        } else {
            mostrarAlerta("info", result.message, "¡Atención!");
        }
    } catch (error) {
        console.error("Error al actualizar equipo:", error);
        mostrarAlerta("error", "Hubo un problema al actualizar el equipo", "¡Error!");
    }
};

const eliminarEquipo = async (id) => {
    const formData = new FormData();
    formData.append("id", id);

    try {
        const response = await fetch("../../backend/controller/admin/equipos/eliminar_equipos.php", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            mostrarAlerta("success", "Equipo eliminado correctamente", "¡Éxito!");

            document.getElementById("modalEquipo").querySelector(".btn-close").click();
            cargarEquipos();
        } else {
            mostrarAlerta("info", result.message, "¡Atención!");
        }
    } catch (error) {
        console.error("Error al eliminar equipo:", error);
        mostrarAlerta("error", "Hubo un problema al eliminar el equipo", "¡Error!");
    }
};
