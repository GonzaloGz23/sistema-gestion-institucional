document.addEventListener("DOMContentLoaded", async () => {
    await cargarEdificios();

    const modalEdificio = document.getElementById("modalEdificio");
    const confirmButton = document.getElementById("modalEdificioConfirm");

    modalEdificio.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idEdificio = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const direccion = button.getAttribute("data-direccion");
        const estado = button.getAttribute("data-estado");
        //const idEntidad = button.getAttribute("data-entidad");

        const modalConfig = {
            crear: {
                title: "Nuevo Edificio",
                body: `
                    <form id="formEdificio">
                        <div class="mb-3">
                            <label for="nombreEdificio" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEdificio" name="nombre"  placeholder="..." required>
                        </div>
                       <!--<div class="mb-3">
                            <label for="entidadEdificio" class="col-form-label">Entidad:</label>
                            <select class="form-select d-none" id="entidadEdificio" name="id_entidad"></select>
                            <p id="mensajeSinEntidades" class="text-danger d-none">No hay entidades disponibles. Debes crear una entidad antes de registrar un edificio.</p>
                        </div>-->
                        <div class="mb-3">
                            <label for="direccionEdificio" class="col-form-label">Dirección:</label>
                            <input type="text" class="form-control" id="direccionEdificio" name="direccion" placeholder="..." >
                        </div>
                        <div class="mb-3">
                            <label for="estadoEdificio" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEdificio" name="estado">
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
                title: "Editar Edificio",
                body: `
                <form id="formEdificio" data-id="${idEdificio}">
                    <div class="mb-3">
                        <label for="nombreEdificio" class="col-form-label">Nombre:</label>
                        <input type="text" class="form-control" id="nombreEdificio" name="nombre" value="${nombre}" required>
                    </div>
                    <!--<div class="mb-3">
                        <label for="entidadEdificio" class="col-form-label">Entidad:</label>
                        <select class="form-select" id="entidadEdificio" name="id_entidad"></select>
                    </div>-->
                    <div class="mb-3">
                        <label for="direccionEdificio" class="col-form-label">Dirección:</label>
                        <input type="text" class="form-control" id="direccionEdificio" name="direccion" value="${direccion || ''}">
                    </div>
                    <div class="mb-3">
                        <label for="estadoEdificio" class="col-form-label">Estado:</label>
                        <select class="form-select" id="estadoEdificio" name="estado">
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
                title: `${estado === "habilitado" ? "Deshabilitar" : "Habilitar"} Entidad`,
                body: `<p>¿Seguro que quieres ${estado === "habilitado" ? "deshabilitar" : "habilitar"} esta entidad?</p>`,
                confirmText: estado === "habilitado" ? "Deshabilitar" : "Habilitar",
                confirmClass: estado === "habilitado" ? "btn btn-warning" : "btn btn-success",
                action: "deshabilitar"
            },
            eliminar: {
                title: "Eliminar Edificio",
                body: "<p>¿Seguro que quieres eliminar este edificio? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalEdificioLabel").textContent = modalConfig[action].title;
            document.getElementById("modalEdificioBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idEdificio);

            // **Solo ejecutar si es acción de creación**
            if (action === "crear" || action === "editar") {
                setTimeout(() => {
                    const selectEntidades = document.getElementById("entidadEdificio");
                    if (selectEntidades) {
                        cargarEntidadesDisponibles();
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
            agregarEdificio();
            break;
        case "editar":
            editarEdificio(id);
            break;
        case "deshabilitar":
            const estadoActual = document.querySelector(`[data-id="${id}"]`).getAttribute("data-estado");
            habilitarDeshabilitarEdificio(id, estadoActual);
            break;
        case "eliminar":
            eliminarEdificio(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
