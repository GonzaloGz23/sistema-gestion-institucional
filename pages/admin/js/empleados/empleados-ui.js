document.addEventListener("DOMContentLoaded", async () => {
    await cargarEmpleados();

    const modalEmpleado = document.getElementById("modalEmpleado");
    const confirmButton = document.getElementById("modalEmpleadoConfirm");

    modalEmpleado.addEventListener("show.bs.modal", (event) => {
        const button = event.relatedTarget;
        const action = button.getAttribute("data-action");
        const idEmpleado = button.getAttribute("data-id");
        const nombre = button.getAttribute("data-nombre");
        const apellido = button.getAttribute("data-apellido");
        const dni = button.getAttribute("data-dni");
        const usuario = button.getAttribute("data-usuario") || "";
        const estado = button.getAttribute("data-estado");
        const idRol = button.getAttribute("data-id-rol");
        const idEquipo = button.getAttribute("data-id-equipo");
        const idEdificio = button.getAttribute("data-id-edificio");

        const modalConfig = {
            crear: {
                title: "Nuevo Empleado",
                body: `
                    <form id="formEmpleado">
                        <div class="mb-3">
                            <label for="selectRol" class="col-form-label">Rol:</label>
                            <select class="form-select d-none" id="selectRol" name="id_rol" required></select>
                            <p id="mensajeSinRoles" class="text-danger d-none">No hay roles disponibles. Debes crear un rol antes de registrar un empleado.</p>
                        </div>
                        <div class="mb-3">
                            <label for="nombreEmpleado" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEmpleado" name="nombre" placeholder="..." required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidoEmpleado" class="col-form-label">Apellido:</label>
                            <input type="text" class="form-control" id="apellidoEmpleado" name="apellido" placeholder="..." required>
                        </div>
                        <div class="mb-3">
                            <label for="dniEmpleado" class="col-form-label">DNI:</label>
                            <input type="text" class="form-control" id="dniEmpleado" name="dni" required pattern="^[0-9]{1,8}$" title="Debe contener solo números (máximo 8 caracteres)" placeholder="xx.xxx.xxx">
                        </div>
                        <div class="mb-3">
                            <label for="usuarioEmpleado" class="col-form-label">Usuario:</label>
                            <input type="text" class="form-control" id="usuarioEmpleado" name="usuario" placeholder="nuevo_usuario" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasenaEmpleado" class="col-form-label">Contraseña:</label>
                            <input type="password" class="form-control" id="contrasenaEmpleado" name="contrasena" placeholder="******" required minlength="6" title="Debe contener al menos 6 caracteres">
                        </div>
                        <div class="mb-3">
                            <label for="equipoEmpleado" class="col-form-label">Equipo:</label>
                            <select class="form-select d-none" id="equipoEmpleado" name="id_equipo"></select>
                            <p id="mensajeSinEquipos" class="text-danger d-none">No hay equipos disponibles. Debes crear un equipo antes de registrar un empleado.</p>
                        </div>
                        <div class="mb-3">
                            <label for="edificioEmpleado" class="col-form-label">Edificio:</label>
                            <select class="form-select d-none" id="edificioEmpleado" name="id_edificio"></select>
                            <p id="mensajeSinEdificios" class="text-danger d-none">No hay edificios disponibles. Debes crear un edificio antes de registrar un empleado.</p>
                        </div>
                        <div class="mb-3">
                            <label for="estadoEmpleado" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEmpleado" name="estado">
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
                title: "Editar Empleado",
                body: `
                    <form id="formEmpleado" data-id="${idEmpleado}">
                       <div class="mb-3">
                            <label for="selectRol" class="col-form-label">Rol:</label>
                            <select class="form-select d-none" id="selectRol" name="id_rol" required></select>
                            <p id="mensajeSinRoles" class="text-danger d-none">No hay roles disponibles. Debes crear un rol antes de registrar un empleado.</p>
                        </div>
                        <div class="mb-3">
                            <label for="nombreEmpleado" class="col-form-label">Nombre:</label>
                            <input type="text" class="form-control" id="nombreEmpleado" name="nombre" value="${nombre}" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidoEmpleado" class="col-form-label">Apellido:</label>
                            <input type="text" class="form-control" id="apellidoEmpleado" name="apellido" value="${apellido}" required>
                        </div>
                          <div class="mb-3">
                            <label for="dniEmpleado" class="col-form-label">DNI:</label>
                            <input 
                            type="text" 
                            class="form-control" 
                            id="dniEmpleado" 
                            name="dni"
                            value="${dni ? dni : ''}" 
                            placeholder="${dni ? '' : 'xx.xxx.xxx'}"
                            required 
                            pattern="^[0-9]{1,8}$" 
                            title="Debe contener solo números (máximo 8 caracteres)"
                            >
                        </div>
                        <div class="mb-3">
                            <label for="usuarioEmpleado" class="col-form-label">Usuario:</label>
                            <input type="text" class="form-control" id="usuarioEmpleado" name="usuario" value="${usuario}" required>
                        </div>
                        <div class="mb-3">
                            <label for="contrasenaEmpleado" class="col-form-label">Nueva Contraseña (dejar vacío para no cambiar):</label>
                            <input type="password" class="form-control" id="contrasenaEmpleado" name="contrasena" minlength="6" title="Debe contener al menos 6 caracteres">
                        </div>              
                        <div class="mb-3">
                            <label for="equipoEmpleado" class="col-form-label">Equipo:</label>
                            <select class="form-select d-none" id="equipoEmpleado" name="id_equipo"></select>
                            <p id="mensajeSinEquipos" class="text-danger d-none">No hay equipos disponibles. Debes crear un equipo antes de registrar un empleado.</p>
                        </div>
                        <div class="mb-3">
                            <label for="edificioEmpleado" class="col-form-label">Edificio:</label>
                            <select class="form-select d-none" id="edificioEmpleado" name="id_edificio"></select>
                            <p id="mensajeSinEdificios" class="text-danger d-none">No hay edificios disponibles. Debes crear un edificio antes de registrar un empleado.</p>
                        </div>      
                        <div class="mb-3">
                            <label for="estadoEmpleado" class="col-form-label">Estado:</label>
                            <select class="form-select" id="estadoEmpleado" name="estado">
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
                title: `${estado === "habilitado" ? "Deshabilitar" : "Habilitar"} Empleado`,
                body: `<p>¿Seguro que quieres ${estado === "habilitado" ? "deshabilitar" : "habilitar"} este empleado?</p>`,
                confirmText: estado === "habilitado" ? "Deshabilitar" : "Habilitar",
                confirmClass: estado === "habilitado" ? "btn btn-warning" : "btn btn-success",
                action: "deshabilitar"
            },
            eliminar: {
                title: "Eliminar Empleado",
                body: "<p>¿Seguro que quieres eliminar este empleado? Esta acción no se puede deshacer.</p>",
                confirmText: "Eliminar",
                confirmClass: "btn btn-danger",
                action: "eliminar"
            }
        };

        if (modalConfig[action]) {
            document.getElementById("modalEmpleadoLabel").textContent = modalConfig[action].title;
            document.getElementById("modalEmpleadoBody").innerHTML = modalConfig[action].body;
            confirmButton.textContent = modalConfig[action].confirmText;
            confirmButton.className = `btn ${modalConfig[action].confirmClass}`;
            confirmButton.setAttribute("data-action", modalConfig[action].action);
            confirmButton.setAttribute("data-id", idEmpleado);

            if (action === "crear") {
                setTimeout(() => {
                    cargarRolesDisponibles();
                    cargarEquiposDisponibles();
                    cargarEdificiosDisponibles();
                }, 10);
            }

            if (action === "editar") {
                const idRol = button.getAttribute("data-id-rol");
                setTimeout(() => {
                    cargarRolesDisponibles(idRol)
                    cargarEquiposDisponibles(idEquipo);
                    cargarEdificiosDisponibles(idEdificio);
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
            agregarEmpleado();
            break;
        case "editar":
            editarEmpleado(id);
            break;
        case "deshabilitar":
            const estadoActual = document.querySelector(`[data-id="${id}"]`).getAttribute("data-estado");
            habilitarDeshabilitarEmpleado(id, estadoActual);
            break;
        case "eliminar":
            eliminarEmpleado(id);
            break;
        default:
            console.error("Acción no reconocida:", action);
    }
};
