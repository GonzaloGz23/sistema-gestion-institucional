document.addEventListener("DOMContentLoaded", async () => {
    await cargarEquipos();

    const modalEquipo = document.getElementById("modalEquipo");
    const confirmButton = document.getElementById("modalEquipoConfirm");

    modalEquipo.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idEquipo = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const estado = button.getAttribute("data-estado");
        const idArea = button.getAttribute("data-area");

        const modalConfig = {
            crear: {
                title: "Nuevo Equipo",
                body: `
                    <form id="formEquipo">
                        <div class="mb-3">
                            <label for="nombreEquipo" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEquipo" name="nombre" placeholder="..." required>
                        </div>
                        <div class="mb-3">
                            <label for="areaEquipo" class="col-form-label">Área:</label>
                            <select class="form-select d-none" id="areaEquipo" name="id_area"></select>
                            <p id="mensajeSinAreas" class="text-danger d-none">No hay áreas disponibles. Debes crear un área antes de registrar un equipo.</p>
                        </div>
                        <div class="mb-3">
                            <label for="estadoEquipo" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEquipo" name="estado">
                                <option value="habilitado">Habilitado</option>
                                <option value="deshabilitado">Deshabilitado</option>
                            </select>
                        </div>
                    </form>`,
                confirmText: "Guardar",
                confirmClass: "btn btn-primary",
                action: "crear"
            },
            editar: {
                title: "Editar Equipo",
                body: `
                    <form id="formEquipo" data-id="${idEquipo}">
                        <div class="mb-3">
                            <label for="nombreEquipo" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEquipo" name="nombre" value="${nombre}" required>
                        </div>
                        <div class="mb-3">
                            <label for="areaEquipo" class="col-form-label">Área:</label>
                            <select class="form-select" id="areaEquipo" name="id_area"></select>
                        </div>
                        <div class="mb-3">
                            <label for="estadoEquipo" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEquipo" name="estado">
                                <option value="habilitado" ${estado === "habilitado" ? "selected" : ""}>Habilitado</option>
                                <option value="deshabilitado" ${estado === "deshabilitado" ? "selected" : ""}>Deshabilitado</option>
                            </select>
                        </div>
                    </form>`,
                confirmText: "Guardar Cambios",
                confirmClass: "btn btn-warning",
                action: "editar"
            },
            deshabilitar: {
                title: `${estado === "habilitado" ? "Deshabilitar" : "Habilitar"} Equipo`,
                body: `<p>¿Seguro que quieres ${estado === "habilitado" ? "deshabilitar" : "habilitar"} este equipo?</p>`,
                confirmText: estado === "habilitado" ? "Deshabilitar" : "Habilitar",
                confirmClass: estado === "habilitado" ? "btn btn-warning" : "btn btn-success",
                action: "deshabilitar"
            },
            eliminar: {
                title: "Eliminar Equipo",
                body: "<p>¿Seguro que quieres eliminar este equipo? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalEquipoLabel").textContent = modalConfig[action].title;
            document.getElementById("modalEquipoBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idEquipo);

            // **Solo ejecutar si es acción de creación o edición**
            if (action === "crear" || action === "editar") {
                setTimeout(() => {
                    const selectAreas = document.getElementById("areaEquipo");
                    if (selectAreas) {
                        cargarAreasDisponibles(idArea);
                    }
                }, 10);
            }
        }
    });

    confirmButton.addEventListener("click", () => {
        const action = confirmButton.getAttribute("data-action");
        const id = confirmButton.getAttribute("data-id");
        manejarConfirmacion(action, id);
    });
});

// Función para manejar la acción de confirmación
const manejarConfirmacion = (action, id) => {
    switch (action) {
        case "crear":
            agregarEquipo();
            break;
        case "editar":
            editarEquipo(id);
            break;
        case "deshabilitar":
            const estadoActual = document.querySelector(`[data-id="${id}"]`).getAttribute("data-estado");
            habilitarDeshabilitarEquipo(id, estadoActual);
            break;
        case "eliminar":
            eliminarEquipo(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
