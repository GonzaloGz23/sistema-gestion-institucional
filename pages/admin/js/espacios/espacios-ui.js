document.addEventListener("DOMContentLoaded", async () => {
    await cargarEspacios();

    const modalEspacio = document.getElementById("modalEspacio");
    const confirmButton = document.getElementById("modalEspacioConfirm");

    modalEspacio.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idEspacio = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const detalles = button.getAttribute("data-detalles");
        const estado = button.getAttribute("data-estado");
        const idEdificio = button.getAttribute("data-edificio");

        const modalConfig = {
            crear: {
                title: "Nuevo Espacio Reservable",
                body: `
                    <form id="formEspacio">
                        <div class="mb-3">
                            <label for="nombreEspacio" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEspacio" name="nombre" placeholder="..." required>
                        </div>
                        <div class="mb-3">
                            <label for="edificioEspacio" class="col-form-label">Edificio:</label>
                            <select class="form-select d-none" id="edificioEspacio" name="id_edificio"></select>
                            <p id="mensajeSinEdificios" class="text-danger d-none">No hay edificios disponibles. Debes crear un edificio antes de registrar un espacio reservable.</p>
                        </div>
                        <div class="mb-3">
                            <label for="detallesEspacio" class="col-form-label">Detalles:</label>
                            <input type="text" class="form-control" id="detallesEspacio" name="detalles" placeholder="...">
                        </div>
                        <div class="mb-3">
                            <label for="estadoEspacio" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEspacio" name="estado">
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
                title: "Editar Espacio Reservable",
                body: `
                    <form id="formEspacio" data-id="${idEspacio}">
                        <div class="mb-3">
                            <label for="nombreEspacio" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEspacio" name="nombre" value="${nombre}" required>
                        </div>
                        <div class="mb-3">
                            <label for="edificioEspacio" class="col-form-label">Edificio:</label>
                            <select class="form-select" id="edificioEspacio" name="id_edificio"></select>
                        </div>
                        <div class="mb-3">
                            <label for="detallesEspacio" class="col-form-label">Detalles:</label>
                            <input type="text" class="form-control" id="detallesEspacio" name="detalles" value="${detalles || ''}">
                        </div>
                        <div class="mb-3">
                            <label for="estadoEspacio" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEspacio" name="estado">
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
                title: `${estado === "habilitado" ? "Deshabilitar" : "Habilitar"} Espacio`,
                body: `<p>¿Seguro que quieres ${estado === "habilitado" ? "deshabilitar" : "habilitar"} este espacio reservable?</p>`,
                confirmText: estado === "habilitado" ? "Deshabilitar" : "Habilitar",
                confirmClass: estado === "habilitado" ? "btn btn-warning" : "btn btn-success",
                action: "deshabilitar"
            },
            eliminar: {
                title: "Eliminar Espacio Reservable",
                body: "<p>¿Seguro que quieres eliminar este espacio? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalEspacioLabel").textContent = modalConfig[action].title;
            document.getElementById("modalEspacioBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idEspacio);

            // **Solo ejecutar si es acción de creación o edición**
            if (action === "crear" || action === "editar") {
                setTimeout(() => {
                    const selectEdificios = document.getElementById("edificioEspacio");
                    if (selectEdificios) {
                        cargarEdificiosDisponibles(idEdificio);
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
            agregarEspacio();
            break;
        case "editar":
            editarEspacio(id);
            break;
        case "deshabilitar":
            const estadoActual = document.querySelector(`[data-id="${id}"]`).getAttribute("data-estado");
            habilitarDeshabilitarEspacio(id, estadoActual);
            break;
        case "eliminar":
            eliminarEspacio(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
