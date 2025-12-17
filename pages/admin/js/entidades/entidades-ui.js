document.addEventListener("DOMContentLoaded", async () => {
    await cargarEntidades(); // Cargar entidades al inicio

    const modalEntidad = document.getElementById("modalEntidad");
    const confirmButton = document.getElementById("modalEntidadConfirm");

    modalEntidad.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idEntidad = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const descripcion = button.getAttribute("data-descripcion");
        const estado = button.getAttribute("data-estado");

        //console.log(action, idEntidad, estado); // Verifica que los datos lleguen correctamente

        const modalConfig = {
            crear: {
                title: "Nueva Entidad",
                body: `
                    <form id="formEntidad">
                        <div class="mb-3">
                            <label for="nombreEntidad" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEntidad" name="nombre" placeholder="..." required>
                        </div>
                        <!--<div class="mb-3">
                            <label for="descripcionEntidad" class="col-form-label">Descripción:</label>
                            <textarea class="form-control" id="descripcionEntidad" name="descripcion"></textarea>
                        </div>-->
                        <div class="mb-3">
                            <label for="estadoEntidad" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEntidad" name="estado">
                                <option value="habilitado">Habilitada</option>
                                <option value="deshabilitado">Deshabilitada</option>
                            </select>
                        </div>
                    </form>`,
                confirmText: "Guardar",
                confirmClass: "btn btn-primary",
                action: "crear"
            },
            editar: {
                title: "Editar Entidad",
                body: `
                    <form id="formEntidad" data-id="${idEntidad}">
                        <div class="mb-3">
                            <label for="nombreEntidad" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEntidad" name="nombre" value="${nombre}" required>
                        </div>
                        <!--<div class="mb-3">
                            <label for="descripcionEntidad" class="col-form-label">Descripción:</label>
                            <textarea class="form-control" id="descripcionEntidad" name="descripcion">${descripcion || ""}</textarea>
                        </div>-->
                        <div class="mb-3">
                            <label for="estadoEntidad" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEntidad" name="estado">
                                <option value="habilitado" ${estado === "habilitado" ? "selected" : ""}>Habilitada</option>
                                <option value="deshabilitado" ${estado === "deshabilitado" ? "selected" : ""}>Deshabilitada</option>
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
                title: "Eliminar Entidad",
                body: "<p>¿Seguro que quieres eliminar esta entidad? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalEntidadLabel").textContent = modalConfig[action].title;
            document.getElementById("modalEntidadBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idEntidad);
            confirmButton.setAttribute("data-estado", estado);
        }
    });

    // Manejar todas las acciones desde un solo evento
    confirmButton.addEventListener("click", () => {
        const action = confirmButton.getAttribute("data-action");
        const id = confirmButton.getAttribute("data-id");
        const estado = confirmButton.getAttribute("data-estado");
        manejarConfirmacion(action, id, estado);
    });
});

// Función para manejar la acción de confirmación
const manejarConfirmacion = (action, id, estado) => {
    switch (action) {
        case "crear":
            agregarEntidad();
            break;
        case "editar":
            editarEntidad();
            break;
        case "deshabilitar":
            habilitarDeshabilitarEntidad(id, estado);
            break;
        case "eliminar":
            eliminarEntidad(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
