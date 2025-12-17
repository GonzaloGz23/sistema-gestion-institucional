
document.addEventListener('DOMContentLoaded', () => {
    cargarTablaRoles();

    // Cargar módulos cuando se abre el modal
    document.getElementById('modalCrearRol').addEventListener('show.bs.modal', () => {
        fetch('../../backend/controller/admin/roles/listar_modulos.php')
            .then(res => res.json())
            .then(data => {
                const contenedor = document.getElementById('contenedorModulos');
                contenedor.innerHTML = '';

                if (data.success) {
                    const modulosAdmin = data.modulos.filter(m => m.perfil === 'admin');
                    const modulosUser = data.modulos.filter(m => m.perfil === 'user');

                    if (modulosAdmin.length > 0) {
                        contenedor.innerHTML += `<div class="col-12"><strong>🛠️ Módulos de Administrador</strong></div>`;
                        modulosAdmin.forEach(modulo => {
                            contenedor.innerHTML += `
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${modulo.id_modulo}" id="modulo-${modulo.id_modulo}" name="modulos[]">
                        <label class="form-check-label" for="modulo-${modulo.id_modulo}">
                          ${modulo.nombre}
                        </label>
                      </div>
                    </div>
                  `;
                        });
                    }

                    if (modulosUser.length > 0) {
                        contenedor.innerHTML += `<div class="col-12 mt-3"><strong>🧑‍💼 Módulos de Empleado</strong></div>`;
                        modulosUser.forEach(modulo => {
                            contenedor.innerHTML += `
                    <div class="col-md-4">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${modulo.id_modulo}" id="modulo-${modulo.id_modulo}" name="modulos[]">
                        <label class="form-check-label" for="modulo-${modulo.id_modulo}">
                          ${modulo.nombre}
                        </label>
                      </div>
                    </div>
                  `;
                        });
                    }
                }
            });
    });

    // Enviar el formulario para crear el rol
    document.getElementById('formCrearRol').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../../backend/controller/admin/roles/crear_rol.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
            });
    });
});

function cargarTablaRoles() {
    fetch('../../backend/controller/admin/roles/listar_roles.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const tabla = document.getElementById('tablaRoles');
                const tbody = document.getElementById('tablaRolesBody');

                // 🔥 Si ya existe una instancia de DataTable, destruirla
                if ($.fn.DataTable.isDataTable(tabla)) {
                    $(tabla).DataTable().destroy();
                }

                tbody.innerHTML = '';

                if (data.roles.length > 0) {
                    document.getElementById('contenedorTabla').classList.remove('d-none');

                    data.roles.forEach((rol, index) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${rol.alias}</td>
                            <td>${rol.modulos || '-'}</td>
                            <td>
                                <button class="btn btn-primary btn-sm m-1" title="Editar" onclick="editarRol(${rol.id_rol})">
                                    <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                    <path d="M2 16H3.425L13.2 6.225L11.775 4.8L2 14.575V16ZM1 18C0.716667 18 0.479333 17.904 0.288 17.712C0.0966668 17.52 0.000666667 17.2827 0 17V14.575C0 14.3083 0.0500001 14.054 0.15 13.812C0.25 13.57 0.391667 13.3577 0.575 13.175L13.2 0.575C13.4 0.391667 13.621 0.25 13.863 0.15C14.105 0.0500001 14.359 0 14.625 0C14.891 0 15.1493 0.0500001 15.4 0.15C15.6507 0.25 15.8673 0.4 16.05 0.6L17.425 2C17.625 2.18333 17.7707 2.4 17.862 2.65C17.9533 2.9 17.9993 3.15 18 3.4C18 3.66667 17.954 3.921 17.862 4.163C17.77 4.405 17.6243 4.62567 17.425 4.825L4.825 17.425C4.64167 17.6083 4.429 17.75 4.187 17.85C3.945 17.95 3.691 18 3.425 18H1ZM12.475 5.525L11.775 4.8L13.2 6.225L12.475 5.525Z" fill="currentColor"/>
                                    </svg>
                                </button>
                                <button class="btn btn-danger btn-sm m-1" title="Eliminar" onclick="confirmarEliminarRol(${rol.id_rol})">
                                    <svg viewBox="0 0 14 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:1em;height:1em;">
                                  <path d="M14 1H10.5L9.5 0H4.5L3.5 1H0V3H14M1 16C1 16.5304 1.21071 17.0391 1.58579 17.4142C1.96086 17.7893 2.46957 18 3 18H11C11.5304 18 12.0391 17.7893 12.4142 17.4142C12.7893 17.0391 13 16.5304 13 16V4H1V16Z" fill="currentColor"/>
                                </svg>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // 🔥 Inicializar DataTables
                    $(tabla).DataTable({
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        language: {
                            url: "../../dist/assets/libs/dataTables/es-ES.json"
                        }
                    });
                } else {
                    document.getElementById('contenedorTabla').classList.add('d-none');
                }
            }
        });
}

function editarRol(idRol) {
    fetch(`../../backend/controller/admin/roles/obtener_rol.php?id=${idRol}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const rol = data.rol;

                // Rellenar campos del formulario
                document.getElementById('editar_idRol').value = rol.id_rol;
                document.getElementById('editar_nombreRol').value = rol.alias;
                document.getElementById('editar_descripcionRol').value = rol.descripcion;

                const contenedor = document.getElementById('editar_contenedorModulos');
                contenedor.innerHTML = '';

                // Separar por tipo de perfil
                const modulosAdmin = data.modulos.filter(m => m.perfil === "admin");
                const modulosEmpleado = data.modulos.filter(m => m.perfil === "user");

                // Renderizar módulos de administrador
                if (modulosAdmin.length > 0) {
                    contenedor.innerHTML += `<div class="col-12"><strong>🛠️ Módulos de Administrador</strong></div>`;
                    modulosAdmin.forEach(modulo => {
                        const checked = rol.modulos.includes(modulo.id_modulo) ? 'checked' : '';
                        contenedor.innerHTML += `
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${modulo.id_modulo}" name="modulos[]" id="moduloEditar-${modulo.id_modulo}" ${checked}>
                                        <label class="form-check-label" for="moduloEditar-${modulo.id_modulo}">
                                            ${modulo.nombre}
                                        </label>
                                    </div>
                                </div>
                            `;
                    });
                }

                // Renderizar módulos de empleado
                if (modulosEmpleado.length > 0) {
                    contenedor.innerHTML += `<div class="col-12 mt-3"><strong>🧑‍💼 Módulos de Empleado</strong></div>`;
                    modulosEmpleado.forEach(modulo => {
                        const checked = rol.modulos.includes(modulo.id_modulo) ? 'checked' : '';
                        contenedor.innerHTML += `
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="${modulo.id_modulo}" name="modulos[]" id="moduloEditar-${modulo.id_modulo}" ${checked}>
                                        <label class="form-check-label" for="moduloEditar-${modulo.id_modulo}">
                                            ${modulo.nombre}
                                        </label>
                                    </div>
                                </div>
                            `;
                    });
                }

                // Mostrar modal
                new bootstrap.Modal(document.getElementById('modalEditarRol')).show();
            }
        });
}


/*     function editarRol(idRol) {
        fetch(`../../backend/controller/admin/roles/obtener_rol.php?id=${idRol}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const rol = data.rol;
                    console.log(rol);
                    
                    document.getElementById('editar_idRol').value = rol.id_rol;
                    document.getElementById('editar_nombreRol').value = rol.alias;
                    document.getElementById('editar_descripcionRol').value = rol.descripcion;

                    const contenedor = document.getElementById('editar_contenedorModulos');
                    contenedor.innerHTML = '';
                    data.modulos.forEach(modulo => {
                        const checked = rol.modulos.includes(modulo.id_modulo) ? 'checked' : '';
                        contenedor.innerHTML += `
                        <div class="col-md-4">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="${modulo.id_modulo}" name="modulos[]" id="moduloEditar-${modulo.id_modulo}" ${checked}>
                            <label class="form-check-label" for="moduloEditar-${modulo.id_modulo}">
                              ${modulo.nombre}
                            </label>
                          </div>
                        </div>
                    `;
                    });

                    new bootstrap.Modal(document.getElementById('modalEditarRol')).show();
                }
            });
    } */

document.getElementById('formAsignarRol').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch('../../backend/controller/admin/roles/asignar_rol.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignarRol'));
                    modal.hide();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
        });
});

document.getElementById('formEditarRol').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch('../../backend/controller/admin/roles/editar_rol.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarRol'));
                    modal.hide();
                    cargarTablaRoles();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
        });
});

function asignarRol(idRol) {
    document.getElementById('asignar_idRol').value = idRol;
    fetch(`../../backend/controller/admin/roles/listar_empleados.php`)
        .then(res => res.json())
        .then(data => {
            const contenedor = document.getElementById('contenedorEmpleados');
            contenedor.innerHTML = '';
            data.empleados.forEach(e => {
                contenedor.innerHTML += `
                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="empleados[]" value="${e.id_empleado}" id="emp-${e.id_empleado}">
                        <label class="form-check-label" for="emp-${e.id_empleado}">
                          ${e.nombre} ${e.apellido}
                        </label>
                      </div>
                    </div>
                `;
            });

            new bootstrap.Modal(document.getElementById('modalAsignarRol')).show();
        });
}

function confirmarEliminarRol(idRol) {
    Swal.fire({
        title: '¿Eliminar rol?',
        /* text: 'Esta acción solo lo ocultará del sistema.', */
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarRol(idRol);
        }
    });
}

function eliminarRol(idRol) {
    const formData = new FormData();
    formData.append('id_rol', idRol);

    fetch('../../backend/controller/admin/roles/eliminar_rol.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    cargarTablaRoles();
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un error inesperado', 'error');
        });
}
