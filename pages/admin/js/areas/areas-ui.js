document.addEventListener("DOMContentLoaded", async () => {
    await cargarAreas();

    const modalArea = document.getElementById("modalArea");
    const confirmButton = document.getElementById("modalAreaConfirm");

    modalArea.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idArea = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const estado = button.getAttribute("data-estado");

        const modalConfig = {
            crear: {
                title: "Nueva Área",
                body: `
                    <form id="formArea">
                        <div class="mb-3">
                            <label for="nombreArea" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreArea" name="nombre" placeholder="..." required>
                        </div>
                        <div class="mb-3">
                            <label for="estadoArea" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoArea" name="estado">
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
                title: "Editar Área",
                body: `
                    <form id="formArea" data-id="${idArea}">
                        <div class="mb-3">
                            <label for="nombreArea" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreArea" name="nombre" value="${nombre}" required>
                        </div>
                        <div class="mb-3">
                            <label for="estadoArea" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoArea" name="estado">
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
                title: `${estado === "habilitado" ? "Deshabilitar" : "Habilitar"} Área`,
                body: `<p>¿Seguro que quieres ${estado === "habilitado" ? "deshabilitar" : "habilitar"} esta área?</p>`,
                confirmText: estado === "habilitado" ? "Deshabilitar" : "Habilitar",
                confirmClass: estado === "habilitado" ? "btn btn-warning" : "btn btn-success",
                action: "deshabilitar"
            },
            eliminar: {
                title: "Eliminar Área",
                body: "<p>¿Seguro que quieres eliminar esta área? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalAreaLabel").textContent = modalConfig[action].title;
            document.getElementById("modalAreaBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idArea);

            // **Solo ejecutar si es acción de creación o edición**
            if (action === "crear" || action === "editar") {
                setTimeout(() => {
                    const selectEntidades = document.getElementById("entidadArea");
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
            agregarArea();
            break;
        case "editar":
            editarArea(id);
            break;
        case "deshabilitar":
            const estadoActual = document.querySelector(`[data-id="${id}"]`).getAttribute("data-estado");
            habilitarDeshabilitarArea(id, estadoActual);
            break;
        case "eliminar":
            eliminarArea(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
