document.addEventListener('DOMContentLoaded', function() {
    const radioPresencial = document.getElementById('radioDefault1');
    const radioVirtual = document.getElementById('radioDefault2');
    const lugarDiv = document.querySelector('.lugar');
    const enlaceDiv = document.querySelector('.enlace');
    const buscarColaboradorInput = document.getElementById('buscar-colaborador');
    const listaUsuariosDiv = document.getElementById('lista-usuarios');
    const listaSeleccionadosUl = document.getElementById('lista-seleccionados');
    const usuariosSeleccionadosDiv = document.getElementById('usuarios-seleccionados');
    const idUsuarioActual = document.getElementById('entidad').value;
    const radioIndividual = document.getElementById('radioDeempleado1');
    const radioEquipo = document.getElementById('radioDeempleado2');
    const radioTodos = document.getElementById('radioDeempleado3');
    const dropdownColabore = document.getElementById('colabore').closest('.dropdown'); // Obtener el elemento dropdown
    const selectColabore = document.querySelector('.selecionarE'); // Obtener el elemento select


    loadcapacitaciones();
  
    
    let seleccionados = [];
    let filtroActivo = ''; // Inicializar sin valor por defecto
    let listaEquiposCache = []; // Almacena la lista completa de equipos para el filtrado

    function toggleLugarVisibility() {
        if (radioVirtual.checked) {
            lugarDiv.style.display = 'none';
            enlaceDiv.style.display = 'block';
        } else if (radioPresencial.checked) {
            lugarDiv.style.display = 'block';
            enlaceDiv.style.display = 'none';
        }else {
            lugarDiv.style.display = 'none';
            enlaceDiv.style.display = 'none'; 
        }
    }

    function cargarUsuarios(searchTerm = '') {
        fetch(`../../backend/controller/usuario/capacitacion/buscar_colaboradores.php?search=${searchTerm}&idUser=${idUsuarioActual}`)
            .then(response => response.json())
            .then(usuarios => {
                listaUsuariosDiv.innerHTML = '';
                usuarios.forEach(usuario => {
                    const div = crearElementoSugerencia(usuario.id_empleado, `${usuario.nombre} ${usuario.apellido}`, 'empleado');
                    listaUsuariosDiv.appendChild(div);
                });
                actualizarListaSeleccionados();
                actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

            })
            .catch(error => console.error('Error al cargar usuarios:', error));
    }

    function cargarEquipos(searchTerm = '') {
        let url = '../../backend/controller/usuario/capacitacion/buscar_equipos.php';
        if (searchTerm) {
            url += `?search=${searchTerm}`;
        }
        fetch(url)
            .then(response => response.json())
            .then(equipos => {
                listaEquiposCache = equipos; // Almacenar la lista completa de equipos
                mostrarEquiposFiltrados(searchTerm);
                actualizarListaSeleccionados();
                actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

            })
            .catch(error => console.error('Error al cargar equipos:', error));
    }

   
    function cargarTodos() {
        fetch('../../backend/controller/usuario/capacitacion/buscar_equipos.php')
            .then(response => response.json())
            .then(equipos => {
                if (equipos) {
                    equipos.forEach(equipo => {
                        const yaSeleccionado = seleccionados.some(item => item.id === equipo.id_equipo && item.tipo === 'equipo');
                        if (!yaSeleccionado) {
                            seleccionados.push({
                                id: equipo.id_equipo,
                                nombre: equipo.alias,
                                tipo: 'equipo'
                            });
                        }
                    });
                    actualizarListaSeleccionados();
                    listaUsuariosDiv.innerHTML = ''; // Limpiar las sugerencias
                    actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

                }
            })
            .catch(error => console.error('Error al cargar todos los equipos:', error));
    }







    function actualizarPlaceholderSelect() {
        // Verificar si ya existe el placeholder para evitar duplicados
        const placeholderOptionExists = selectColabore.querySelector('option[value=""][disabled][selected]');
        if (!placeholderOptionExists) {
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.disabled = true;
            placeholderOption.selected = true;
            placeholderOption.textContent = 'Seleccionar Empleados o Equipos';
            selectColabore.appendChild(placeholderOption);
        } else if (selectColabore.selectedIndex !== 0) {
            // Si hay elementos seleccionados, deseleccionar el placeholder al cambiar de filtro
            selectColabore.selectedIndex = -1; // Deseleccionar cualquier opción activa
        }
    }


   

    toggleLugarVisibility();

    radioPresencial.addEventListener('change', toggleLugarVisibility);
    radioVirtual.addEventListener('change', toggleLugarVisibility);




    // Event listeners para los radio buttons de selección de colaboradores
    radioIndividual.addEventListener('change', function() {
        buscarColaboradorInput.disabled = false;
        filtroActivo = 'individual';
        cargarUsuarios();
        buscarColaboradorInput.placeholder = 'Buscar Colaborador';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    });

    radioEquipo.addEventListener('change', function() {
        buscarColaboradorInput.disabled = false;
        filtroActivo = 'equipo';
        cargarEquipos();
        buscarColaboradorInput.placeholder = 'Buscar Equipo';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    });

    radioTodos.addEventListener('change', function() {
        filtroActivo = 'todos';
        cargarTodos();
        buscarColaboradorInput.disabled = true;
        buscarColaboradorInput.placeholder = 'Todos seleccionados';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    });



    // Carga inicial y establecimiento de filtroActivo
    if (radioIndividual.checked) {
        buscarColaboradorInput.disabled = false;

        filtroActivo = 'individual';
        cargarUsuarios();
        buscarColaboradorInput.placeholder = 'Buscar Colaborador';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    } else if (radioEquipo.checked) {
        buscarColaboradorInput.disabled = false;

        filtroActivo = 'equipo';
        cargarEquipos();
        buscarColaboradorInput.placeholder = 'Buscar Equipo';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    } else if (radioTodos.checked) {
        filtroActivo = 'todos';
        cargarTodos();
        buscarColaboradorInput.disabled = true;
        buscarColaboradorInput.placeholder = 'Todos seleccionados';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    } else {
        buscarColaboradorInput.disabled = true;
        buscarColaboradorInput.placeholder = 'buscar';
        actualizarPlaceholderSelect(); // Asegurar que el placeholder esté presente

    }

 
});


document.addEventListener('DOMContentLoaded', function() {
    // Función para obtener un parámetro GET por su nombre
    function obtenerParametroGet(nombre) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(nombre);
    }

    // Verificar si las variables existen en los parámetros GET
    const otraVariable = obtenerParametroGet('mi_variable');
    const otraVariable2 = obtenerParametroGet('mi_variable2'); // Obtener otraVariable2

    const elementoPrincipal = document.querySelector('.principal');
    const elementoCrearCapacitacion = document.querySelector('.crearCapacitacion');
    const elementoMiCapacitacion = document.querySelector('.miCapacitacion'); // Obtener elementoMiCapacitacion

    // Nueva condición: Si otraVariable2 es distinto de null
    if (otraVariable2 !== null) {
        // Ocultar elementoPrincipal y elementoCrearCapacitacion, mostrar elementoMiCapacitacion
        if (elementoPrincipal) {
            elementoPrincipal.style.display = 'none';
        }
        if (elementoCrearCapacitacion) {
            elementoCrearCapacitacion.style.display = 'none';
        }
        if (elementoMiCapacitacion) {
            elementoMiCapacitacion.style.display = 'block';
        }
    } else if (otraVariable !== null) { // Condición existente: Si otraVariable es distinto de null
        // Si 'otra_variable' existe, ocultar 'principal' y mostrar 'crearCapacitacion'
        if (elementoPrincipal) {
            elementoPrincipal.style.display = 'none';
        }
        if (elementoCrearCapacitacion) {
            elementoCrearCapacitacion.style.display = 'block';
        }
        // Asegurar que elementoMiCapacitacion esté oculto si solo otraVariable está presente
        if (elementoMiCapacitacion) {
             elementoMiCapacitacion.style.display = 'none';
        }
    } else { // Condición existente: Si ninguna de las variables existe
        // Si ninguna variable existe, asegurar que 'principal' esté visible y las otras ocultas (por defecto)
        if (elementoPrincipal) {
            elementoPrincipal.style.display = 'block';
        }
        if (elementoCrearCapacitacion) {
            elementoCrearCapacitacion.style.display = 'none';
        }
         if (elementoMiCapacitacion) {
             elementoMiCapacitacion.style.display = 'none';
         }
    }
});






//document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('formFile');
    const archivosSeleccionadosDiv = document.createElement('div');
    archivosSeleccionadosDiv.classList.add('mt-2', 'd-flex', 'flex-wrap', 'gap-2', 'archivo-itemx'); // Añadimos clases de Flexbox
    archivosSeleccionadosDiv.id = "selcion";
    fileInput.parentNode.insertBefore(archivosSeleccionadosDiv, fileInput.nextSibling);

    fileInput.addEventListener('change', function() {
        archivosSeleccionadosDiv.innerHTML = '';

        if (this.files && this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
                const archivo = this.files[i];
                const archivoWrapper = document.createElement('div'); // Contenedor para nombre y botón
                archivoWrapper.classList.add('d-flex', 'align-items-center', 'border', 'rounded', 'p-2'); // Estilos para cada archivo

                const nombreArchivoSpan = document.createElement('span');
                nombreArchivoSpan.textContent = archivo.name;
                nombreArchivoSpan.classList.add('me-2');
                nombreArchivoSpan.style.maxWidth = '150px'; // Ajusta este valor según necesites
                nombreArchivoSpan.style.overflow = 'hidden';
                nombreArchivoSpan.style.textOverflow = 'ellipsis';

                const botonEliminar = document.createElement('button');
                botonEliminar.type = 'button';
                botonEliminar.classList.add('btn', 'btn-sm', 'btn-outline-danger', 'border-0', 'ms-auto');
                botonEliminar.innerHTML = '<i class="bi bi-x-lg"></i>';
                botonEliminar.dataset.index = i;

                botonEliminar.addEventListener('click', function() {
                    const indexAEliminar = parseInt(this.dataset.index);
                    const archivosActuales = Array.from(fileInput.files);
                    archivosActuales.splice(indexAEliminar, 1);

                    const dt = new DataTransfer();
                    archivosActuales.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;

                    archivosSeleccionadosDiv.innerHTML = '';
                    if (fileInput.files.length > 0) {
                        Array.from(fileInput.files).forEach((nuevoArchivo, nuevoIndex) => {
                            const nuevoArchivoWrapper = document.createElement('div');
                            nuevoArchivoWrapper.classList.add('d-flex', 'align-items-center', 'border', 'rounded', 'p-2');

                            const nuevoNombreArchivoSpan = document.createElement('span');
                            nuevoNombreArchivoSpan.textContent = nuevoArchivo.name;
                            nuevoNombreArchivoSpan.classList.add('me-2');
                            nuevoNombreArchivoSpan.style.maxWidth = '150px';
                            nuevoNombreArchivoSpan.style.overflow = 'hidden';
                            nuevoNombreArchivoSpan.style.textOverflow = 'ellipsis';

                            const nuevoBotonEliminar = document.createElement('button');
                            nuevoBotonEliminar.type = 'button';
                            nuevoBotonEliminar.classList.add('btn', 'btn-sm', 'btn-outline-danger', 'border-0', 'ms-auto');
                            nuevoBotonEliminar.innerHTML = '<i class="bi bi-x-lg"></i>';
                            nuevoBotonEliminar.dataset.index = nuevoIndex;
                            nuevoBotonEliminar.addEventListener('click', arguments.callee);

                            nuevoArchivoWrapper.appendChild(nuevoNombreArchivoSpan);
                            nuevoArchivoWrapper.appendChild(nuevoBotonEliminar);
                            archivosSeleccionadosDiv.appendChild(nuevoArchivoWrapper);
                        });
                    }
                });

                archivoWrapper.appendChild(nombreArchivoSpan);
                archivoWrapper.appendChild(botonEliminar);
                archivosSeleccionadosDiv.appendChild(archivoWrapper);
            }
        }
    });
//});


const form = document.getElementById("noteForm");
const notesContainer = document.getElementById("notesContainer");
const listaSeleccionados = document.getElementById('lista-seleccionados'); // Obtén la lista de seleccionados


// Obtener referencias al botón y al spinner
const guardarBtn = document.getElementById('guardarBtn');
const spinnerGuardar = document.getElementById('spinnerGuardar');


let allTeamsFetched = false; // Bandera para evitar múltiples fetch de todos los equipos




form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const colaboradoresIds = [];
    let equiposIds = [];
    const selectedItemsElements = form.querySelectorAll('#selectedItemsList .selected-item');

    let aplicarATodos = false;
    selectedItemsElements.forEach(itemElement => {
        const id = itemElement.dataset.id;
        const tipo = itemElement.dataset.tipo;
        if (tipo === 'individual') {
            colaboradoresIds.push(id);
        } else if (tipo === 'equipo') {
            equiposIds.push(id);
        } else if (tipo === 'todos') {
            aplicarATodos = true;
        }
    });

    // Verificar si no se ha seleccionado ningún colaborador, equipo o "Aplicar a todos"
    if (colaboradoresIds.length === 0 && equiposIds.length === 0 && !aplicarATodos) {
        Swal.fire({
            title: "Advertencia",
            text: "Debe agregar al menos un empleado, un equipo o seleccionar 'Aplicar a todos'.",
            icon: "warning"
        });
        return; // Detener el envío del formulario
    }

      // --- Mostrar spinner e inhabilitar botón ---
      guardarBtn.disabled = true;
      spinnerGuardar.classList.remove('d-none'); // Muestra el spinner

    const formData = new FormData();
    formData.append("fechaInicio", document.getElementById("fechaHora").value);
    formData.append("fechaFin", document.getElementById("fechaHorafin").value);
    formData.append("link", document.getElementById("enlace").value);
    formData.append("lugar", document.getElementById("lugar").value);
        formData.append("obligacion", document.getElementById("obligacion").value);


    const archivos = document.getElementById("formFile").files;
    for (let i = 0; i < archivos.length; i++) {
        formData.append("materiales[]", archivos[i]);
    }

    formData.append("temas", document.getElementById("tema").value);
    formData.append("requerimientos", document.getElementById("requerimientos").value);
    formData.append("usuarioActual", document.getElementById("entidad").value);

    const modalidadRadios = document.querySelectorAll('input[name="radioDefault"]');
    let modalidadSeleccionada = '';
    modalidadRadios.forEach(radio => {
        if (radio.checked) {
            const label = document.querySelector(`label[for="${radio.id}"]`);
            if (label) {
                modalidadSeleccionada = label.textContent.trim();
            }
        }
    });
    formData.append("modalidad", modalidadSeleccionada);

    if (aplicarATodos) {
        // Obtener todos los equipos si se seleccionó "Todos" y aún no se han fetched
        if (!allTeamsFetched) {
            const allTeams = await fetchTeams();
            equiposIds = allTeams.map(team => team.id_equipo);
            allTeamsFetched = true; // Marcar que ya se obtuvieron todos los equipos
        }
        colaboradoresIds.length = 0; // Asegurarse de que no haya colaboradores individuales
    }

    formData.append("colaboradores", JSON.stringify(colaboradoresIds));
    formData.append("equipos", JSON.stringify(equiposIds));

    console.log("Datos enviados:", {
        fechaInicio: formData.get("fechaInicio"),
        fechaFin: formData.get("fechaFin"),
        link: formData.get("link"),
        edificio: formData.get("lugar"),
        obligacion: formData.get("obligacion"),

        materiales: Array.from(archivos).map(file => file.name),
        tema: formData.get("temas"),
        requerimientos: formData.get("requerimientos"),
        usuario: formData.get("usuarioActual"),
        colaboradores: colaboradoresIds,
        equipos: equiposIds,
        modalidad: modalidadSeleccionada,
        aplicarATodos: aplicarATodos
    });

    const response = await fetch("../../backend/controller/usuario/capacitacion/guardar_capacitacion.php", {
        method: "POST",
        body: formData,
    });

    const result = await response.json();

    if (result.success) {
        Swal.fire({
            title: "Exito",
            text: "Capacitacion Guardada exitosamente.",
            icon: "success"
        }).then(() => {


            form.reset();
            const selectedItemsList = document.getElementById('selectedItemsList');
            selectedItemsList.innerHTML = '';
            const elementosArchivo = document.getElementsByClassName('archivo-itemx');
            Array.from(elementosArchivo).forEach(elemento => {
                elemento.innerHTML = '';
            });

            const elementosLugar = document.getElementsByClassName('lugar');
            Array.from(elementosLugar).forEach(elementox => {
                elementox.style.display = 'none';
            });

            const elementosEnlace = document.getElementsByClassName('enlace');

            Array.from(elementosEnlace).forEach(elementom => {
                elementom.style.display = 'none';
            });

            allTeamsFetched = false; // Resetear la bandera al limpiar el formulario

            loadcapacitaciones();
        });


   // --- Ocultar spinner y habilitar botón ---
   guardarBtn.disabled = false;
   spinnerGuardar.classList.add('d-none'); // Oculta el spinner


    } else {
        //alert(result.error || "Error al guardar la nota.");
    }
});

async function loadcapacitaciones() {
    const modalElement1 = document.getElementById(`actual-capac`);

    const idUserx = document.getElementById('entidad').value;
    const now = new Date();

    const response = await fetch(`../../backend/controller/usuario/capacitacion/conseguir_capacitacion.php?idUserx=${encodeURIComponent(idUserx)}`);
    const capacitacion = await response.json();
    const notesContainer = document.getElementById('notesContainer');
    notesContainer.innerHTML = "";

    capacitacion.forEach((note) => {
        const idCapacitacion = note["id_capacitacion"];
        modalElement1.dataset.id = idCapacitacion;
        modalElement1.name = `actual-capac-${idCapacitacion}`;

        const fechaInicioOriginal = note["fecha-inicio"];
        let formattedDateInicio = "Ninguno";
        let formattedHoraInicio = "";
        if (fechaInicioOriginal) {
            const dateInicio = new Date(fechaInicioOriginal);
            const dia = dateInicio.getDate().toString().padStart(2, '0');
            const mes = (dateInicio.getMonth() + 1).toString().padStart(2, '0');
            const anio = dateInicio.getFullYear();
            const hora = dateInicio.getHours().toString().padStart(2, '0');
            const minuto = dateInicio.getMinutes().toString().padStart(2, '0');
            formattedDateInicio = `${dia}/${mes}`;
            formattedHoraInicio = `${hora}:${minuto}`;
        }
        // formattedDateFin no se usa, se calcula directamente en el if
        const fechaInicioCapacitacion = new Date(note["fecha-inicio"]);
        const fechaFinCapacitacion = new Date(note["fecha-fin"]);

        let estadoHTML = '';
        let deleteButtonDisplay = ''; // Variable para controlar la visibilidad del botón borrar

        if (now > fechaFinCapacitacion) {
            estadoHTML = `<span class="badge bg-primary-soft ms-2" type="button" id="filt_Todos">Cerrado</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Cerrado');
        } else if (now >= fechaInicioCapacitacion && now < fechaFinCapacitacion) {
            estadoHTML = `<span class="badge bg-warning-soft ms-2" type="button" id="filt_EnCurso">Proceso</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Proceso');
            deleteButtonDisplay = 'style="display: none;"'; // Ocultar el botón si está en Proceso

        } else {
            estadoHTML = `<span class="badge bg-success-soft ms-2" type="button" id="filt_Habilitado">Espera</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Espera');
        }

        const noteElement = document.createElement("div");
        noteElement.classList.add("col-lg-6", "col-md-6", "col-sm-6", "col-8", "g-4");

        noteElement.innerHTML = `
        <div class="card car-${note.id_capacitacion}"  data-id="${note.id_capacitacion}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center my-2">
                    <div>
                       <span class="text-secondary "   data-id="${note.id_capacitacion}">${formattedDateInicio} ${formattedHoraInicio}</span>
                    </div>
                    <div class="text-end">
                        ${estadoHTML}
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary w-50 "   data-id="${note.id_capacitacion}" style="word-wrap: break-word;">${note.temas}</span>
                </div>
               <div class="text-start my-2 toggle-content" style="display:none">
    <span class="text-secondary " c data-id="${note.id_capacitacion}">${note.requerimientos}</span>
</div>
                <div class="d-flex justify-content-between align-items-center my-2">
                </div>
                <div class="text-start my-2  toggle-content" style="display:none" >
                    ${note.modalidad === 'Presencial'
                        ? `<span class="text-secondary" data-id="${note.id_capacitacion}">${note.lugar}</span>`
                        : (note.modalidad === 'Virtual'
                            ? `<a href="${note.link}" target="_blank" class=" link-secondary" data-id="${note.id_capacitacion}">Link</a>`
                            : `<span class="text-secondary" data-id="${note.id_capacitacion}"></span>`
                        )
                    }
                </div>

                <div class="text-start my-2  toggle-content" style="display:none" >

                <span class="badge bg-info-soft " type="button" data-id="${note.id_capacitacion}" id="obligac">${note.obligacion}</span>
                </div>

                <div class="d-flex flex-wrap" id="collabo-${note.id_capacitacion}" >
                </div>
    
                <div class="d-flex flex-wrap mt-2">
                </div>
    
                <div class="d-flex justify-content-between mt-3">
                                                             <span class="badge text-bg-secondary text-center pt-2">${note.modalidad}</span>

    
                    <div class="dropdown ">
                        <a class="btn border-0 btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-haspopup="true" aria-expanded="false" >
                            <i class="bi bi-three-dots-vertical"></i>
                        </a>
                        <ul class="dropdown-menu p-2">
                            <li><button class="dropdown-item add-tag-btn" data-id="${note.id_capacitacion}">Editar</button></li>
                            <li><button class="dropdown-item add-certificate-btn" data-id="${note.id_capacitacion}">Certificaciones</button></li>
                            <li class="elimionir" ${deleteButtonDisplay}><button class="dropdown-item delete-btn" data-id="${note.id_capacitacion}">Borrar</button></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;




  // --- *** Añadir Event Listener a la Card *** ---
  const cardElement = noteElement.querySelector(".card");
  cardElement.addEventListener("click", () => {
      // Seleccionar todos los elementos con la clase 'toggle-content' dentro de esta card
      const hiddenElements = cardElement.querySelectorAll(".toggle-content");

      hiddenElements.forEach(element => {
          // Alternar el estilo display
          if (element.style.display === 'none') {
              element.style.display = 'block'; // O flex, dependiendo del contenedor
          } else {
              element.style.display = 'none';
          }
      });
  });
  // --- *** Fin del Event Listener *** ---



    // --- *** Añadir Event Listener para detener la propagación en el Dropdown *** ---
    const dropdownElement = noteElement.querySelector(".dropdown"); // Selecciona el div del dropdown
    if (dropdownElement) { // Asegúrate de que el dropdown exista
        dropdownElement.addEventListener("click", (event) => {
            event.stopPropagation(); // Detiene el evento click para que no llegue a la card
        });
    }
    // --- *** Fin del Event Listener del Dropdown *** ---


        // Solo agregar el event listener si el botón no está oculto
        if (deleteButtonDisplay === '') {
             noteElement.querySelector(".delete-btn").addEventListener("click", async () => {
                 await deletecapacitacion(note.id_capacitacion);
                 loadcapacitaciones();
             });
        }


        noteElement.querySelector(".add-tag-btn").addEventListener("click", (event) => {
            const editButton = event.target; // Get the clicked button
            editButton.disabled = true; // Disable the button
        
            const spinnerHTML = `
                <div class="spinner-border text-primary" role="status" id="spinner-${note.id_capacitacion}">
                    <span class="sr-only"></span>
                </div>
            `;
            // Insert the spinner after the button
            editButton.insertAdjacentHTML('afterend', spinnerHTML);
        
            // Call the function that opens the modal
            openAddCapacitacionModal(note.id_capacitacion);
        
            const spinner = document.getElementById(`spinner-${note.id_capacitacion}`);
            if (spinner) {
                spinner.remove(); // Remove the spinner
            }
            // Wrap the re-enable and remove spinner logic in a setTimeout
            setTimeout(() => {
                // Re-enable button and remove spinner after 1000ms
                editButton.disabled = false; // Re-enable the button
              
            }, 2500); // Delay of 1000 milliseconds (1 second)
        });

        noteElement.querySelector(".add-certificate-btn").addEventListener("click", (event) => {
            const certificateButton = event.target; // Get the clicked button
            certificateButton.disabled = true; // Disable the button
        
            // Create a unique ID for the spinner related to the certification button
            const spinnerId = `spinner-certificate-${note.id_capacitacion}`;
            const spinnerHTML = `
                <div class="spinner-border text-primary" role="status" id="${spinnerId}">
                    <span class="sr-only"></span>
                </div>
            `;
            // Insert the spinner after the button
            certificateButton.insertAdjacentHTML('afterend', spinnerHTML);
        
            // Call the function that opens the modal for certifications
            openCertificacionesModal(note.id_capacitacion);
        

            const spinner = document.getElementById(spinnerId);
            if (spinner) {
                spinner.remove(); // Remove the spinner
            }
            // Re-enable button and remove spinner after a 1000ms delay
            // This delay is fixed and doesn't wait for the modal to close
            // or for any asynchronous operations within openCertificacionesModal to complete.
            setTimeout(() => {
                certificateButton.disabled = false; // Re-enable the button
              
            }, 2500); // Delay of 1000 milliseconds
        });

        const collaboContainer = noteElement.querySelector(`#collabo-${note.id_capacitacion}`);
        // const materialesContainer = noteElement.querySelector(`#materiales-${note.id_capacitacion}`); // Esta variable no se usa

        note.colaboradores.forEach(colaborador => {
            if (colaborador.equipo_alias !== null) {
                const initials = colaborador.equipo_alias.substring(0, 2).toUpperCase();
                const badgeHTML = `<span style="display:none" class="badge bg-secondary-soft me-1 toggle-content" data-bs-toggle="tooltip" title="${colaborador.equipo_alias}">${initials}</span>`;
                collaboContainer.innerHTML += badgeHTML;
            } else if (colaborador.empleado_nombre !== null) {
                const fullName = `${colaborador.empleado_nombre} ${colaborador.empleado_apellido}`;
                const initials = `${colaborador.empleado_nombre.charAt(0)}${colaborador.empleado_apellido.charAt(0)}`.toUpperCase();
                const badgeHTML = `<span style="display:none" class="badge bg-secondary-soft me-1 toggle-content" data-bs-toggle="tooltip" title="${fullName}">${initials}</span>`;
                collaboContainer.innerHTML += badgeHTML;
            }
        });

        /* Bloque de materiales comentado en el original
        note.materiales.forEach((material, index) => {
            const previewLink = `../../uploads/capacitacion/${material.nombre_material}`;
            const downloadLink = `../../uploads/capacitacion/${material.nombre_material}`;
            const materialNumber = index + 1;
            const materialLinkHTML = `
                <p>
                    <a href="${previewLink}" target="_blank" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Ver Material-${materialNumber}">
                        <i class="bi bi-eye-fill me-1"></i> Ver
                    </a>
                    <a href="${downloadLink}" target="_blank" download="${material.nombre_material}" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Descargar Material-${materialNumber}">
                        <i class="bi bi-download-fill me-1"></i> Descargar
                    </a>
                </p>
            `;
            materialesContainer.innerHTML += materialLinkHTML;
        });*/

        notesContainer.appendChild(noteElement);
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

async function actualizarEstadoCapacitacion(idCapacitacion, nuevoEstado) {
    const formData = new FormData();
    formData.append('idCapacitacion', idCapacitacion);
    formData.append('estado', nuevoEstado);

    const response = await fetch('../../backend/controller/usuario/capacitacion/actualizar_estado_capacitacion.php', {
        method: 'POST',
        body: formData
    });

    const result = await response.json();
    if (!result.success) {
        console.error(`Error al actualizar el estado de la capacitación ${idCapacitacion}:`, result.error);
    }
}

loadcapacitaciones();








 // Eliminar nota
 async function deletecapacitacion(id) {
    const response = await fetch("../../backend/controller/usuario/capacitacion/borrar_capacitacion.php", {
        method: "POST",
        body: JSON.stringify({ id }),
        headers: { "Content-Type": "application/json" },
    });

    const result = await response.json();

    if (!result.success) {
        alert("Error al eliminar la nota.");
    }
}


function cerrar() {
    const modalElement = document.getElementById("actual-capac");
    if (modalElement) {
        const modal = bootstrap.Modal.getInstance(modalElement);
        if (modal) {
            modal.hide();
        }
    }
}


function abrirModal() {
    const modalElement = document.getElementById("actual-capac");
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}



async function openAddCapacitacionModal(capacitacionId) {

   
    const modalContainer = document.getElementById('actual'); // Contenedor donde se insertará el modal
    const modalId = `actual-capac-${capacitacionId}`; // ID único para este modal

    // *** Simplificar y forzar la eliminación de cualquier modal existente con este ID ***
    const existingModalElement = document.getElementById(modalId);
    if (existingModalElement) {
       console.log(`[DEBUG] Forzando eliminación de modal existente con ID: ${modalId}`);
       // Eliminar directamente. Confiamos en que el nuevo se creará inmediatamente.
       existingModalElement.remove();
    }
    // *** Fin Simplificación ***


    // Obtener los datos de la capacitación
    const response = await fetch(`../../backend/controller/usuario/capacitacion/conseguir_capacitacion_por_id.php?id=${capacitacionId}`);
    // Verificar si la respuesta es OK antes de intentar parsear JSON
    if (!response.ok) {
        console.error(`Error al obtener datos de la capacitación ${capacitacionId}: ${response.status} ${response.statusText}`);
        mostrarAlerta('error', 'Error', 'No se pudieron cargar los datos de la capacitación.');
        return; // Salir si la petición falló
    }
    const capacitacionData = await response.json();


    if (capacitacionData && capacitacionData.length > 0) {
        const note = capacitacionData[0];
        const noteCapacitacionId = note.id_capacitacion;


        // Generar el HTML completo del modal de forma dinámica
        // Eliminamos data-bs-dismiss="modal", añadimos ID al botón 'Cerrar'
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="actualCapacLabel-${noteCapacitacionId}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="actualCapacLabel-${noteCapacitacionId}">Actualizar Capacitación</h5>
                            <button type="button" class="btn-close" aria-label="Close"></button> 
                        </div>
                        <div class="modal-body">
                            <form action="" id="noteForm-${noteCapacitacionId}">
                                <label for="labelFecha-${noteCapacitacionId}" class="form-label fs-3">Editar Capacitación</label>
                                <br>
                                <label for="fechaHora-${noteCapacitacionId}" class="form-label fs-5">Fecha y hora inicio-fin</label>
                                  <div class="mb-3 row g-2 align-items-center">
 <div class="col-md-4"> 
 <label for="fechaInicio-${noteCapacitacionId}" class="form-label">Inicio</label>
 <input type="datetime-local" id="fechaInicio-${noteCapacitacionId}" name="fechaInicio" class="form-control border-0" value="${note["fecha-inicio"] ? note["fecha-inicio"].slice(0, 16) : ''}">
 </div>
 <div class="col-md-4"> 
 <label for="fechaFin-${noteCapacitacionId}" class="form-label">Fin</label>
 <input type="datetime-local" id="fechaFin-${noteCapacitacionId}" name="fechaFin" class="form-control border-0" value="${note["fecha-fin"] ? note["fecha-fin"].slice(0, 16) : ''}">
 </div>


 <div class="col-md-4"> 
 <b class="form-label">Modalidad</b>
                                    <div style="align-items: center;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="modalidad" id="presencial-${noteCapacitacionId}" value="Presencial" ${note.modalidad === 'Presencial' ? 'checked' : ''}>
                                            <label class="form-check-label" for="presencial-${noteCapacitacionId}">Presencial</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="modalidad" id="virtual-${noteCapacitacionId}" value="Virtual" ${note.modalidad === 'Virtual' ? 'checked' : ''}>
                                            <label class="form-check-label" for="virtual-${noteCapacitacionId}">Virtual</label>
                                        </div>
                                    </div>

 </div>
                                </div> 
                                <div>
                                   
                                    <br>
                                    <div class="mb-3 enlace" style="display:${note.modalidad === 'Virtual' ? 'block' : 'none'};">
                                        <label for="enlace-${noteCapacitacionId}" class="form-label">Enlace</label>
                                        <input type="url" class="form-control" id="enlace-${noteCapacitacionId}" name="enlace" placeholder="Ingresa la URL aquí" value="${note.link || ''}">
                                        <div class="form-text">Por favor, introduce la dirección web (URL).</div>
                                    </div>
                                    <div class="mb-3 lugar" style="display:${note.modalidad === 'Presencial' ? 'block' : 'none'};">
                                        <label for="lugar-${noteCapacitacionId}" class="form-label">Lugar</label>
                                        <select class="form-select" id="lugar-${noteCapacitacionId}" name="lugar"> <option value="${note.lugar || ''}" selected>${note.lugar || ''}</option> ${note.edificio.map((edificio, index) => {    return `<option value="${edificio.direccion}">${edificio.direccion}</option>
                                        `;    }).join('')}</select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="formFile-${noteCapacitacionId}" class="form-label">Materiales</label>
                                        <div id="materiales-actuales-${note.id_capacitacion}">
                                            ${note.materiales.map((material, index) => {
                                                const fullPublicFilePath = `${window.location.origin}/sistemaInstitucional/uploads/capacitacion/${material.nombre_material}`;
  const googleViewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(fullPublicFilePath)}&embedded=true`;
  const previewLinkBase = `../../uploads/capacitacion/${material.nombre_material}`;
  const downloadLink = `../../uploads/capacitacion/${material.nombre_material}`;
  const materialNumber = index + 1;

  // Obtener la extensión del nombre del archivo
  const fileExtension = material.nombre_material.split('.').pop().toLowerCase();

  // Array de extensiones de imagen comunes
  const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

  // Determinar la URL de vista previa condicionalmente
  let previewLink = previewLinkBase;
  if (!imageExtensions.includes(fileExtension) && window.location.origin !== 'http://localhost') {
    previewLink = googleViewerUrl;
  }
                                                return `
                                                    <div class="d-flex align-items-center justify-content-between material-item-${material.id_material}">

                                                   <div>
                                                    <p class="m-0">
                                                        <a href="${previewLink}" target="_blank" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Ver Material-${materialNumber}">
                                                            <i class="bi bi-eye-fill me-1" style="color:grey;"></i> 
                                                        </a>
                                                        <a href="${downloadLink}" target="_blank" download="${material.nombre_material}" class="link-secondary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Descargar Material-${materialNumber}">
                                                            <i class="bi bi-download-fill me-1"></i> Descargar Material-${materialNumber} 
                                                        </a>
                                                    </p>
                                                    </div>
                                                     <button type="button" class="btn btn-danger btn-sm eliminar-material mb-1" data-id="${material.id_material}" data-capacitacion-id="${note.id_capacitacion}">
                                                            <i class="bi bi-trash-fill"></i> 
                                                        </button>
                                                        </div>
                                                `;
                                            }).join('')}
                                        </div>
                                        <input class="form-control" type="file" id="formFile-${noteCapacitacionId}" name="materiale[]" multiple>
                                    </div>

                                    <div class="mb-3 tema">
                                        <label for="tema-${noteCapacitacionId}" class="form-label">Tema</label>
                                        <input type="text" class="form-control" id="tema-${noteCapacitacionId}" name="temas" placeholder="Escribir Tema" value="${note.temas || ''}">
                                    </div>

                                    <div class="mb-3">
                                        <label for="requerimientos-${noteCapacitacionId}" class="form-label">Requerimientos</label>
                                        <textarea id="requerimientos-${noteCapacitacionId}" class="form-control" name="requerimientos">${note.requerimientos || ''}</textarea>
                                    </div>

                                    <input type="hidden" id="capacitacionIdHidden-${noteCapacitacionId}" value="${note.id_capacitacion}">
                                    <input type="hidden" id="entidadHidden-${noteCapacitacionId}" value="${document.getElementById('entidad').value}">

<select multiple name="drawfs" id="drawfs" class="form-control">
  <option>Gruñón</option>
  <option>Feliz</option>
  <option>Dormilón</option>
  <option>Tímido</option>
  <option>Estornudo</option>
  <option>Tontín</option>
  <option>Doc</option>
</select>
                                    
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div id="usuarios-seleccionados-container-${noteCapacitacionId}" class="mt-2">
                                            <h6>Empleados/equipos seleccionados:</h6>
                                            <ul id="lista-seleccionados-${note.id_capacitacion}" class="list-unstyled">
                                                </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="closeModalBtn-${noteCapacitacionId}" class="btn btn-secondary" >Cerrar</button> 
                            <div class="d-flex align-items-center ms-2"> 
                                <button type="button" id="saveNoteTagBtn-${noteCapacitacionId}" class="btn btn-primary">Guardar</button>
                                
                                <div class="spinner-border text-primary ms-2 d-none" role="status" id="spinnerActualizar-${noteCapacitacionId}">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insertar el HTML completo del modal
        modalContainer.insertAdjacentHTML('beforeend', modalHTML);

        // Obtener la referencia al nuevo modal creado
        const newModalElement = document.getElementById(modalId);
         if (!newModalElement) {
             console.error("Error: No se pudo encontrar el elemento modal recién insertado.");
             mostrarAlerta('error', 'Error interno', 'No se pudo crear la ventana de edición.');
             return;
         }




  // --- *** APLICAR LÓGICA DE VALIDACIÓN DE FECHAS A LOS INPUTS DEL MODAL *** ---

        // 1. Obtener referencias a los inputs de fecha dentro del modal recién creado
        // Usamos newModalElement.querySelector para buscar *dentro* de este modal específico
        const fechaInicioInputModal = newModalElement.querySelector(`#fechaInicio-${noteCapacitacionId}`);
        const fechaFinInputModal = newModalElement.querySelector(`#fechaFin-${noteCapacitacionId}`);

        // Asegurarse de que los elementos existen antes de intentar modificarlos
        if (fechaInicioInputModal && fechaFinInputModal) {
            // 2. Calcular la fecha y hora actual y formatearla
            // Asegúrate de que la fecha/hora actual sea calculada en el momento de abrir el modal
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');

           // const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

            // 3. Establecer la fecha y hora mínima para ambos campos a la actual
            //fechaInicioInputModal.min = minDateTime;
            fechaFinInputModal.min = fechaInicioInputModal.value;

            // 4. Actualizar dinámicamente el mínimo de Fecha Fin basado en Fecha Inicio
            fechaInicioInputModal.addEventListener('input', function() {
                const inicioValue = fechaInicioInputModal.value;

                if (inicioValue) {
                    // Establecer la fecha y hora mínima del campo 'fechaFin'
                    fechaFinInputModal.min = inicioValue;

                    // Opcional: Si la fecha/hora de Fin actual es anterior a la nueva fecha/hora de Inicio,
                    // puedes resetear el campo de Fin o establecerlo igual al de Inicio.
                    // Esto evita que el usuario quede con una selección inválida si reduce la fecha de inicio.
                    if (fechaFinInputModal.value && fechaFinInputModal.value < inicioValue) {
                         fechaFinInputModal.value = inicioValue; // O podrías usar '' para borrarlo
                    }
                } else {
                    // Si se borra la fecha de inicio, el mínimo de Fin vuelve a ser la hora actual.
                    //fechaFinInputModal.min = minDateTime;
                    // Opcional: Si se borra la fecha de inicio, también borrar la fecha de fin.
                    // fechaFinInputModal.value = '';
                }
            });

             // También podrías añadir un listener a fechaFinInputModal si quieres validación en tiempo real mientras se edita Fin,
             // aunque el atributo 'min' ya proporciona validación automática del navegador.
             /*
             fechaFinInputModal.addEventListener('input', function() {
                 const inicioValue = fechaInicioInputModal.value;
                 const finValue = fechaFinInputModal.value;
                 if (inicioValue && finValue && finValue < inicioValue) {
                     // Opcionalmente mostrar un mensaje de error al usuario
                     console.log("La fecha de fin no puede ser anterior a la fecha de inicio.");
                     // El atributo 'min' ya previene la selección inválida, pero aquí podrías añadir UI feedback adicional.
                 }
             });
             */

        } else {
            console.error("Error: No se encontraron los campos de fecha/hora con los IDs esperados en el modal.");
        }

        // --- *** FIN LÓGICA DE VALIDACIÓN DE FECHAS *** ---

         

        // Obtener referencias a los elementos *dentro* del nuevo modal
        const modalBody = newModalElement.querySelector('.modal-body');
        const modalidadRadios = modalBody.querySelectorAll('input[name="modalidad"]');
        const enlaceDiv = modalBody.querySelector('.enlace');
        const lugarDiv = modalBody.querySelector('.lugar');
        const saveNoteTagBtn = newModalElement.querySelector(`#saveNoteTagBtn-${noteCapacitacionId}`);
        const form = newModalElement.querySelector(`#noteForm-${noteCapacitacionId}`);
        const materialesActualesDiv = newModalElement.querySelector(`#materiales-actuales-${note.id_capacitacion}`);





         // Referencia al spinner dentro de este modal específico
         const spinnerActualizar = newModalElement.querySelector(`#spinnerActualizar-${noteCapacitacionId}`);

         // Inicializar Select2 en el nuevo elemento <select>
        // $('.js-example-basic-multiple').select2();
         // Obtener los botones de cierre por su clase/ID
        const closeButton = newModalElement.querySelector(`#closeModalBtn-${noteCapacitacionId}`); // Botón "Cerrar" en el footer
        const headerCloseButton = newModalElement.querySelector('.modal-header .btn-close'); // Botón "X" en el header


        

        // Agregar listeners para la visibilidad de Enlace/Lugar (solo a elementos del nuevo modal)
        modalidadRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                enlaceDiv.style.display = this.value === 'Virtual' ? 'block' : 'none';
                lugarDiv.style.display = this.value === 'Presencial' ? 'block' : 'none';
            });
        });





 // Agregar event listener para el botón "Eliminar" de los materiales
 if (materialesActualesDiv) {
    materialesActualesDiv.addEventListener('click', async function(event) {
        const deleteButton = event.target.closest('.eliminar-material');
        if (deleteButton) {
            const materialId = deleteButton.dataset.id;
            const capacitacionIdToDelete = deleteButton.dataset.capacitacionId;

            console.log('ID Material a eliminar:', materialId);
console.log('ID Capacitación del material:', capacitacionIdToDelete);

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el material de la capacitación.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const deleteResponse = await fetch('../../backend/controller/usuario/capacitacion/eliminar_material.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ idMaterial: materialId, idCapacitacion: capacitacionIdToDelete })
                    });

                    if (deleteResponse.ok) {
                        const deleteResult = await deleteResponse.json();
                        if (deleteResult.success) {
                            mostrarAlerta('success', 'Material eliminado', 'El material se ha eliminado correctamente.');
                            // *** Actualización dinámica de la lista de materiales ***
                            const materialItemToRemove = materialesActualesDiv.querySelector(`.material-item-${materialId}`);
                            if (materialItemToRemove) {
                                materialItemToRemove.remove();
                            } else {
                                console.warn(`No se encontró el elemento del material con ID: ${materialId} para eliminar del DOM.`);
                            }
                        } else {
                            mostrarAlerta('error', 'Error al eliminar', deleteResult.error || 'No se pudo eliminar el material.');
                        }
                    } else {
                        mostrarAlerta('error', 'Error de red', 'Hubo un problema al comunicarse con el servidor.');
                    }
                }
            });
        }
    });
}






         // Agregar event listener para el botón "Guardar" (solo a elementos del nuevo modal)
         if (saveNoteTagBtn) {
             console.log(`[DEBUG] Añadiendo listener click a botón Guardar para modal ID: ${modalId}`); // Log para depuración
             saveNoteTagBtn.addEventListener('click', async function() {
                 console.log(`[DEBUG] Click en botón Guardar para modal ID: ${modalId}. Iniciando guardado...`); // Log para depuración
                 // ... (lógica de guardar - sin cambios aquí) ...


  // --- Mostrar spinner e inhabilitar botón ---
  saveNoteTagBtn.disabled = true;
  spinnerActualizar.classList.remove('d-none'); // Muestra el spinner


                 const selectElement = newModalElement.querySelector('#drawfs');
                 let selectedCollaborators = []; // Array para almacenar objetos { id, type }

                 if (selectElement && selectElement.selectedOptions) {
                     selectedCollaborators = Array.from(selectElement.selectedOptions).map(option => {
                         const id = option.value; // El ID del empleado o equipo
                         const type = option.dataset.type; // El tipo ('employee' o 'team') del atributo data-type

                         // Opcional: Validar que se pudo obtener el tipo
                         if (!type) {
                             console.warn(`[DEBUG] Opción seleccionada sin data-type:`, option);
                             // Puedes decidir saltar esta opción o asignarle un tipo por defecto/desconocido
                             return null; // Salta esta opción si no tiene tipo
                         }

                         return { id: id, type: type }; // Retorna un objeto
                     }).filter(item => item !== null); // Filtra cualquier entrada nula si decidiste saltarlas
                     console.log("[DEBUG] Colaboradores seleccionados (id y tipo):", selectedCollaborators);
                 } else {
                     console.log("[DEBUG] El select 'drawfs' no se encontró o no tiene opciones seleccionadas.");
                 }


                 const fechaInicio = form.elements.fechaInicio.value;
                 const fechaFin = form.elements.fechaFin.value;
                 const modalidad = form.elements.modalidad.value;
                 const enlaceInput = form.elements.enlace;
                 const lugarInput = form.elements.lugar;
                 const enlace = enlaceInput ? enlaceInput.value : '';
                 const lugar = lugarInput ? lugarInput.value : '';
                 const temaInput = form.elements.temas;
                 const tema = temaInput ? temaInput.value : '';
                 const requerimientosInput = form.elements.requerimientos;
                 const requerimientos = requerimientosInput ? requerimientosInput.value : '';
                 const currentCapacitacionIdInput = newModalElement.querySelector(`#capacitacionIdHidden-${noteCapacitacionId}`);
                 const currentEntidadIdInput = newModalElement.querySelector(`#entidadHidden-${noteCapacitacionId}`);
                 const currentCapacitacionId = currentCapacitacionIdInput ? currentCapacitacionIdInput.value : '';
                 const entidadId = currentEntidadIdInput ? currentEntidadIdInput.value : (document.getElementById('entidad') ? document.getElementById('entidad').value : '');
                 const materialesInput = form.elements['materiale[]'];


                 
                 const formData = new FormData();
                 formData.append('idCapacitacion', currentCapacitacionId);
                 formData.append('fechaInicio', fechaInicio);
                 formData.append('fechaFin', fechaFin);
                 formData.append('modalidad', modalidad);
                 if (modalidad === 'Virtual' && enlace) {
                     formData.append('link', enlace);
                 } else if (modalidad === 'Presencial' && lugar) {
                     formData.append('lugar', lugar);
                 }
                 formData.append('temas', tema);
                 formData.append('requerimientos', requerimientos);
                 formData.append('usuarioActual', entidadId);

   // --- MODIFICACIÓN AQUÍ: ENVIAR EL NUEVO ARRAY DE OBJETOS ---
   formData.append('colaboradores', JSON.stringify(selectedCollaborators)); // Envía el array de { id, type } como JSON
   console.log("Datos de colaboradores enviados en formData:", JSON.stringify(selectedCollaborators)); // Verifica lo que se envía
   // --- FIN MODIFICACIÓN ---
                 if (materialesInput && materialesInput.files) {
                     for (let i = 0; i < materialesInput.files.length; i++) {
                         formData.append('materiale[]', materialesInput.files[i]);
                     }
                 }

                 const updateResponse = await fetch('../../backend/controller/usuario/capacitacion/actualizar_capacitacion.php', {
                     method: 'POST',
                     body: formData
                 });

                  if (!updateResponse.ok) {
                     console.error(`Error en la solicitud de actualización: ${updateResponse.status} ${updateResponse.statusText}`);
                     mostrarAlerta('error', 'Error', 'Hubo un problema al guardar los cambios.');
                     return;
                  }

                 const updateResult = await updateResponse.json();

                 if (updateResult.success) {
                     mostrarAlerta('success', 'Capacitacion Actualizada.', '¡Listo!');
                     const modalInstanceToHide = bootstrap.Modal.getInstance(newModalElement);
                     if (modalInstanceToHide) {
                        // modalInstanceToHide.hide();
                        dynamicModal.hide();
                     } else {
                          newModalElement.classList.remove('show');
                          newModalElement.setAttribute('aria-hidden', 'true');
                          newModalElement.style.display = 'none';
                          const backdrop = document.querySelector('.modal-backdrop');
                          if(backdrop) backdrop.remove();
                          newModalElement.remove();
                     }
                     cerrar();
                     loadcapacitaciones();


// --- Ocultar spinner y habilitar botón ---
saveNoteTagBtn.disabled = false;
if(spinnerActualizar) spinnerActualizar.classList.add('d-none'); // Oculta el spinner


                 } else {
                      const errorMessage = updateResult.error || 'Error desconocido al actualizar.';
                      mostrarAlerta('error', 'Error al actualizar la capacitación.', errorMessage);
                      console.error("Error updating capacitacion:", updateResult.error);
                 }
             });
         } else {
              console.error("Error: No se encontró el botón de guardar con ID #saveNoteTagBtn-" + noteCapacitacionId);
         }


         // Agregar un listener para eliminar el modal del DOM cuando se oculte completamente
         newModalElement.addEventListener('hidden.bs.modal', function () {

             console.log(`Modal ${modalId} oculto, eliminando del DOM.`);
             newModalElement.remove(); // Elimina el elemento modal y su backdrop
         });


        // --- ESPERAR A OPERACIONES ASÍNCRONAS ANTES DE MOSTRAR EL MODAL ---

        console.log("Cargando colaboradores...");
        await loadCurrentCollaboratorsForModal(noteCapacitacionId, newModalElement);
        console.log("Colaboradores cargados. Mostrando modal...");


        // --- AHORA SÍ, INICIALIZAR Y MOSTRAR EL MODAL ---

        const dynamicModal = new bootstrap.Modal(newModalElement);

        // Adjuntar listeners de click a los botones de cierre para llamar a hide()
        if(closeButton) {
            console.log(`[DEBUG] Añadiendo listener click a botón Cerrar para modal ID: ${modalId}`); // Log para depuración
            closeButton.addEventListener('click', function() {
                console.log("[DEBUG] Click en botón 'Cerrar', llamando a hide()"); // Log para depuración
                dynamicModal.hide();
            });
        } else {
             console.warn("Botón 'Cerrar' del footer no encontrado.");
        }

         if(headerCloseButton) {
            console.log(`[DEBUG] Añadiendo listener click a botón X para modal ID: ${modalId}`); // Log para depuración
             headerCloseButton.addEventListener('click', function() {
                 console.log("[DEBUG] Click en botón 'X', llamando a hide()"); // Log para depuración
                 dynamicModal.hide();
             });
         } else {
              console.warn("Botón 'X' del header no encontrado.");
         }


        // Mostrar el modal
        dynamicModal.show();


    } else {
        console.error("No se encontraron datos de la capacitación con ID:", capacitacionId);
        mostrarAlerta('warning', 'Capacitación no encontrada.', 'No se pudieron cargar los datos de la capacitación solicitada.');
    }
}

async function loadCurrentCollaboratorsForModal(capacitacionNoteId, modalElement) {
    console.log(`[DEBUG] Intento cargar colaboradores para capacitación NOTE ID: ${capacitacionNoteId}`);
    const collabResponse = await fetch(`../../backend/controller/usuario/capacitacion/conseguir_colaboradores_capacitacion.php?id=${capacitacionNoteId}`);
    if (!collabResponse.ok) {
        console.error(`[DEBUG] Error en respuesta al obtener colaboradores para capacitación ${capacitacionNoteId}: ${collabResponse.status} ${collabResponse.statusText}`);
        mostrarAlerta('error', 'Error', 'No se pudieron cargar los colaboradores.');
        const listaSeleccionados = modalElement.querySelector(`#lista-seleccionados-${capacitacionNoteId}`);
        if(listaSeleccionados) listaSeleccionados.innerHTML = '<li><span class="text-danger">Error al cargar la lista de colaboradores.</span></li>';
        return;
    }

    let collaboratorsData;
    try {
        const responseText = await collabResponse.text();
        console.log(`[DEBUG] Respuesta texto colaboradores para ${capacitacionNoteId}:`, responseText);
        collaboratorsData = JSON.parse(responseText);
        console.log(`[DEBUG] Datos colaboradores parseados para ${capacitacionNoteId}:`, collaboratorsData);

    } catch (e) {
        console.error(`[DEBUG] Error parseando JSON de colaboradores para ${capacitacionNoteId}:`, e);
        mostrarAlerta('error', 'Error de datos', 'Formato de datos de colaboradores inválido.');
        const listaSeleccionados = modalElement.querySelector(`#lista-seleccionados-${capacitacionNoteId}`);
        if(listaSeleccionados) listaSeleccionados.innerHTML = '<li><span class="text-danger">Error en el formato de datos de colaboradores.</span></li>';
        return;
    }

    const listaSeleccionados = modalElement.querySelector(`#lista-seleccionados-${capacitacionNoteId}`);
    if (!listaSeleccionados) {
        console.error(`[DEBUG] Error: No se encontró la lista de seleccionados con ID #lista-seleccionados-${capacitacionNoteId} dentro del modal proporcionado.`);
        return;
    }
    console.log(`[DEBUG] Elemento de lista de colaboradores encontrado:`, listaSeleccionados);

    listaSeleccionados.innerHTML = '';
    console.log(`[DEBUG] Lista de colaboradores limpiada. Cantidad de elementos en los datos:`, collaboratorsData.length);

    let isEmpleado = false;
    let isEquipo = false;

    if (Array.isArray(collaboratorsData) && collaboratorsData.length > 0) {
        isEmpleado = collaboratorsData.some(colaborador => colaborador.empleado_nombre != null);
        isEquipo = collaboratorsData.some(colaborador => colaborador.equipo_alias != null);

        collaboratorsData.forEach((colaborador, index) => {
            console.log(`[DEBUG] Procesando colaborador ${index}:`, colaborador);
            let displayName = '';
            if (colaborador && colaborador.equipo_alias != null) {
                displayName = `<span class="badge bg-info me-1">${colaborador.equipo_alias} (Equipo)</span>`;
            } else if (colaborador && colaborador.empleado_nombre != null) {
                displayName = `<span class="badge bg-success me-1">${colaborador.empleado_nombre} ${colaborador.empleado_apellido} (Empleado)</span>`;
            }
            console.log("[DEBUG] Nombre a mostrar generado:", displayName);

            if (displayName) {
                const listItem = document.createElement('li');
                listItem.innerHTML = displayName;
                listaSeleccionados.appendChild(listItem);
                console.log("[DEBUG] Elemento de lista añadido.");
            } else {
                console.warn(`[DEBUG] El objeto colaborador ${index} no tiene 'equipo_alias' ni 'empleado_nombre' válidos:`, colaborador);
            }
        });
        console.log("[DEBUG] Finalizado el procesamiento de colaboradores.");
    } else {
        console.log("[DEBUG] No se encontraron colaboradores o los datos no son un array.");
        listaSeleccionados.innerHTML = '<li><span class="text-muted">No hay empleados/equipos seleccionados para esta capacitación.</span></li>';
    }

    const selectDrawfs = modalElement.querySelector('#drawfs');
    if (selectDrawfs) {
        selectDrawfs.innerHTML = '';

        if (collaboratorsData && collaboratorsData.length > 0) {
            const empleadoIdsSeleccionados = collaboratorsData
                .filter(colaborador => colaborador.id_empleado != null)
                .map(colaborador => colaborador.id_empleado);
            const equipoIdsSeleccionados = collaboratorsData
                .filter(colaborador => colaborador.id_equipo != null)
                .map(colaborador => colaborador.id_equipo);

                console.log("[DEBUG] colaboradoresData completo:", collaboratorsData); // <-- Añade esto
            console.log("[DEBUG] equipoIdsSeleccionados antes de loadOptions:", equipoIdsSeleccionados); // <-- Añade esto

            const todosSonEmpleados = collaboratorsData.every(colaborador => colaborador.empleado_nombre != null);
            const todosSonEquipos = collaboratorsData.every(colaborador => colaborador.equipo_alias != null);

            if (todosSonEmpleados) {
                console.log("[DEBUG] Cargando lista de empleados para el select con IDs seleccionados.");
                await loadOptions(
                    selectDrawfs,
                    '../../backend/controller/usuario/capacitacion/todos_los_empleados.php',
                    'id_empleado',
                    (empleado) => `${empleado.nombre} ${empleado.apellido}`, // Esta es la función para el nombre del empleado
                    empleadoIdsSeleccionados
                );
            } else if (todosSonEquipos) {
                console.log("[DEBUG] Cargando lista de equipos para el select con IDs seleccionados.");
                await loadOptions(
                    selectDrawfs,
                    '../../backend/controller/usuario/capacitacion/todos_los_equipos.php',
                    'id_equipo',
                    (equipo) => equipo.alias, // Asegúrate de que esto sea una función que devuelve el alias
                    equipoIdsSeleccionados
                );
            } else {
                console.log("[DEBUG] Los colaboradores actuales son de diferentes tipos o no hay. No se llenará el select.");
                const defaultOption = document.createElement('option');
                defaultOption.textContent = 'Los colaboradores actuales son de diferentes tipos.';
                selectDrawfs.appendChild(defaultOption);
                
            }
        } else {
            console.log("[DEBUG] No hay colaboradores actuales. Cargando ambas listas sin IDs seleccionados.");
            await loadOptions(selectDrawfs, '../../backend/controller/usuario/capacitacion/todos_los_empleados.php', 'id_empleado', (empleado) => `${empleado.nombre} ${empleado.apellido}`, []);
            await loadOptions(selectDrawfs, '../../backend/controller/usuario/capacitacion/todos_los_equipos.php', 'id_equipo', (equipo) => equipo.alias, []); // Asegúrate de que esto sea una función
        }
    } else {
        console.error("[DEBUG] No se encontró el select con ID #drawfs dentro del modal.");
    }
}

async function loadOptions(selectElement, url, valueKey, displayKeyCallback, selectedIds = []) {
    console.log(`[DEBUG] Intentando cargar opciones desde: ${url}`);
    console.log(`[DEBUG] loadOptions recibiendo selectedIds:`, selectedIds);
    const params = new URLSearchParams();

    // Determinar el tipo basado en la URL o valueKey una sola vez
    let optionType = '';
    if (valueKey === 'id_empleado') {
        optionType = 'employee';
    } else if (valueKey === 'id_equipo') {
        optionType = 'team';
    }
    if (!optionType) {
        console.warn(`[DEBUG] Tipo de opción no reconocido para url: ${url} y valueKey: ${valueKey}`);
        // Continuar, pero sin añadir data-type si no se reconoce el tipo
    }


    try {
        if (selectedIds && selectedIds.length > 0) {
            params.append('selected_ids', JSON.stringify(selectedIds));
            url += (url.includes('?') ? '&' : '?') + params.toString();
            console.log(`[DEBUG] URL con IDs seleccionados: ${url}`);
        }

        const response = await fetch(url);
        const responseText = await response.text();
        console.log(`[DEBUG] Respuesta RAW de ${url}:`, responseText);

        if (!response.ok) {
            try {
                const errorData = JSON.parse(responseText);
                console.error(`[DEBUG] Detalles del error del servidor:`, errorData);
                mostrarAlerta('error', `Error ${response.status}`, errorData.error || 'Error desconocido al cargar opciones.');
            } catch (e) {
                mostrarAlerta('error', `Error ${response.status}`, `No se pudieron cargar las opciones. ${responseText.substring(0, 100)}...`);
            }
            return;
        }

        let data;
        try {
            data = JSON.parse(responseText);
            console.log(`[DEBUG] Datos parseados de ${url}:`, data);
        } catch (e) {
            console.error(`[DEBUG] Error parseando JSON de ${url}:`, e, `Texto recibido:`, responseText);
            mostrarAlerta('error', 'Error de datos', 'El formato de datos de opciones es inválido.');
            return;
        }

        if (!Array.isArray(data)) {
            console.error(`[DEBUG] Error: La respuesta de ${url} NO es un array. Tipo recibido:`, typeof data, `Datos:`, data);
            mostrarAlerta('error', 'Error de formato', 'La lista de opciones recibida no tiene el formato esperado.');
            return;
        }

        // selectElement.innerHTML = ''; // Limpiar opciones existentes
        // Ojo: Si cargas empleados Y equipos, no limpies aquí, sino antes de llamar a loadOptions la primera vez para el select.

        data.forEach(item => {
            const option = document.createElement('option');

            if (item[valueKey] === undefined) {
                console.warn(`[DEBUG] Ítem de opciones sin la clave de valor esperada "${valueKey}":`, item);
                return;
            }

            option.value = item[valueKey]; // Asigna el ID

            let optionText = '';
            try {
                optionText = displayKeyCallback(item);
                if (typeof optionText !== 'string') {
                    console.warn(`[DEBUG] La función displayKeyCallback no devolvió una cadena para el ítem:`, item, `Devolvió:`, optionText);
                    optionText = 'Nombre inválido';
                }
            } catch (e) {
                console.error(`[DEBUG] Error ejecutando displayKeyCallback para el ítem:`, item, e);
                optionText = 'Error al obtener nombre';
            }
            option.textContent = optionText; // Asigna el texto visible

            // *** AÑADIR EL ATRIBUTO data-type ***
            if (optionType) {
                 option.dataset.type = optionType;
                 console.log(`[DEBUG] Opción creada: value=${option.value}, text=${option.textContent}, data-type=${option.dataset.type}`);
            } else {
                 console.log(`[DEBUG] Opción creada (sin data-type): value=${option.value}, text=${option.textContent}`);
            }
            // ***********************************


            if (selectedIds.includes(item[valueKey].toString())) { // Asegúrate de comparar tipos (string vs string o number vs number)
                 option.selected = true; // Pre-seleccionar la opción
                 console.log(`[DEBUG] Opción ${option.value} marcada como seleccionada.`);
            }
            selectElement.appendChild(option);
        });
        console.log(`[DEBUG] Opciones cargadas exitosamente en el select.`);
    } catch (error) {
        console.error(`[DEBUG] Error general al cargar o procesar opciones desde ${url}:`, error);
        mostrarAlerta('error', 'Error de red o procesamiento', 'Ocurrió un error inesperado al cargar las opciones.');
    }
}
// Listener global para los clicks en elementos con la clase 'add-tag-btn' (sin cambios)
document.addEventListener('click', function(event) {
    const targetButton = event.target.closest('.add-tag-btn');
    if (targetButton) {
        const capacitacionId = targetButton.getAttribute('data-id');
        if (capacitacionId) {
            const id = parseInt(capacitacionId, 10);
            if (!isNaN(id)) {
                openAddCapacitacionModal(id);
            } else {
                 console.error(`El atributo data-id del elemento no es un número válido: ${capacitacionId}`);
                 mostrarAlerta('error', 'Error', 'ID de capacitación inválido.');
            }
        } else {
            console.error("El elemento clickeado con clase 'add-tag-btn' no tiene el atributo 'data-id'.");
            mostrarAlerta('error', 'Error', 'No se pudo obtener el ID de la capacitación.');
        }
    }
});
// Escuchar los clicks
/*document.addEventListener('click', function(event) {
    if (event.target.classList.contains('add-tag-btn')) {
        const capacitacionId = event.target.getAttribute('data-id');
        openAddCapacitacionModal(capacitacionId);
    }
});*/



$(document).ready(function() {
    $('.js-example-basic-single').select2({
        // Agrega o modifica esta línea
        width: '100%',
        // Otras opciones de Select2 si las tienes
        // placeholder: 'Seleccionar Empleados o Equipos',
        // allowClear: true
    });
});







//document.addEventListener('DOMContentLoaded', function() {
    // Get references to the elements
    const radioButtons = document.querySelectorAll('input[name="radioDeemplead"]');
    const selectElement = $('.js-example-basic-single'); // Use jQuery for Select2
    const selectedItemsList = document.getElementById('selectedItemsList');
    const idUsuarioActual = document.getElementById('entidad').value; // Get current user ID

    // Array to store selected items (ID, name, type)
    let selectedItems = [];

    // Initialize Select2 (if not already initialized elsewhere)
    selectElement.select2({
        placeholder: "Seleccionar Empleados o Equipos",
        allowClear: true // Optional: adds a clear button to the select itself
    });

     // Set up the container for selected items with flex properties
    selectedItemsList.classList.add('d-flex', 'flex-wrap', 'gap-2'); // Add flex, wrap, and gap for spacing

    // Function to fetch employees
    async function fetchEmployees(userId) {
        selectElement.prop('disabled', true).trigger('change'); // Deshabilitar al inicio

        const url = `../../backend/controller/usuario/capacitacion/buscar_colaboradores.php?idUser=${encodeURIComponent(userId)}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
             // Check if the response contains an error property
            if (data && data.error) {
                 console.error("Error fetching employees:", data.error);
                 // You might want to display this error to the user
                 return []; // Return empty array on error
            }
            return data; // Expects [{ id_empleado: ..., nombre: ..., apellido: ... }]
        } catch (error) {
            console.error("Error fetching employees:", error);
            // You might want to display an error message to the user
            return []; // Return empty array on fetch error
        } finally {
            selectElement.prop('disabled', false).trigger('change'); // Habilitar al finalizar (éxito o error)
        }
    }

    // Function to fetch teams
    async function fetchTeams() {
        selectElement.prop('disabled', true).trigger('change'); // Deshabilitar al inicio

        const url = `../../backend/controller/usuario/capacitacion/buscar_equipos.php`;
         try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            // Check if the response contains an error property
            if (data && data.error) {
                 console.error("Error fetching teams:", data.error);
                 // You might want to display this error to the user
                 return []; // Return empty array on error
            }
            return data; // Return empty array on error
            return data; // Expects [{ id_equipo: ..., alias: ... }]
         } catch (error) {
            console.error("Error fetching teams:", error);
            // You might want to display an error message to the user
            return []; // Return empty array on fetch error
        }  finally {
            selectElement.prop('disabled', false).trigger('change'); // Habilitar al finalizar (éxito o error)
        }
    }

    // Function to populate the select dropdown using Select2 methods
   // Function to populate the select dropdown using Select2 methods
   function populateSelect(options, type) {
    selectElement.empty(); // Clear existing options using Select2 method
    // Add the default placeholder option
    const placeholderText = type === 'individual' ? "Seleccionar Empleados" : (type === 'equipo' ? "Seleccionar Equipos" : "Seleccionar Empleados o Equipos");
    selectElement.append(new Option(placeholderText, "", true, true)); // text, value, defaultSelected, selected
    selectElement.val(null).trigger('change'); // Reset selected value and trigger Select2 update

    // Deshabilitar el select al inicio de la carga
    selectElement.prop('disabled', true);

    if (options && options.length > 0) {
         options.forEach(item => {
            let optionText = "";
            let optionValue = "";
            if (type === 'individual') {
                optionText = `${item.nombre} ${item.apellido}`;
                optionValue = item.id_empleado;
            } else if (type === 'equipo') {
                optionText = item.alias;
                optionValue = item.id_equipo;
            }
             // Prevent adding empty or invalid options
             if (optionValue) {
                // Add new option using Select2 method
                selectElement.append(new Option(optionText, optionValue, false, false));
             }
        });
        // Habilitar el select después de cargar las opciones
        selectElement.prop('disabled', false);
    }
     selectElement.trigger('change'); // Trigger Select2 update after adding options
}

    // Function to render the list of selected items
    function renderSelectedItems() {
        selectedItemsList.innerHTML = ''; // Clear the current list

        if (selectedItems.length === 0) {
             // No need to display anything if the list is empty
             return;
        }

        // Render items (employees or teams) as badges
        selectedItems.forEach(item => {
            const itemElement = document.createElement('span'); // Use span for badge/tag
            itemElement.classList.add(
                'badge',          // Bootstrap badge class
                'bg-secondary',   // Background color
                'text-light',     // Text color
                'p-2',            // Padding
                'd-inline-flex',  // Use flex to align content and button
                'align-items-center', // Vertically center text and button
                'selected-item'   // Custom class to easily select items for removal
            );
             // Add type-specific class if needed for styling, e.g., 'item-individual', 'item-equipo'
            itemElement.classList.add(`item-${item.type}`);

            // Set data attributes for easy access during removal
            itemElement.setAttribute('data-id', item.id);
            itemElement.setAttribute('data-tipo', item.type);

            // Create span for item name
            const itemName = document.createElement('span');
             // Add a small margin to the text if there's a close button
            itemName.textContent = item.name;
            itemElement.appendChild(itemName);

            // If it's not the 'todos' item, add a remove button
            if (item.type !== 'todos') {
                 itemName.classList.add('me-2'); // Add margin-right to text if button exists

                const removeButton = document.createElement('button');
                removeButton.type = 'button'; // Important for button element
                removeButton.classList.add('btn-close', 'btn-close-white'); // Bootstrap close button, white color for dark background
                removeButton.setAttribute('aria-label', 'Remove');
                 // Add a small margin-left to the button
                 removeButton.classList.add('ms-1');

                itemElement.appendChild(removeButton);
            }


            selectedItemsList.appendChild(itemElement);
        });
    }

    // Handle radio button changes
    radioButtons.forEach(radio => {
        radio.addEventListener('change', async function() {
            const selectedValue = this.value;

            // Clear previous selections from the list
            selectedItems = [];

            if (selectedValue === 'individual') {
                selectElement.prop('disabled', false).trigger('change'); // Enable select
                selectElement.select2({ placeholder: "Seleccionar Empleados" }); // Set placeholder

                // selectElement.attr('name', 'empleados[]'); // Change name for form submission if needed
                const employees = await fetchEmployees(idUsuarioActual);
                populateSelect(employees, 'individual');
                // Note: Select2 placeholder is updated in populateSelect

            } else if (selectedValue === 'equipo') {
                selectElement.prop('disabled', false).trigger('change'); // Enable select
                selectElement.select2({ placeholder: "Seleccionar Equipos" }); // Set placeholder

                 // selectElement.attr('name', 'equipos[]'); // Change name for form submission if needed
                const teams = await fetchTeams();
                populateSelect(teams, 'equipo');
                 // Note: Select2 placeholder is updated in populateSelect

            } else if (selectedValue === 'todos') {
                selectElement.prop('disabled', true).trigger('change'); // Disable select
                 // selectElement.attr('name', 'equipos[]'); // Assume 'todos' applies to teams for form submission
                selectElement.empty().trigger('change'); // Clear select options
                selectElement.select2({ placeholder: "" }); // Clear Select2 placeholder visually

                // Clear Select2 placeholder visually as well
                selectElement.select2({ placeholder: "" });


                // Add 'todos' indicator to selectedItems
                // Ensure this is the *only* item when 'todos' is selected
                 selectedItems.push({ id: 'all', type: 'todos', name: 'Todos los equipos' });

            }

             // Re-render the list based on the (now cleared or updated) selectedItems array
             renderSelectedItems();
        });
    });

    // Handle select dropdown changes (item selection)
    selectElement.on('select2:select', function (e) {
        const data = e.params.data;
        const itemId = data.id;
        const itemText = data.text;

        // Determine the type based on which radio is checked
        const checkedRadio = document.querySelector('input[name="radioDeemplead"]:checked');
        if (!checkedRadio || (checkedRadio.value !== 'individual' && checkedRadio.value !== 'equipo')) {
            // Should not happen if logic is correct, but as a safeguard
            console.warn("Select change occurred without 'individual' or 'equipo' radio checked.");
            // Reset select just in case
            selectElement.val(null).trigger('change');
            return;
        }
        const itemType = checkedRadio.value; // 'individual' or 'equipo'

        // Prevent adding the placeholder option (which has an empty value)
        if (!itemId) {
             selectElement.val(null).trigger('change'); // Reset select
             return;
        }

        // Check if item is already in the selected list
        // Use strict comparison for ID if they are numeric, or loose if they might be strings
        const alreadySelected = selectedItems.some(item => item.id == itemId && item.type === itemType);

        if (!alreadySelected) {
            selectedItems.push({ id: itemId, type: itemType, name: itemText });
            renderSelectedItems(); // Re-render the list
        }

        // Reset the select element back to the placeholder after selection
        // This is the standard Select2 behavior for allowing multiple selections
        selectElement.val(null).trigger('change');
    });

    selectedItemsList.addEventListener('click', function(event) {
        const removeButton = event.target.closest('.btn-close');
    
        if (removeButton) {
            const itemElement = removeButton.closest('.selected-item');
            if (itemElement) {
                const itemId = itemElement.getAttribute('data-id'); // Correcto, lee data-id
                // --- CORREGIR ESTA LÍNEA ---
                const itemType = itemElement.getAttribute('data-tipo'); // <-- ¡Leer data-tipo!
    
                console.log("--- Click en eliminar ---");
                console.log("ID del elemento clickeado (atributo):", itemId, typeof itemId);
                console.log("Tipo del elemento clickeado (atributo):", itemType, typeof itemType); // Ahora debería ser "individual" o "equipo"
                console.log("-------------------------");
    
                selectedItems = selectedItems.filter(item => {
                     const itemIdInArray = String(item.id);
                     const itemTypeInArray = item.type;
    
                     console.log(`Filtrando item: (ID: "${itemIdInArray}", Tipo: "${itemTypeInArray}")`);
                     console.log(`  Comparando con clickeado: (ID: "${itemId}", Tipo: "${itemType}")`);
                     console.log(`  Resultado ID ===: ${itemIdInArray === itemId}`);
                     console.log(`  Resultado Tipo ===: ${itemTypeInArray === itemType}`); // Esto ahora debería dar true cuando coincida el tipo
                     console.log(`  Resultado AND: ${itemIdInArray === itemId && itemTypeInArray === itemType}`); // Esto ahora debería dar true cuando coincida ID y Tipo
                     console.log(`  Resultado filtro (negado AND): ${!(itemIdInArray === itemId && itemTypeInArray === itemType)}`);
    
    
                     return !(itemIdInArray === itemId && itemTypeInArray === itemType);
                });
    
                console.log("--- Fin de filtro ---");
                console.log("Array selectedItems después de filtrar:", selectedItems);
    
                renderSelectedItems(); // Mantén esta línea
    
                console.log("--- renderSelectedItems llamado ---");
            }
        }
    });
    // Initial state: Disable the select and clear the list
    selectElement.prop('disabled', true).trigger('change');
     // Also clear placeholder visually when disabled initially
    selectElement.select2({ placeholder: "" });
    selectedItems = []; // Ensure the array is empty
    renderSelectedItems(); // Render initially empty list


     // Optional: Check initial radio state on page load
     // This handles cases where a radio button might be pre-checked by the server
     const initiallyCheckedRadio = document.querySelector('input[name="radioDeemplead"]:checked');
     if(initiallyCheckedRadio) {
         // Trigger the change event manually to set up the initial state
         initiallyCheckedRadio.dispatchEvent(new Event('change'));
     }

//});





/**
 * Abre un modal específico para gestionar las certificaciones de una capacitación.
 * Si el modal para esa capacitación ya existe, simplemente lo muestra.
 * @param {number} idCapacitacion - El ID de la capacitación.
 */

async function openCertificacionesModal(idCapacitacion) {
    console.log("Abriendo modal de certificaciones para ID:", idCapacitacion);

    const xxcertificacionesContainer = document.getElementById('certificaciones');
    if (!xxcertificacionesContainer) {
        console.error("Error: Elemento con id='certificaciones' no encontrado en el DOM.");
        return;
    }
    xxcertificacionesContainer.innerHTML = ""; // Limpiar el contenedor

    const mainModalId = `certificacionesModal-${idCapacitacion}`;
    const mainModalHTML = `
        <div class="modal fade" id="${mainModalId}" tabindex="-1" role="dialog" aria-labelledby="certificacionesModalLabel-${idCapacitacion}" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="certificacionesModalLabel-${idCapacitacion}">Certificaciones de Capacitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">Buscar y seleccionar:</p>
                        <div class="row align-items-center mb-2">
                            <div class="col-auto">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filterType-${idCapacitacion}" id="filterEmpleados-${idCapacitacion}" value="empleados" checked>
                                    <label class="form-check-label" for="filterEmpleados-${idCapacitacion}">Empleados</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="filterType-${idCapacitacion}" id="filterEquipos-${idCapacitacion}" value="equipos">
                                    <label class="form-check-label" for="filterEquipos-${idCapacitacion}">Equipos</label>
                                </div>
                            </div>
                            <div class="col">
                                <select class="js-example-disabled-results form-control" style="width: 100%">
                                    <option value="" selected disabled>Buscar...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-check my-3 border-bottom pb-2">
                            <input class="form-check-input" type="checkbox" id="checkTodosEmpleados-${idCapacitacion}">
                            <label class="form-check-label" for="checkTodosEmpleados-${idCapacitacion}">
                                Seleccionar/Deseleccionar Todos los Empleados 
                            </label>
                            <div id="spinnerTodos-${idCapacitacion}" class="spinner-border spinner-border-sm text-primary ms-2 d-none" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        <input type="text" style="visibility:hidden;height:1px;">
                        <div id="certificacionesList-${idCapacitacion}">
                            {/* Aquí se listarán los empleados agrupados por equipo */}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" style="display:none;">Guardar Certificaciones</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    const mainModalWrapper = document.createElement('div');
    mainModalWrapper.innerHTML = mainModalHTML.trim();
    const mainModalElement = mainModalWrapper.firstChild;

    if (mainModalElement) {
        xxcertificacionesContainer.appendChild(mainModalElement);
        const xxcertificationsModal = new bootstrap.Modal(mainModalElement);

        const listaColaboradoresContainer = document.getElementById(`certificacionesList-${idCapacitacion}`);
        if (!listaColaboradoresContainer) {
            console.error("Error: Contenedor de lista de colaboradores no encontrado:", `certificacionesList-${idCapacitacion}`);
            return;
        }

        let empleadosz = [];

        try {
            const response = await fetch(`../../backend/controller/usuario/capacitacion/empleados-capacitacion.php?idUserx=${encodeURIComponent(idCapacitacion)}`);
            if (!response.ok) {
                console.error(`Error al obtener empleados de la capacitación: ${response.status}`);
                listaColaboradoresContainer.innerHTML = `<p class="text-danger">Error al cargar la lista de colaboradores.</p>`;
                return;
            }
            empleadosz = await response.json();
            console.log("Datos de empleados recibidos:", empleadosz);

            const selectElement = $(mainModalElement).find('.js-example-disabled-results');
            const radioEmpleados = mainModalElement.querySelector(`#filterEmpleados-${idCapacitacion}`);
            const radioEquipos = mainModalElement.querySelector(`#filterEquipos-${idCapacitacion}`);
            // ===== NUEVA REFERENCIA AL CHECKBOX "TODOS" Y SU SPINNER =====
            const checkTodosEmpleados = mainModalElement.querySelector(`#checkTodosEmpleados-${idCapacitacion}`);
            const spinnerTodos = mainModalElement.querySelector(`#spinnerTodos-${idCapacitacion}`);


            const updateSelect2Options = (filterType) => {
                if (selectElement.data('select2')) {
                    selectElement.select2('destroy');
                }
                selectElement.empty(); 

                const select2Data = [];
                let placeholderText = '';

                if (filterType === 'empleados') {
                    placeholderText = 'Buscar Empleados';
                    const empleadosData = empleadosz.filter(item => item.id_colaborador !== null);
                    if (empleadosData.length > 0) {
                        const empleadosPorEquipoSelect = empleadosData.reduce((acc, item) => {
                            const equipo = item.equipo_alias || 'Sin Equipo';
                            if (!acc[equipo]) acc[equipo] = [];
                            acc[equipo].push(item);
                            return acc;
                        }, {});
                        const empleadosIndividualesSelect = empleadosz.filter(item => item.id_colaborador !== null && item.id_equip === null);
                        if (empleadosIndividualesSelect.length > 0) {
                            select2Data.push({
                                text: '<b>Empleados Individuales</b>',
                                children: empleadosIndividualesSelect.map(item => ({
                                    id: `empleado-${item.id_colaborador}`,
                                    text: `${item.empleado_nombre || ''} ${item.empleado_apellido || ''}`.trim()
                                }))
                            });
                        }
                        Object.keys(empleadosPorEquipoSelect).filter(equipo => equipo !== 'Sin Equipo').forEach(equipo => {
                            select2Data.push({
                                text: `<b>${equipo}</b>`,
                                children: empleadosPorEquipoSelect[equipo].map(item => ({
                                    id: `empleado-${item.id_colaborador}`,
                                    text: `${item.empleado_nombre || ''} ${item.empleado_apellido || ''}`.trim()
                                }))
                            });
                        });
                    }
                } else if (filterType === 'equipos') {
                    placeholderText = 'Buscar Equipos';
                    const equiposUnicos = Object.values(empleadosz.reduce((acc, item) => {
                        if (item.id_equip !== null && item.equipo_alias && item.equipo_alias.trim() !== '') {
                            acc[item.id_equip] = { id_equip: item.id_equip, equipo_alias: item.equipo_alias };
                        }
                        return acc;
                    }, {})).filter(equipo => equipo.id_equip !== null);
                    if (equiposUnicos.length > 0) {
                        select2Data.push({
                            text: '<b>Equipos</b>',
                            children: equiposUnicos.map(item => ({
                                id: `equipo-${item.id_equip}`,
                                text: item.equipo_alias || 'Sin Nombre de Equipo'
                            }))
                        });
                    }
                }

                if (selectElement.length > 0) {
                    selectElement.select2({
                        placeholder: placeholderText,
                        allowClear: true,
                        width: '100%',
                        data: select2Data,
                        escapeMarkup: function (markup) { return markup; },
                        dropdownParent: $(mainModalElement).find('.modal-body')
                    });
                    selectElement.val(null).trigger('change');
                } else {
                    console.warn("Select2: Elemento no encontrado.");
                }
                const listItems = listaColaboradoresContainer.querySelectorAll('.card.mb-2');
                listItems.forEach(item => { item.style.display = ''; });
                 // Al cambiar el filtro, desmarcar "Seleccionar Todos"
                if (checkTodosEmpleados) checkTodosEmpleados.checked = false;
            };

            radioEmpleados.addEventListener('change', () => { if (radioEmpleados.checked) updateSelect2Options('empleados'); });
            radioEquipos.addEventListener('change', () => { if (radioEquipos.checked) updateSelect2Options('equipos'); });

            selectElement.on('select2:select', function (e) {
                const selectedId = e.params.data.id;
                const listItems = listaColaboradoresContainer.querySelectorAll('.card.mb-2');
                listItems.forEach(item => {
                    const employeeId = item.dataset.employeeId;
                    let shouldShow = false;
                    if (selectedId?.startsWith('empleado-')) {
                        shouldShow = employeeId === selectedId.substring(9);
                    } else if (selectedId?.startsWith('equipo-')) {
                        const selectedTeamId = selectedId.substring(7);
                        const employeesInTeam = empleadosz.filter(emp => emp.id_equip == selectedTeamId);
                        shouldShow = employeesInTeam.some(emp => emp.id_colaborador == employeeId);
                    } else if (!selectedId) {
                        const currentFilter = mainModalElement.querySelector(`input[name="filterType-${idCapacitacion}"]:checked`).value;
                        if (currentFilter === 'empleados') shouldShow = item.dataset.employeeId !== '';
                        else if (currentFilter === 'equipos') shouldShow = true;
                        else shouldShow = true;
                    }
                    item.style.display = shouldShow ? '' : 'none';
                });
                // Al filtrar, desmarcar "Seleccionar Todos"
                if (checkTodosEmpleados) checkTodosEmpleados.checked = false;
                const firstVisible = listaColaboradoresContainer.querySelector('.card.mb-2:not([style*="display: none"])');
                if (firstVisible) firstVisible.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });

            selectElement.on('select2:clear', function (e) {
                const currentFilter = mainModalElement.querySelector(`input[name="filterType-${idCapacitacion}"]:checked`).value;
                const listItems = listaColaboradoresContainer.querySelectorAll('.card.mb-2');
                listItems.forEach(item => {
                    let shouldShow = true;
                    if (currentFilter === 'empleados') shouldShow = item.dataset.employeeId !== '';
                    else if (currentFilter === 'equipos') shouldShow = true;
                    item.style.display = shouldShow ? '' : 'none';
                });
                // Al limpiar filtro, desmarcar "Seleccionar Todos"
                if (checkTodosEmpleados) checkTodosEmpleados.checked = false;
            });

            const initialFilterType = mainModalElement.querySelector(`input[name="filterType-${idCapacitacion}"]:checked`).value;
            updateSelect2Options(initialFilterType);

            listaColaboradoresContainer.innerHTML = "";
            const empleadosPorEquipoList = empleadosz.reduce((acc, item) => {
                const equipo = item.equipo_alias || 'Sin Equipo';
                if (!acc[equipo]) acc[equipo] = [];
                acc[equipo].push(item);
                return acc;
            }, {});

            const empleadosIndividualesList = empleadosz.filter(item => item.id_colaborador !== null && item.id_equip === null);
            if (empleadosIndividualesList.length > 0) {
                listaColaboradoresContainer.innerHTML += `<h6 class="mt-3">Empleados Individuales</h6>`;
                empleadosIndividualesList.forEach((note) => {
                    if (note.id_colaborador) {
                        const isCertificado = note.certificacion === 'si';
                        listaColaboradoresContainer.innerHTML += `
                            <div class="card mb-2" data-employee-id="${note.id_colaborador}" data-equipo-id="">
                                <div class="card-body d-flex justify-content-between align-items-center py-1">
                                    <div class="form-check d-flex align-items-center flex-grow-1">
                                        <input class="form-check-input checkbox individual-cert-checkbox" type="checkbox" id="certificacion-${idCapacitacion}-${note.id_colaborador}" data-capacitacion-id="${idCapacitacion}" data-colaborador-id="${note.id_colaborador}" data-equipo-id="" ${isCertificado ? 'checked' : ''}>
                                        <label class="form-check-label text-capitalize ms-2 me-auto pt-1" for="certificacion-${idCapacitacion}-${note.id_colaborador}">${note.empleado_nombre} ${note.empleado_apellido}</label>
                                        <div class="cert-spinner spinner-border text-primary spinner-border-sm ms-2 d-none" role="status" id="spinner-cert-${idCapacitacion}-${note.id_colaborador}"><span class="visually-hidden">Loading...</span></div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-info" style="display:none;" onclick="cargarFormularioCertificacion('${idCapacitacion}', '${note.id_colaborador}')">Gestionar</button>
                                </div>
                            </div>`;
                    }
                });
            }

            Object.keys(empleadosPorEquipoList).filter(equipo => equipo !== 'Sin Equipo').forEach(equipo => {
                listaColaboradoresContainer.innerHTML += `<h6 class="mt-3">${equipo}</h6>`;
                empleadosPorEquipoList[equipo].forEach((note) => {
                    if (note.id_colaborador !== null) {
                        const isCertificado = note.certificacion === 'si';
                        listaColaboradoresContainer.innerHTML += `
                            <div class="card mb-2" data-employee-id="${note.id_colaborador}" data-equipo-id="${note.id_equip || ''}">
                                <div class="card-body d-flex justify-content-between align-items-center py-1">
                                    <div class="form-check d-flex align-items-center flex-grow-1">
                                        <input class="form-check-input checkbox individual-cert-checkbox" type="checkbox" id="certificacion-${idCapacitacion}-${note.id_colaborador}" data-capacitacion-id="${idCapacitacion}" data-colaborador-id="${note.id_colaborador}" data-equipo-id="${note.id_equip || ''}" ${isCertificado ? 'checked' : ''}>
                                        <label class="form-check-label text-capitalize ms-2 me-auto pt-1" for="certificacion-${idCapacitacion}-${note.id_colaborador}">${note.empleado_nombre || ''} ${note.empleado_apellido || ''}</label>
                                        <div class="cert-spinner spinner-border text-primary spinner-border-sm ms-2 d-none" role="status" id="spinner-cert-${idCapacitacion}-${note.id_colaborador}"><span class="visually-hidden">Loading...</span></div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-info" style="display:none;" onclick="cargarFormularioCertificacion('${idCapacitacion}', '${note.id_colaborador}')">Gestionar</button>
                                </div>
                            </div>`;
                    }
                });
            });
            
            // ===== LISTENER PARA CHECKBOXES INDIVIDUALES (MODIFICADO) =====
            listaColaboradoresContainer.querySelectorAll('.individual-cert-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', async function() { // Convertido a async para el await
                    const currentCheckbox = this;
                    const spinnerElement = currentCheckbox.closest('.form-check').querySelector('.cert-spinner');
                    const nuevoEstadoCertificacion = this.checked ? 'si' : 'no';
                    const capacitacionIdActual = this.dataset.capacitacionId;
                    const colaboradorIdActual = this.dataset.colaboradorId || null;
                    const equipoIdActual = this.dataset.equipoId || null;

                    currentCheckbox.disabled = true;
                    if(spinnerElement) spinnerElement.classList.remove('d-none');

                    try {
                        await actualizarCertificacion(capacitacionIdActual, colaboradorIdActual, equipoIdActual, nuevoEstadoCertificacion, currentCheckbox, spinnerElement);
                         // Si un checkbox individual se desmarca, desmarcar "Todos"
                        if (!this.checked && checkTodosEmpleados && checkTodosEmpleados.checked) {
                            checkTodosEmpleados.checked = false;
                        }
                        // Opcional: verificar si todos están marcados para marcar "Todos" (puede ser costoso)

                    } catch (error) {
                        console.error("Error en el listener individual:", error)
                        // El error ya debería manejarse en actualizarCertificacion, incluyendo revertir el check y habilitar
                    } finally {
                        // El disable y spinner se manejan en actualizarCertificacion, pero por si acaso:
                        // currentCheckbox.disabled = false;
                        // if(spinnerElement) spinnerElement.classList.add('d-none');
                    }
                });
            });

            // ===== LISTENER PARA EL CHECKBOX "TODOS" =====
            if (checkTodosEmpleados) {
                checkTodosEmpleados.addEventListener('change', async function() {
                    const isChecked = this.checked;
                    // Solo empleados con data-colaborador-id y que estén visibles
                    const checkboxesVisibles = listaColaboradoresContainer.querySelectorAll('.card.mb-2:not([style*="display: none"]) .individual-cert-checkbox[data-colaborador-id]');

                    if (checkboxesVisibles.length === 0) {
                        console.log("No hay empleados visibles para (de)seleccionar.");
                        this.checked = !isChecked; // Revertir si no hay nada que hacer
                        return;
                    }

                    this.disabled = true;
                    if (spinnerTodos) spinnerTodos.classList.remove('d-none');

                    const promises = [];
                    checkboxesVisibles.forEach(chk => {
                        if (chk.checked !== isChecked) { // Solo actuar si el estado es diferente
                            chk.checked = isChecked; // Cambiar estado visual inmediatamente

                            const currentIndividualCheckbox = chk;
                            const spinnerIndividualElement = currentIndividualCheckbox.closest('.form-check').querySelector('.cert-spinner');
                            const nuevoEstado = isChecked ? 'si' : 'no';
                            const capId = currentIndividualCheckbox.dataset.capacitacionId;
                            const colId = currentIndividualCheckbox.dataset.colaboradorId;
                            const eqId = currentIndividualCheckbox.dataset.equipoId;

                            currentIndividualCheckbox.disabled = true;
                            if(spinnerIndividualElement) spinnerIndividualElement.classList.remove('d-none');
                            
                            promises.push(
                                actualizarCertificacion(capId, colId, eqId, nuevoEstado, currentIndividualCheckbox, spinnerIndividualElement)
                                .catch(err => {
                                    console.error(`Error actualizando masivamente a ${colId}:`, err);
                                    // No revertimos el check "Todos" aquí, la actualización individual falló.
                                    // actualizarCertificacion debería manejar el estado del checkbox individual.
                                })
                            );
                        }
                    });

                    try {
                        await Promise.all(promises);
                        console.log("Actualización masiva completada para elementos visibles.");
                    } catch (error) {
                        // Promise.all se rechaza en el primer error, pero los otros pueden haber continuado.
                        console.error("Al menos una actualización falló durante la operación masiva:", error);
                        // Aquí no revertimos el checkbox "Todos" automáticamente, ya que algunas operaciones pueden haber tenido éxito.
                        // El estado de los checkboxes individuales debe ser manejado por 'actualizarCertificacion'.
                    } finally {
                        this.disabled = false;
                        if (spinnerTodos) spinnerTodos.classList.add('d-none');
                    }
                });
            }

        } catch (error) {
            console.error("Error al procesar la respuesta JSON o construir HTML:", error);
            if (listaColaboradoresContainer) {
                listaColaboradoresContainer.innerHTML = `<p class="text-danger">Error al cargar la lista de colaboradores.</p>`;
            }
        }

        mainModalElement.addEventListener('hidden.bs.modal', function () {
            const selectElement = $(mainModalElement).find('.js-example-disabled-results');
            if (selectElement.data('select2')) {
                selectElement.select2('destroy');
            }
            mainModalElement.remove();
        });

        xxcertificationsModal.show();

    } else {
        console.error("Error: No se pudo crear el elemento modal principal.");
    }
}
// Mantén tus funciones auxiliares
async function actualizarCertificacion(idCapacitacion, idColaborador, idEquipo, certificacionEstado, checkboxElement, spinnerElement) {
     console.log(`Actualizando certificación para Capacitación ${idCapacitacion}, Colaborador ${idColaborador}, Equipo ${idEquipo} a estado: ${certificacionEstado}`);
     console.log("Enviando datos:", {
          id_capacitacion: idCapacitacion,
          id_colaborador: idColaborador,
          id_equipo: idEquipo,
          certificacion: certificacionEstado
     });

     try {
         const response = await fetch('../../backend/controller/usuario/capacitacion/actualizar_certificacion.php', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json'
             },
             body: JSON.stringify({
                 id_capacitacion: idCapacitacion,
                 id_colaborador: idColaborador,
                 id_equipo: idEquipo,
                 certificacion: certificacionEstado
             })
         });

         if (!response.ok) {
             console.error(`Error al actualizar la certificación: ${response.status}`);
             mostrarAlerta('error', 'Error', 'No se pudo actualizar la certificación.');
             return;
         }

         const data = await response.json();
         console.log('Certificación actualizada:', data);
          if (data.success) {
              //mostrarAlerta('success', 'Éxito', 'Certificación actualizada correctamente.');
          } else {
               mostrarAlerta('warning', 'Atención', data.message || 'Error al actualizar la certificación.');
          }

     } catch (error) {
         console.error('Error al enviar la solicitud de actualización de certificación:', error);
         mostrarAlerta('error', 'Error', 'Error al comunicarse con el servidor para actualizar la certificación.');
     } finally {
        // --- Ocultar spinner y habilitar checkbox SIEMPRE al finalizar ---
        if (checkboxElement) {
            checkboxElement.disabled = false; // Habilitar el checkbox
        }
        if (spinnerElement) {
            spinnerElement.classList.add('d-none'); // Ocultar el spinner
        }
        console.log('[ACTUALIZAR] Petición finalizada. UI actualizada.');
    }
}


// Función temporal para simular la carga del formulario de certificación (debes implementarla)
function cargarFormularioCertificacion(idCapacitacion, colaboradorId) {
    alert(`Cargar formulario de certificación para la capacitación ${idCapacitacion} y el colaborador ${colaboradorId}`);
    // Aquí deberías cargar dinámicamente el formulario específico para gestionar
    // las certificaciones del colaborador seleccionado. Esto podría implicar
    // otra llamada fetch para obtener el formulario o inyectar HTML ya existente.
}














document.addEventListener('DOMContentLoaded', function() {
    const fechaInicioInput = document.getElementById('fechaHora');
    const fechaFinInput = document.getElementById('fechaHorafin');

    // 1. Establecer la fecha y hora mínima para ambos campos a la actual
    const now = new Date();
    // Formato YYYY-MM-DDTHH:mm requerido por input type="datetime-local"
    const year = now.getFullYear();
    const month = (now.getMonth() + 1).toString().padStart(2, '0');
    const day = now.getDate().toString().padStart(2, '0');
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');

    const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;

    fechaInicioInput.min = minDateTime;
    fechaFinInput.min = minDateTime;

    // 2. Actualizar dinámicamente el mínimo de Fecha Fin basado en Fecha Inicio
    fechaInicioInput.addEventListener('input', function() {
        const inicioValue = fechaInicioInput.value;

        // Si se ha seleccionado una fecha y hora de inicio
        if (inicioValue) {
            // Establecer la fecha y hora mínima del campo 'fechaFin'
            fechaFinInput.min = inicioValue;

            // Opcional: Si la fecha/hora de Fin actual es anterior a la nueva fecha/hora de Inicio,
            // puedes resetear el campo de Fin o establecerlo igual al de Inicio.
            // Esto evita que el usuario quede con una selección inválida si reduce la fecha de inicio.
            if (fechaFinInput.value && fechaFinInput.value < inicioValue) {
                 fechaFinInput.value = inicioValue; // O podrías usar '' para borrarlo
            }
        } else {
            // Si se borra la fecha de inicio, el mínimo de Fin vuelve a ser la hora actual.
            fechaFinInput.min = minDateTime;
            // Opcional: Si se borra la fecha de inicio, también borrar la fecha de fin.
            // fechaFinInput.value = '';
        }
    });
});









document.addEventListener('DOMContentLoaded', (event) => {
    const selectLugar = document.getElementById('lugar');

    // Check if the select element exists before trying to populate it
    if (selectLugar) {
        // Replace 'get_edificios.php' with the actual path to your PHP file
        const phpScriptUrl = '../../backend/controller/usuario/capacitacion/conseguir_edificios.php';

        fetch(phpScriptUrl)
            .then(response => {
                // Check if the request was successful (status code 200-299)
                if (!response.ok) {
                    // Si hay un error HTTP, lanza un error para ir al .catch
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Parse the JSON response into a Javascript object/array
                return response.json();
            })
            .then(data => {
                // Assuming 'data' is an array of objects like [{id_edificio: ..., alias: ..., direccion: ...}, ...]

                // Check if the received data is an array and not an error object
                if (Array.isArray(data)) {
                    // Opcional: Limpiar opciones existentes si no quieres mantener la primera
                    // selectLugar.innerHTML = '<option value="" selected disabled>Seleccionar Edificio</option>';

                    data.forEach(edificio => {
                        const option = document.createElement('option');
                        option.value = edificio.direccion; // O edificio.id_edificio si lo prefieres para el valor
                        option.textContent = edificio.direccion; // O edificio.alias si ese es el texto a mostrar
                        selectLugar.appendChild(option);
                    });

                    // --- AGREGA ESTA LÍNEA ---
                    // Habilita el select una vez que las opciones se han cargado
                    //selectLugar.disabled = false;
                    // -------------------------

                } else {
                    // Handle case where PHP script returned an error JSON (e.g., {error: '...'})
                    console.error('Received data is not an array or contains an error:', data);
                    if (data && data.error) {
                         console.error('Server returned an error:', data.error);
                    } else {
                         console.error('Unknown data format from server.');
                    }
                    // Opcional: Si falla la carga, puedes dejarlo disabled o mostrar un mensaje
                    // selectLugar.disabled = true; // Ya está disabled por defecto, así que esto es redundante a menos que lo habilites antes
                }
            })
            .catch(error => {
                // Handle any errors during the fetch operation (network issues, script errors, etc.)
                console.error('Error fetching edificios:', error);
                // Si ocurre un error, el select permanece disabled (ya que estaba así por defecto)
                // Opcional: Mostrar un mensaje de error al usuario
            });
    } else {
        console.error('Select element with ID "lugar" not found.');
    }
});












document.addEventListener('DOMContentLoaded', (event) => {
    const selectObligacion = document.getElementById('obligacion');

    // Check if the select element exists before trying to populate it
    if (selectObligacion) {
        // Replace 'get_edificios.php' with the actual path to your PHP file
        const phpScriptUrl = '../../backend/controller/usuario/capacitacion/conseguir_obligacion.php';

        fetch(phpScriptUrl)
            .then(response => {
                // Check if the request was successful (status code 200-299)
                if (!response.ok) {
                    // Si hay un error HTTP, lanza un error para ir al .catch
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Parse the JSON response into a Javascript object/array
                return response.json();
            })
            .then(data => {
                // Assuming 'data' is an array of objects like [{id_edificio: ..., alias: ..., direccion: ...}, ...]

                // Check if the received data is an array and not an error object
                if (Array.isArray(data)) {
                    // Opcional: Limpiar opciones existentes si no quieres mantener la primera
                    // selectLugar.innerHTML = '<option value="" selected disabled>Seleccionar Edificio</option>';

                    data.forEach(obligacion => {
                        const option = document.createElement('option');
                        option.value = obligacion.descrip_obligacion; // O edificio.id_edificio si lo prefieres para el valor
                        option.textContent = obligacion.descrip_obligacion; // O edificio.alias si ese es el texto a mostrar
                        selectObligacion.appendChild(option);
                    });

                    // --- AGREGA ESTA LÍNEA ---
                    // Habilita el select una vez que las opciones se han cargado
                    //selectLugar.disabled = false;
                    // -------------------------

                } else {
                    // Handle case where PHP script returned an error JSON (e.g., {error: '...'})
                    console.error('Received data is not an array or contains an error:', data);
                    if (data && data.error) {
                         console.error('Server returned an error:', data.error);
                    } else {
                         console.error('Unknown data format from server.');
                    }
                    // Opcional: Si falla la carga, puedes dejarlo disabled o mostrar un mensaje
                    // selectLugar.disabled = true; // Ya está disabled por defecto, así que esto es redundante a menos que lo habilites antes
                }
            })
            .catch(error => {
                // Handle any errors during the fetch operation (network issues, script errors, etc.)
                console.error('Error fetching edificios:', error);
                // Si ocurre un error, el select permanece disabled (ya que estaba así por defecto)
                // Opcional: Mostrar un mensaje de error al usuario
            });
    } else {
        console.error('Select element with ID "lugar" not found.');
    }
});