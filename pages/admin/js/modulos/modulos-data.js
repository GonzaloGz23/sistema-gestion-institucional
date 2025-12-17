document.addEventListener("DOMContentLoaded", async () => {
    const spinner = document.getElementById("spinnerCarga");
    const contenedorTabla = document.getElementById("contenedorTabla");
    const mensajeNoAreas = document.getElementById("mensajeNoAreas");
    const tablaBody = document.getElementById("tablaAreasBody");
    let modulos = [];

    try {
        spinner.classList.remove("d-none");

        const response = await fetch("../../backend/controller/admin/modulos/modulos_listar.php");
        const data = await response.json();

        if (!data.success || !data.data || data.data.length === 0) {
            mensajeNoAreas.classList.remove("d-none");
            spinner.classList.add("d-none");
            return;
        }

        modulos = data.data;
        renderTabla(modulos, tablaBody);

        contenedorTabla.classList.remove("d-none");
        spinner.classList.add("d-none");

      // Destruir instancia previa si existe
if ($.fn.DataTable.isDataTable('#tablaAreas')) {
    $('#tablaAreas').DataTable().destroy();
}

let tabla = new DataTable('#tablaAreas', {
    language: {
        url: "https://cdn.datatables.net/plug-ins/2.1.8/i18n/es-ES.json"
    },
    responsive: true,
    pageLength: 10
});

// Esperar a que DataTables genere el buscador
setTimeout(() => {
    let inputBuscador = document.querySelector('#dt-search-0');

    if (inputBuscador) {
        inputBuscador.addEventListener('keyup', function () {
            tabla.columns(1).search(this.value).draw(); // Buscar solo por columna Nombre
        });
    }
}, 150);


    } catch (error) {
        console.error("Error al cargar los módulos:", error);
        spinner.classList.add("d-none");
        mensajeNoAreas.textContent = "Error al obtener los módulos.";
        mensajeNoAreas.classList.remove("d-none");
    }

    // === Render tabla ===
    renderTabla(modulos, document.getElementById("tablaAreasBody"));


    // === Detectar cambios en selects e inputs ===
    tablaBody.addEventListener("change", async (e) => {
        const campo = e.target.dataset.campo;
        if (!campo) return;

        const fila = e.target.closest("tr");
        const id = fila.dataset.id;
        const valor = e.target.value;

        const confirmed = await Swal.fire({
            title: "¿Guardar cambios?",
            text: `¿Deseas actualizar el campo "${campo}"?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33"
        });

        if (!confirmed.isConfirmed) return;

        const ok = await actualizarCampo(id, campo, valor);
        if (ok) {
            actualizarFila(fila, campo, valor);
            mostrarToast("Campo actualizado correctamente");
        }
    });

    // === Detectar cambios en celdas editables ===
    tablaBody.addEventListener("blur", async (e) => {
        if (e.target.hasAttribute("contenteditable")) {
            const campo = e.target.dataset.campo;
            const fila = e.target.closest("tr");
            const id = fila.dataset.id;
            const valor = e.target.textContent.trim();

            const confirmed = await Swal.fire({
                title: "¿Guardar cambios?",
                text: `¿Deseas guardar el nuevo valor para "${campo}"?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, guardar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33"
            });

            if (!confirmed.isConfirmed) return;

            const ok = await actualizarCampo(id, campo, valor);
            if (ok) {
                actualizarFila(fila, campo, valor);
                mostrarToast("Campo actualizado correctamente");
            }
        }
    }, true);

    // === Actualizar fila en la vista ===
    function actualizarFila(fila, campo, valor) {
        const id = fila.dataset.id;
        const modulo = modulos.find(m => m.id_modulo == id);
        if (modulo) modulo[campo] = valor;

        if (campo === "icono_svg") {
            const celdaSVG = fila.querySelector(".svg-preview");
            celdaSVG.innerHTML = valor || "-";
        }

        fila.style.backgroundColor = "#d4edda";
        setTimeout(() => fila.style.backgroundColor = "", 700);
    }

    // === Enviar cambio al backend ===
    async function actualizarCampo(id, campo, valor) {
        try {
            const formData = new FormData();
            formData.append("id_modulo", id);
            formData.append("campo", campo);
            formData.append("valor", valor);

            const res = await fetch("../../backend/controller/admin/modulos/modulos_editar.php", {
                method: "POST",
                body: formData
            });

            const data = await res.json();
            return data.success;
        } catch (err) {
            console.error("Error al guardar campo:", err);
            return false;
        }
    }

    // === Toast para avisos cortos ===
    function mostrarToast(mensaje) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: mensaje,
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true
        });
    }
});




// === Helper: escapar texto para insertar dentro de contenteditable evitando etiquetas no deseadas
function escapeHtml(str) {
    if (str == null) return "";
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}



// === Función para actualizar un campo ===
async function actualizarCampo(id, campo, valor) {
    try {
        const formData = new FormData();
        formData.append("id_modulo", id);
        formData.append("campo", campo);
        formData.append("valor", valor);

        const res = await fetch("../../backend/controller/admin/modulos/modulos_editar.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();
        if (!data.success) {
            alert("Error al actualizar: " + data.message);
        }
    } catch (err) {
        console.error("Error al actualizar campo:", err);
    }
}

// === ABRIR MODAL PARA AGREGAR ===
const modal = document.getElementById("modalModulos");
const modalBody = document.getElementById("modalModulosBody");
const modalConfirm = document.getElementById("modalModulosConfirm");
const modalLabel = document.getElementById("modalModulosLabel");

modal.addEventListener("show.bs.modal", (event) => {
    const button = event.relatedTarget;
    const action = button.getAttribute("data-action");

    if (action === "crear") {
        modalLabel.textContent = "Agregar Nuevo Módulo";
        modalConfirm.textContent = "Guardar";
        modalConfirm.className = "btn btn-primary";
        renderFormularioNuevoModulo();
    }
});

// === Renderizar formulario dentro del modal ===
// === Renderizar formulario dentro del modal ===
async function renderFormularioNuevoModulo() {
    // Mostrar spinner mientras carga
    modalBody.innerHTML = `
        <div class="text-center my-3">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Cargando formulario...</p>
        </div>
    `;

    let modulos = [];
    try {
        const response = await fetch("../../backend/controller/admin/modulos/modulos_listar.php");
        const data = await response.json();
        if (data.success && Array.isArray(data.data)) {
            modulos = data.data;
        }
    } catch (error) {
        console.error("Error al cargar módulos:", error);
    }

    // Reemplazar spinner con el formulario
    modalBody.innerHTML = `
        <form id="formNuevoModulo" novalidate>
            <div class="mb-3">
                <label class="form-label">Nombre del módulo <span class="text-danger">*</span></label>
                <input type="text" name="nombre" class="form-control" required>
                <div class="invalid-feedback">El nombre es obligatorio.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Ruta <span class="text-danger">*</span></label>
                <input type="text" name="ruta" class="form-control" placeholder="ej: calendario.php" required>
                <div class="invalid-feedback">La ruta es obligatoria.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Perfil <span class="text-danger">*</span></label>
                <select name="perfil" class="form-select" required>
                    <option value="">Seleccione un perfil...</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <div class="invalid-feedback">Debe seleccionar un perfil.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado <span class="text-danger">*</span></label>
                <select name="activo" class="form-select" required>
                    <option value="">Seleccione un estado...</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
                <div class="invalid-feedback">Debe seleccionar un estado.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Orden <span class="text-danger">*</span></label>
                <input type="number" name="orden" class="form-control" min="0" step="1" required>
                <div class="invalid-feedback">Debe ingresar un número válido.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Módulo de referencia (opcional)</label>
                <select name="id_modulo_fk" class="form-select">
                    <option value="">- Ninguno -</option>
                    ${modulos.map(m => `<option value="${m.id_modulo}">${m.nombre}</option>`).join("")}
                </select>
            </div>

           

            <div class="mb-3">
                <label class="form-label">Icono SVG (opcional)</label>
                <textarea name="icono_svg" class="form-control" rows="3" placeholder="Pega aquí el SVG"></textarea>
            </div>

            <div class="text-center mb-3">
                <label class="form-label">Vista previa del icono</label>
                <div id="previewSVG"
                     class="border rounded p-2 d-inline-flex align-items-center justify-content-center bg-light"
                     style="width:50px; height:50px; overflow:hidden;">
                    <span class="text-muted small">Sin icono</span>
                </div>
            </div>

            <div id="errorCampos" class="text-danger text-center fw-semibold d-none">
                ⚠️ Complete los campos obligatorios.
            </div>
        </form>
    `;

    const svgInput = modalBody.querySelector('textarea[name="icono_svg"]');
    const svgPreview = modalBody.querySelector('#previewSVG');
    const form = modalBody.querySelector("#formNuevoModulo");
    const errorCampos = modalBody.querySelector("#errorCampos");

    svgInput.addEventListener("input", () => {
        const val = svgInput.value.trim();
        svgPreview.innerHTML = val ? val : `<span class="text-muted small">Sin icono</span>`;
    });

    modalConfirm.onclick = async (e) => {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            form.classList.add("was-validated");
            errorCampos.classList.remove("d-none");
            return;
        }

        errorCampos.classList.add("d-none");

        // 🔹 Confirmar antes de guardar
        const confirm = await Swal.fire({
            title: "¿Guardar nuevo módulo?",
            text: "Se agregará un nuevo módulo al sistema.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Guardar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33"
        });

        if (!confirm.isConfirmed) return;

        await guardarNuevoModulo(form);
    };
}
let modulos = [];
function renderTabla_listanueva(modulos, tablaBody) {
    tablaBody.innerHTML = "";

    modulos.forEach(modulo => {
        const fila = document.createElement("tr");
        fila.innerHTML = `
            <td>${modulo.id_modulo}</td>
            <td>${modulo.nombre}</td>
            <td>${modulo.ruta || "-"}</td>
            <td>${modulo.activo}</td>
            <td>${modulo.perfil}</td>
            <td>${modulo.orden}</td>
        `;
        tablaBody.appendChild(fila);
    });
}

function renderTabla(modulos, tablaBody) {
    tablaBody.innerHTML = "";

    modulos.forEach((modulo) => {
        const fila = document.createElement("tr");
        fila.dataset.id = modulo.id_modulo;

        fila.innerHTML = `
                <td>${fila.dataset.id}</td>
                <td contenteditable="true" data-campo="nombre">${modulo.nombre}</td>
                <td contenteditable="true" data-campo="ruta">${modulo.ruta || "-"}</td>
                <td class="svg-preview">${modulo.icono_svg ? modulo.icono_svg : '-'}</td>
                 <td>
                    <textarea class="form-control form-control-sm editable-textarea" rows="3" 
                              data-campo="icono_svg">${modulo.icono_svg || ''}</textarea>
                </td>
                <td>
                    <select class="form-select form-select-sm editable-select" data-campo="activo">
                        <option value="Activo" ${modulo.activo === "Activo" ? "selected" : ""}>Activo</option>
                        <option value="Inactivo" ${modulo.activo !== "Activo" ? "selected" : ""}>Inactivo</option>
                    </select>
                </td>

                <td>
                    <select class="form-select form-select-sm editable-select" data-campo="perfil">
                        <option value="admin" ${modulo.perfil === "admin" ? "selected" : ""}>admin</option>
                        <option value="user" ${modulo.perfil === "user" ? "selected" : ""}>user</option>
                    </select>
                </td>

                <td>
                    <input type="number" min="0" step="1" class="form-control form-control-sm editable-input" 
                           value="${modulo.orden || 0}" data-campo="orden">
                </td>

              

               

                <td>
                    <select class="form-select form-select-sm editable-select" data-campo="id_modulo_fk">
                        <option value="">- Ninguno -</option>
                        ${modulos.map(m => `
                            <option value="${m.id_modulo}" ${modulo.id_modulo_fk == m.id_modulo ? "selected" : ""}>
                                ${m.nombre}
                            </option>`).join('')}
                    </select>
                </td>
            `;

        tablaBody.appendChild(fila);
    });
}
// === Guardar módulo ===
async function guardarNuevoModulo(form) {
    const formData = new FormData(form);
    modalConfirm.disabled = true;
    modalConfirm.textContent = "Guardando...";

    try {
        const res = await fetch("../../backend/controller/admin/modulos/modulos_agregar.php", {
            method: "POST",
            body: formData
        });

        const data = await res.json();

        if (data.success) {
            const nuevoModulo = Object.fromEntries(formData.entries());
            nuevoModulo.id_modulo = data.id_modulo;

            modulos.push(nuevoModulo); // ✅ ahora sí existe


            const modalInstance = bootstrap.Modal.getInstance(modal);
            modalInstance.hide();

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: data.message || "Módulo agregado correctamente",
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            });
            // 👇 Ejecutar algo luego de que el SweetAlert termine
            setTimeout(() => {
               location.reload();
            }, 1900);
        } else {
            Swal.fire({
                icon: "error",
                title: "Error al guardar",
                text: data.message || "No se pudo agregar el módulo."
            });
        }

    } catch (err) {
        console.error("❌ Error en el fetch:", err);
        Swal.fire({
            icon: "error",
            title: "Error inesperado",
            text: err.message || "Error desconocido."
        });
    } finally {
        modalConfirm.disabled = false;
        modalConfirm.textContent = "Guardar";
    }
}



// === Pequeña notificación flotante ===
function mostrarToast(msg) {
    const toast = document.createElement("div");
    toast.className = "position-fixed bottom-0 end-0 m-3 p-3 bg-success text-white rounded shadow";
    toast.style.zIndex = "1055";
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}
