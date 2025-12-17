  const radioIndividual = document.getElementById('radioDeempleado1');
    const radioEquipo = document.getElementById('radioDeempleado2');
    const radioTodos = document.getElementById('radioDeempleado3');


loadgrupos();


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











const form = document.getElementById("noteForm");
const notesContainer = document.getElementById("notesContainer");
const listaSeleccionados = document.getElementById('lista-seleccionados'); // Obtén la lista de seleccionados


// Obtener referencias al botón y al spinner
const guardarBtn = document.getElementById('guardarBtn');
const spinnerGuardar = document.getElementById('spinnerGuardar');


let allTeamsFetched = false; // Bandera para evitar múltiples fetch de todos los equipos




form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const selectColaborador = document.querySelector('.js-example-basic-single'); // Obtener el elemento select

      // Encontrar el option que está seleccionado por defecto
    const defaultOption = document.querySelector('select2-selection__placeholder');

     const getThemex = () => document.documentElement.getAttribute("data-bs-theme") || "light";
        const temax = getThemex(); // Detecta el tema actual

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
  
    formData.append("grupo", document.getElementById("grupo").value);
    formData.append("usuarioActual", document.getElementById("entidad").value);

   

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
      
        grupo: formData.get("grupo"),
        usuario: formData.get("usuarioActual"),
        colaboradores: colaboradoresIds,
        equipos: equiposIds,
        aplicarATodos: aplicarATodos
    });

    const response = await fetch("../../backend/controller/usuario/capacitacion/guardar_grupo.php", {
        method: "POST",
        body: formData,
    });

    const result = await response.json();

    if (result.success) {
        Swal.fire({
            title: "Exito",
            text: "Grupo Guardado exitosamente.",
            icon: "success",
            background: temax === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
            color: temax === "dark" ? "#ffffff" : "#333333" // Texto adaptativo
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

            selectColaborador.disabled = true;

 if (defaultOption) {
        defaultOption.textContent = 'Seleccionar Empleados o Equipos';
    }

            loadgrupos();
        });


   // --- Ocultar spinner y habilitar botón ---
   guardarBtn.disabled = false;
   spinnerGuardar.classList.add('d-none'); // Oculta el spinner


    } else {
        //alert(result.error || "Error al guardar la nota.");
    }
});












async function loadgrupos() {
    //const modalElement1 = document.getElementById(`actual-capac`);
    const idUserx = document.getElementById('entidad').value;
    const now = new Date();

    try {
        // ✅ Agregar timestamp para evitar cache
        const timestamp = new Date().getTime();
        const response = await fetch(`../../backend/controller/usuario/capacitacion/conseguir_grupo.php?idUserx=${encodeURIComponent(idUserx)}&t=${timestamp}`, {
            headers: {
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const grupo = await response.json();
        console.log('📋 grupos cargadas:', grupo.length);
        
        const notesContainer = document.getElementById('notesContainer');
        notesContainer.innerHTML = "";

        grupo.forEach((note) => {
            const idGrupo = note["id_grupo"];
            const nombreGrupo = note["nombre_grupo"];
            const tipoGrupo = note["tipo_grupo"];
            const habilitacion = note["habilitado"];

            let estadoHTML = '';


            if(habilitacion === 'si') {
                estadoHTML = `Inabilitar`;

            }else if(habilitacion === 'no') {
                estadoHTML = `Habilitar`;


            }
           // modalElement1.dataset.id = idCapacitacion;
           // modalElement1.name = `actual-capac-${idCapacitacion}`;
          

           // let estadoHTML = '';
           // let deleteButtonDisplay = ''; // Variable para controlar la visibilidad del botón borrar

       /*     if (now > fechaFinCapacitacion) {
                estadoHTML = `<span class="badge bg-primary-soft ms-2" type="button" id="filt_Todos">Cerrado</span>`;
                actualizarEstadoCapacitacion(note.id_capacitacion, 'Cerrado');
            } else if (now >= fechaInicioCapacitacion && now < fechaFinCapacitacion) {
                estadoHTML = `<span class="badge bg-warning-soft ms-2" type="button" id="filt_EnCurso">Proceso</span>`;
                actualizarEstadoCapacitacion(note.id_capacitacion, 'Proceso');
                deleteButtonDisplay = 'style="display: none;"'; 

            } else {
                estadoHTML = `<span class="badge bg-success-soft ms-2" type="button" id="filt_Habilitado">Espera</span>`;
                actualizarEstadoCapacitacion(note.id_capacitacion, 'Espera');
            }*/

            const noteElement = document.createElement("div");
            noteElement.classList.add("col-lg-6", "col-md-6", "col-sm-6", "col-8", "g-4");

            noteElement.innerHTML = `
            <div class="card car-${note.id_grupo}"  data-id="${note.id_grupo}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center my-2">
                     
                           <span class="text-secondary w-50" style="word-wrap: break-word;"  data-id="${note.id_grupo}">${note.nombre_grupo} </span>
                        
                        <div class="text-end">
                           
                            ${note.habilitado === 'si'
                            ? `<span class="badge bg-primary-soft" data-id="${note.id_grupo}">Habilitado</span>`
                            : (note.habilitado === 'no'
                                ? `<span class="badge bg-secondary-soft" data-id="${note.id_grupo}">Inabilitado</span>`
                                : `<span class="badge bg-secondary-soft" data-id="${note.id_grupo}"></span>`
                            ) 
                        }
                      
                        </div>
                    </div>
                  
                    <div class="d-flex justify-content-between align-items-center my-2">
                    </div>
                  

                   

                    <div class="d-flex flex-wrap" id="collabo-${note.id_grupo}" >
                    </div>
        
                    <div class="d-flex flex-wrap mt-2">
                    </div>
        
                    <div class="d-flex justify-content-between mt-3">
                                                                 <span class="badge text-bg-secondary text-center pt-2" style="visibility:hidden;">1</span>

        
                        <div class="dropdown ">
                            <a class="btn border-0 btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-haspopup="true" aria-expanded="false" >
                                <i class="bi bi-three-dots-vertical"></i>
                            </a>
                            <ul class="dropdown-menu p-2">
                                <li><button class="dropdown-item add-tag-btn" data-id="${note.id_grupo}">Editar</button></li>
                                <li><button class="dropdown-item add-habilita-btn" data-id="${note.id_grupo}">${estadoHTML}</button></li>
                                <li class="elimionir" ><button class="dropdown-item delete-btn" data-id="${note.id_grupo}">Borrar</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        `;

         const cardElement = noteElement.querySelector(".card");
          cardElement.addEventListener("click", () => {
              const hiddenElements = cardElement.querySelectorAll(".toggle-content");

              hiddenElements.forEach(element => {
                  if (element.style.display === 'none') {
                      element.style.display = 'block'; 
                  } else {
                      element.style.display = 'none';
                  }
              });
          }); 

            // --- *** Añadir Event Listener para detener la propagación en el Dropdown *** ---
            const dropdownElement = noteElement.querySelector(".dropdown"); // Selecciona el div del dropdown
            if (dropdownElement) { // Asegúrate de que el dropdown exista
                dropdownElement.addEventListener("click", (event) => {
                    event.stopPropagation(); // Detiene el evento click para que no llegue a la card
                });
            }
            // --- *** Fin del Event Listener del Dropdown *** ---



            // Solo agregar el event listener si el botón no está oculto
                 noteElement.querySelector(".delete-btn").addEventListener("click", async () => {
                     await deleteGrupo(note.id_grupo);
                     loadgrupos();
                 });
            



    noteElement.querySelector(".add-habilita-btn").addEventListener("click", async () => {
                     await actualizarGrupo(note.id_grupo);
                     loadgrupos();
                 });


            noteElement.querySelector(".add-tag-btn").addEventListener("click", (event) => {
                const editButton = event.target; // Get the clicked button
                editButton.disabled = true; // Disable the button
            
              
            
                // Call the function that opens the modal
                openAddGrupoModal(note.id_grupo);
            
              
                // Wrap the re-enable and remove spinner logic in a setTimeout
                setTimeout(() => {
                    // Re-enable button and remove spinner after 1000ms
                    editButton.disabled = false; // Re-enable the button
                  
                }, 2500); // Delay of 1000 milliseconds (1 second)
            });


       const collaboContainer = noteElement.querySelector(`#collabo-${note.id_grupo}`);
// const materialesContainer = noteElement.querySelector(`#materiales-${note.id_capacitacion}`); // Esta variable no se usa
console.log(tipoGrupo); // Esto te mostrará si tipoGrupo es 'equipo' o 'individual'

const addedAliases = new Set(); // Conjunto para almacenar los alias ya agregados

note.colaboradores.forEach(colaborador => {
    let initials = '';
    let fullName = '';
    let badgeHTML = '';

    if (tipoGrupo === 'equipo') {
        // Si el tipo de grupo es 'equipo', usamos el alias del colaborador
        if (colaborador.alias !== null) {
            // Verificar si el alias ya fue agregado
            if (addedAliases.has(colaborador.alias)) {
                return; // Saltar esta iteración si el alias ya existe
            }
            initials = colaborador.alias.substring(0, 2).toUpperCase();
            fullName = colaborador.alias; // Usamos el alias como el nombre completo para el tooltip
            addedAliases.add(colaborador.alias); // Añadir el alias al conjunto
        } else {
            // Manejo por si un colaborador en un grupo 'equipo' no tiene alias
            // Podrías poner un valor por defecto o usar el nombre/apellido si están disponibles
            initials = `${colaborador.nombre ? colaborador.nombre.charAt(0) : ''}${colaborador.apellido ? colaborador.apellido.charAt(0) : ''}`.toUpperCase();
            fullName = `${colaborador.nombre || ''} ${colaborador.apellido || ''}`.trim();

            // Si decides no mostrar alias duplicados, también puedes aplicar una lógica similar aquí
            // para evitar mostrar nombres/apellidos duplicados si no hay alias.
            // Por ejemplo, podrías usar una combinación de nombre y apellido como clave para el Set.
            const uniqueKey = `${colaborador.nombre || ''}-${colaborador.apellido || ''}`;
            if (addedAliases.has(uniqueKey)) {
                return;
            }
            addedAliases.add(uniqueKey);
        }
    } else if (tipoGrupo === 'individual') {
        // Si el tipo de grupo es 'individual', usamos nombre y apellido del empleado
        fullName = `${colaborador.nombre || ''} ${colaborador.apellido || ''}`.trim();
        // Asegúrate de que empleado_nombre y apellido existan en tu objeto colaborador
        // Si vienen como 'nombre' y 'apellido' de la base de datos, úsalos directamente
        initials = `${colaborador.nombre ? colaborador.nombre.charAt(0) : ''}${colaborador.apellido ? colaborador.apellido.charAt(0) : ''}`.toUpperCase();
    }

    // Si se pudieron obtener las iniciales y el nombre completo
    if (initials && fullName) {
        badgeHTML = `<span style="display:none" class="badge bg-secondary-soft me-1 toggle-content" data-bs-toggle="tooltip" title="${fullName}">${initials}</span>`;
        collaboContainer.innerHTML += badgeHTML;
    }
});
            notesContainer.appendChild(noteElement);
        });

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    } catch (error) {
        console.error('❌ Error cargando capacitaciones:', error);
        Swal.fire({
            title: 'Error',
            text: 'No se pudieron cargar las capacitaciones',
            icon: 'error'
        });
    }
}







document.addEventListener('DOMContentLoaded', function() {
    // Función para obtener un parámetro GET por su nombre
    function obtenerParametroGet(nombre) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(nombre);
    }
       

    // Verificar si las variables existen en los parámetros GET
    const otraVariable = obtenerParametroGet('mi_variable');
    const otraVariable2 = obtenerParametroGet('grupo_variable'); // Obtener otraVariable2

    const elementoPrincipal = document.querySelector('.principal');
    const elementoCrearCapacitacion = document.querySelector('.crearCapacitacion');
    const elementoMiCapacitacion = document.querySelector('.crearGrupo'); // Obtener elementoMiCapacitacion

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










 // Eliminar nota
async function deleteGrupo(id) {

  const getTheme = () => document.documentElement.getAttribute("data-bs-theme") || "light";
        const tema = getTheme(); // Detecta el tema actual



    // ✅ Confirmar antes de eliminar
    const confirmResult = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '',
        cancelButtonColor: '',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: tema === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
            color: tema === "dark" ? "#ffffff" : "#333333" // Texto adaptativo
    });

    if (!confirmResult.isConfirmed) {
        return;
    }

    try {
        console.log('🗑️ Eliminando capacitación ID:', id);
        
        const response = await fetch("../../backend/controller/usuario/capacitacion/borrar_grupo.php", {
            method: "POST",
            body: JSON.stringify({ id }),
            headers: { 
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, no-store, must-revalidate",
                "Pragma": "no-cache",
                "Expires": "0"
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('📄 Respuesta del servidor:', result);

        if (result.success) {
            // ✅ Éxito
            Swal.fire({
                title: '¡Eliminado!',
                text: 'El grupo ha sido eliminado correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: tema === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
            color: tema === "dark" ? "#ffffff" : "#333333" // Texto adaptativo
            });

            // ✅ IMPORTANTE: Recargar la lista para mostrar cambios
            loadgrupos();
        } else {
            // ❌ Error del servidor
            Swal.fire({
                title: 'Error al eliminar',
                text: result.error || 'Error desconocido',
                icon: 'error'
            });
        }

    } catch (error) {
        console.error('❌ Error:', error);
        
        if (!navigator.onLine) {
            Swal.fire({
                title: 'Sin conexión',
                text: 'No hay conexión a internet.',
                icon: 'warning'
            });
        } else {
            Swal.fire({
                title: 'Error inesperado',
                text: `Error: ${error.message}`,
                icon: 'error'
            });
        }
    }
}













 // Actualizar grupo
async function actualizarGrupo(id) {

  const getTheme = () => document.documentElement.getAttribute("data-bs-theme") || "light";
        const tema = getTheme(); // Detecta el tema actual


    try {
        console.log('🗑️ Actualizada capacitación ID:', id);
        
        const response = await fetch("../../backend/controller/usuario/capacitacion/habilta_grupo.php", {
            method: "POST",
            body: JSON.stringify({ id }),
            headers: { 
                "Content-Type": "application/json",
                "Cache-Control": "no-cache, no-store, must-revalidate",
                "Pragma": "no-cache",
                "Expires": "0"
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('📄 Respuesta del servidor:', result);

        if (result.success) {
            // ✅ Éxito
            Swal.fire({
                title: '¡Actualizado!',
                text: 'El grupo ha sido Habilitado/Inabilitado Correctamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                background: tema === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
            color: tema === "dark" ? "#ffffff" : "#333333" // Texto adaptativo
            });

            // ✅ IMPORTANTE: Recargar la lista para mostrar cambios
            loadgrupos();
        } else {
            // ❌ Error del servidor
            Swal.fire({
                title: 'Error al actualizar',
                text: result.error || 'Error desconocido',
                icon: 'error'
            });
        }

    } catch (error) {
        console.error('❌ Error:', error);
        
        if (!navigator.onLine) {
            Swal.fire({
                title: 'Sin conexión',
                text: 'No hay conexión a internet.',
                icon: 'warning'
            });
        } else {
            Swal.fire({
                title: 'Error inesperado',
                text: `Error: ${error.message}`,
                icon: 'error'
            });
        }
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









async function openAddGrupoModal(capacitacionId) {

   
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
    const response = await fetch(`../../backend/controller/usuario/capacitacion/conseguir_grupo_por_id.php?id=${capacitacionId}`);
    // Verificar si la respuesta es OK antes de intentar parsear JSON
    if (!response.ok) {
        console.error(`Error al obtener datos de la capacitación ${capacitacionId}: ${response.status} ${response.statusText}`);
        mostrarAlerta('error', 'Error', 'No se pudieron cargar los datos de la capacitación.');
        return; // Salir si la petición falló
    }
     const grupoData = await response.json(); // Renombrado a grupoData

    if (grupoData && grupoData.length > 0) {
        const grupo = grupoData[0]; // Renombrado a grupo
        const grupoId = grupo.id_grupo; // Usar id_grupo
        const tipoGrupo = grupo.tipo_grupo; // Obtener el tipo de grupo
        const nombreGrupo = grupo.nombre_grupo; // Obtener el nombre del grupo

        let optionsHtml = '';
        let isDisabled = false; // Variable para controlar si el select debe estar deshabilitado

        if (tipoGrupo === 'individual' && grupo.empleados_disponibles) {
            if (grupo.empleados_disponibles.length === 0) {
                isDisabled = true; // Deshabilitar si no hay empleados disponibles
            } else {
                // Si el tipo es 'individual', poblamos con empleados disponibles
                optionsHtml = grupo.empleados_disponibles.map(empleado => `
                    <option value="${empleado.id_empleado}">${empleado.nombre} ${empleado.apellido}</option>
                `).join('');
            }
        } else if (tipoGrupo === 'equipo' && grupo.equipos_disponibles) {
            if (grupo.equipos_disponibles.length === 0) {
                isDisabled = true; // Deshabilitar si no hay equipos disponibles
            } else {
                // Si el tipo es 'equipo', poblamos con equipos disponibles
                optionsHtml = grupo.equipos_disponibles.map(equipo => `
                    <option value="${equipo.id_equipo}">${equipo.alias}</option>
                `).join('');
            }
        }

        // Determinar el atributo 'disabled' para el select
        const disabledAttribute = isDisabled ? 'disabled' : '';

        // Generar el HTML completo del modal de forma dinámica
        const modalHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="actualCapacLabel-${grupoId}" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="actualCapacLabel-${grupoId}">Actualizar Grupo</h5>
                            <button type="button" class="btn-close" aria-label="Close"></button> 
                        </div>
                        <div class="modal-body">
                            <form action="" id="noteForm-${grupoId}">
                                <div>
                                    <div class="mb-3 tema">
                                        <label for="grupo-${grupoId}" class="form-label">Nombre del Grupo</label>
                                        <input type="text" class="form-control" id="grupo-${grupoId}" name="grupo" placeholder="Escribir grupo" value="${nombreGrupo}">
                                    </div>

                                    <input type="hidden" id="relacionIdHidden-${grupoId}" value="${grupo.tipo_grupo}">

                                    <input type="hidden" id="capacitacionIdHidden-${grupoId}" value="${grupo.id_grupo}">
                                    <input type="hidden" id="entidadHidden-${grupoId}" value="${document.getElementById('entidad').value}">

                                    <div class="mb-3">
                                        <label for="drawfs-${grupoId}" class="form-label">
                                            ${tipoGrupo === 'individual' ? 'Empleados disponibles' : 'Equipos disponibles'}
                                        </label>
                                          <select multiple name="drawfs" id="drawfs-${grupoId}" class="form-control" ${disabledAttribute}>
                                            ${optionsHtml}
                                        </select>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div id="usuarios-seleccionados-container-${grupoId}" class="mt-2">
                                            <h6>Empleados/equipos seleccionados:</h6>
                                            <ul id="lista-seleccionados-${grupoId}" class="list-unstyled">
                                                ${(() => {
                const addedAliases = new Set(); // Para llevar un registro de los alias ya mostrados
                return grupo.colaborador.map(colaborador => {
                    if (colaborador.tipo_relacion === 2) { // Si es un equipo
                        if (colaborador.alias && !addedAliases.has(colaborador.alias)) {
                            addedAliases.add(colaborador.alias); // Agrega el alias al Set para evitar repeticiones
                            return `<li class="badge bg-primary-soft">${colaborador.alias}</li>`;
                        }
                        return ''; // Si ya se mostró el alias o no hay alias, no devuelve nada
                    } else if (colaborador.tipo_relacion === 1) { // Si es individual
                        // Muestra el nombre, apellido y alias entre paréntesis como siempre
                        return `<li class="badge bg-primary-soft">${colaborador.nombre} ${colaborador.apellido} (${colaborador.alias || 'Sin Alias'})</li>`;
                    }
                    return ''; // Para cualquier otro caso de tipo_relacion
                }).join('');
            })()}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" id="closeModalBtn-${grupoId}" class="btn btn-secondary" >Cerrar</button> 
                            <div class="d-flex align-items-center ms-2"> 
                                <button type="button" id="saveNoteTagBtn-${grupoId}" class="btn btn-primary">Guardar</button>
                                
                                <div class="spinner-border text-primary ms-2 d-none" role="status" id="spinnerActualizar-${grupoId}">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
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

    

        const saveNoteTagBtn = newModalElement.querySelector(`#saveNoteTagBtn-${grupoId}`);
        const form = newModalElement.querySelector(`#noteForm-${grupoId}`);





         // Referencia al spinner dentro de este modal específico
         const spinnerActualizar = newModalElement.querySelector(`#spinnerActualizar-${grupoId}`);

         // Inicializar Select2 en el nuevo elemento <select>
        // $('.js-example-basic-multiple').select2();
         // Obtener los botones de cierre por su clase/ID
        const closeButton = newModalElement.querySelector(`#closeModalBtn-${grupoId}`); // Botón "Cerrar" en el footer
        const headerCloseButton = newModalElement.querySelector('.modal-header .btn-close'); // Botón "X" en el header

         // Agregar event listener para el botón "Guardar" (solo a elementos del nuevo modal)
         if (saveNoteTagBtn) {
             console.log(`[DEBUG] Añadiendo listener click a botón Guardar para modal ID: ${modalId}`); // Log para depuración
             saveNoteTagBtn.addEventListener('click', async function() {
                 console.log(`[DEBUG] Click en botón Guardar para modal ID: ${modalId}. Iniciando guardado...`); // Log para depuración
                 // ... (lógica de guardar - sin cambios aquí) ...


  // --- Mostrar spinner e inhabilitar botón ---
  saveNoteTagBtn.disabled = true;
  spinnerActualizar.classList.remove('d-none'); // Muestra el spinner


            
            
              
            const colaboradoresSelect = newModalElement.querySelector(`#drawfs-${grupoId}`); // Get the select element
            const grupoInput = newModalElement.querySelector(`#grupo-${grupoId}`); // Get the input element

            // Get the value of the group name input
            const nombreGrupoValue = grupoInput.value;

            // Get the selected values from the multi-select
            const selectedColaboradores = Array.from(colaboradoresSelect.selectedOptions).map(option => option.value);

            const currentrelacionIdInput = newModalElement.querySelector(`#relacionIdHidden-${grupoId}`);
            const currentCapacitacionIdInput = newModalElement.querySelector(`#capacitacionIdHidden-${grupoId}`);
            const currentEntidadIdInput = newModalElement.querySelector(`#entidadHidden-${grupoId}`);

            const currentCapacitacionId = currentCapacitacionIdInput ? currentCapacitacionIdInput.value : '';
            const entidadId = currentEntidadIdInput ? currentEntidadIdInput.value : (document.getElementById('entidad') ? document.getElementById('entidad').value : '');
            const currentrelacionId = currentrelacionIdInput ? currentrelacionIdInput.value : '';

            const formData = new FormData();
            formData.append('idGrupo', currentCapacitacionId);
            formData.append('nombreGrupo', nombreGrupoValue); // Correct: append the value
            formData.append('tipoRelacion', currentrelacionId);

            // Correct: stringify the array of selected collaborators
            formData.append('colaboradores', JSON.stringify(selectedColaboradores));

            formData.append('usuarioActual', entidadId);
                

                 const updateResponse = await fetch('../../backend/controller/usuario/capacitacion/actualizar_grupo.php', {
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
                     mostrarAlerta('success', 'grupo Actualizado.', '¡Listo!');
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
                     loadgrupos();


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