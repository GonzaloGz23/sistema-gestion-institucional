



document.addEventListener('DOMContentLoaded', function() {

    loadnuevaCapacitacion();
    loadfinalizadaCapacitacion();


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




async function loadnuevaCapacitacion() {

    const zzidUserx = document.getElementById('entidades').value;
    const zznow = new Date();

    const zzresponse = await fetch(`../../backend/controller/usuario/capacitacion/nueva_capacitacion.php?idUserx=${encodeURIComponent(zzidUserx)}`);
    const zzcapacitacion = await zzresponse.json();
    const zznotesContainer = document.getElementById('empleadoCapacitacion');
    zznotesContainer.innerHTML = "";

    zzcapacitacion.forEach((note) => {
        const zzidCapacitacion = note["id_capacitacion"];

        const zzfechaInicioOriginal = note["fecha-inicio"];
        let zzformattedDateInicio = "Ninguno";
        let zzformattedHoraInicio = "";
        if (zzfechaInicioOriginal) {
            const zzdateInicio = new Date(zzfechaInicioOriginal);
            const zzdia = zzdateInicio.getDate().toString().padStart(2, '0');
            const zzmes = (zzdateInicio.getMonth() + 1).toString().padStart(2, '0');
            const zzanio = zzdateInicio.getFullYear();
            const zzhora = zzdateInicio.getHours().toString().padStart(2, '0');
            const zzminuto = zzdateInicio.getMinutes().toString().padStart(2, '0');
            zzformattedDateInicio = `${zzdia}/${zzmes}`;
            zzformattedHoraInicio = `${zzhora}:${zzminuto}`;
        }
        // formattedDateFin no se usa, se calcula directamente en el if
        const zzfechaInicioCapacitacion = new Date(note["fecha-inicio"]);
        const zzfechaFinCapacitacion = new Date(note["fecha-fin"]);

        let zzestadoHTML = '';
        let zzdeleteButtonDisplay = ''; // Variable para controlar la visibilidad del botón borrar

        if (zznow > zzfechaFinCapacitacion) {
            zzestadoHTML = `<span class="badge bg-primary-soft ms-2" type="button" id="filt_Todos">Cerrado</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Cerrado');
        } else if (zznow >= zzfechaInicioCapacitacion && zznow < zzfechaFinCapacitacion) {
            zzestadoHTML = `<span class="badge bg-warning-soft ms-2" type="button" id="filt_EnCurso">Proceso</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Proceso');
            zzdeleteButtonDisplay = 'style="display: none;"'; // Ocultar el botón si está en Proceso

        } else {
            zzestadoHTML = `<span class="badge bg-success-soft ms-2" type="button" id="filt_Habilitado">Espera</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Espera');
        }

        const zznoteElement = document.createElement("div");
        zznoteElement.classList.add("col-lg-6", "col-md-6", "col-sm-6", "col-xs-6");

        zznoteElement.innerHTML = `
        <div class="card car-${note.id_capacitacion}"  data-id="${note.id_capacitacion}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center my-2">
                    <div>
                       <span class="text-secondary "   data-id="${note.id_capacitacion}">${zzformattedDateInicio} ${zzformattedHoraInicio}</span>
                    </div>
                    <div class="text-end">
                        ${zzestadoHTML}
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

                 <div class="text-start my-2  toggle-content" id="materiales-${note.id_capacitacion}" style="display:none" >
                   
                </div>

                <div class="d-flex flex-wrap mt-2">
                </div>

                <div class="d-flex justify-content-between mt-3">
                                                                                             <span class="badge text-bg-secondary text-center pt-2">${note.modalidad}</span>

                <span class="badge bg-info-soft " type="button" data-id="${note.id_capacitacion}" id="obligac">${note.obligacion}</span>

                </div>
            </div>
        </div>
        <b style="visibility:hidden;">a</b>
    `;


         const materialesContainer = zznoteElement.querySelector(`#materiales-${note.id_capacitacion}`); // Esta variable no se usa


  // --- *** Añadir Event Listener a la Card *** ---
  const zzcardElement = zznoteElement.querySelector(".card");
  zzcardElement.addEventListener("click", () => {
      // Seleccionar todos los elementos con la clase 'toggle-content' dentro de esta card
      const zzhiddenElements = zzcardElement.querySelectorAll(".toggle-content");

      zzhiddenElements.forEach(element => {
          // Alternar el estilo display
          if (element.style.display === 'none') {
              element.style.display = 'block'; // O flex, dependiendo del contenedor
          } else {
              element.style.display = 'none';
          }
      });
  });
  // --- *** Fin del Event Listener *** ---


  // Bloque de materiales comentado en el original
        note.materiales.forEach((material, index) => {

            const fullPublicFilePath2 = `${window.location.origin}/sistemaInstitucional/uploads/capacitacion/${material.nombre_material}`;
            const googleViewerUrl2 = `https://docs.google.com/viewer?url=${encodeURIComponent(fullPublicFilePath2)}&embedded=true`;

            const previewLinkBase2 = `../../uploads/capacitacion/${material.nombre_material}`;
            const downloadLink = `../../uploads/capacitacion/${material.nombre_material}`;
            const materialNumber = index + 1;

// Obtener la extensión del nombre del archivo
const fileExtension2 = material.nombre_material.split('.').pop().toLowerCase();

// Array de extensiones de imagen comunes
const imageExtensions2 = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

// Determinar la URL de vista previa condicionalmente
let previewLink2 = previewLinkBase2;
if (!imageExtensions2.includes(fileExtension2) && window.location.origin !== 'http://localhost') {
  previewLink2 = googleViewerUrl2;
}


            const materialLinkHTML = `
                <p>
                    <a href="${previewLink2}" target="_blank" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Ver Material-${materialNumber}">
                        <i class="bi bi-eye-fill me-1"></i> 
                    </a>
                    <a href="${downloadLink}" target="_blank" download="${material.nombre_material}" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Descargar Material-${materialNumber}">
                        <i class="bi bi-download-fill me-1"></i> Descargar Material ${materialNumber}
                    </a>
                </p>
            `;
            materialesContainer.innerHTML += materialLinkHTML;
        });


        zznotesContainer.appendChild(zznoteElement);
    });

    const zztooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...zztooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}



















async function loadfinalizadaCapacitacion() {

    const xxzzidUserx = document.getElementById('entidades').value;
    const xxzznow = new Date();

    const xxzzresponse = await fetch(`../../backend/controller/usuario/capacitacion/finalizada_capacitacion.php?idUserx=${encodeURIComponent(xxzzidUserx)}`);
    const xxzzcapacitacion = await xxzzresponse.json();
    const xxzznotesContainer = document.getElementById('finalizadaCapacitacion');
    xxzznotesContainer.innerHTML = "";

    xxzzcapacitacion.forEach((note) => {
        const xxzzidCapacitacion = note["id_capacitacion"];

      // **Inicio: Código agregado para fecha-fin**
    const xxzzfechaFinOriginal = note["fecha-fin"];
    let zzformattedDateFin = "Ninguno"; // Inicializar con "Ninguno"
    let zzformattedHoraFin = ""; // Inicializar vacío
    if (xxzzfechaFinOriginal) {
        const xxzzdateFin = new Date(xxzzfechaFinOriginal);
        const xxzzdiaFin = xxzzdateFin.getDate().toString().padStart(2, '0');
        const xxzzmesFin = (xxzzdateFin.getMonth() + 1).toString().padStart(2, '0');
        // const xxzzanioFin = xxzzdateFin.getFullYear(); // No se usa en el formato DD/MM
        const xxzzhoraFin = xxzzdateFin.getHours().toString().padStart(2, '0');
        const xxzzminutoFin = xxzzdateFin.getMinutes().toString().padStart(2, '0');
        zzformattedDateFin = `${xxzzdiaFin}/${xxzzmesFin}`;
        zzformattedHoraFin = `${xxzzhoraFin}:${xxzzminutoFin}`;
    }
    // **Fin: Código agregado para fecha-fin**
        // formattedDateFin no se usa, se calcula directamente en el if
        const xxzzfechaInicioCapacitacion = new Date(note["fecha-inicio"]);
        const xxzzfechaFinCapacitacion = new Date(note["fecha-fin"]);

        let zzestadoHTML = ''; // This was already let, keep it as is
        let zzdeleteButtonDisplay = ''; // Variable para controlar la visibilidad del botón borrar, keep as let

        if (xxzznow > xxzzfechaFinCapacitacion) {
            zzestadoHTML = `<span class="badge bg-primary-soft ms-2" type="button" id="filt_Todos">Cerrado</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Cerrado');
        } else if (xxzznow >= xxzzfechaInicioCapacitacion && xxzznow < xxzzfechaFinCapacitacion) {
            zzestadoHTML = `<span class="badge bg-warning-soft ms-2" type="button" id="filt_EnCurso">Proceso</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Proceso');
            zzdeleteButtonDisplay = 'style="display: none;"'; // Ocultar el botón si está en Proceso

        } else {
            zzestadoHTML = `<span class="badge bg-success-soft ms-2" type="button" id="filt_Habilitado">Espera</span>`;
            actualizarEstadoCapacitacion(note.id_capacitacion, 'Espera');
        }

        const xxzznoteElement = document.createElement("div");
        xxzznoteElement.classList.add("col-lg-6", "col-md-6", "col-sm-6", "col-xs-6");

        xxzznoteElement.innerHTML = `
        <div class="card car-${note.id_capacitacion} mb-2"  data-id="${note.id_capacitacion}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center my-2">
                    <div>
                       <span class="text-secondary "   data-id="${note.id_capacitacion}">${zzformattedDateFin} ${zzformattedHoraFin}</span>
                    </div>
                    <div class="text-end">
                        ${zzestadoHTML}
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


                   <div class="text-start my-2  toggle-content" id="materiales-${note.id_capacitacion}" style="display:none" >

                 </div>


                <div class="d-flex flex-wrap mt-2">
                </div>

                <div class="d-flex justify-content-between mt-3">
                <span class="badge text-bg-secondary text-center pt-2">${note.modalidad}</span>

                <span class="badge bg-info-soft " type="button" data-id="${note.id_capacitacion}" id="obligac">${note.obligacion}</span>

                </div>
            </div>
        </div>
                <b style="visibility:hidden;">a</b>

    `;


        // Esta variable no se usa
        // const materialesContainer = zznoteElement.querySelector(`#materiales-${note.id_capacitacion}`);


        // --- *** Añadir Event Listener a la Card *** ---
        const xxzzcardElement = xxzznoteElement.querySelector(".card");
        xxzzcardElement.addEventListener("click", () => {
            // Seleccionar todos los elementos con la clase 'toggle-content' dentro de esta card
            const xxzzhiddenElements = xxzzcardElement.querySelectorAll(".toggle-content");

            xxzzhiddenElements.forEach(element => {
                // Alternar el estilo display
                if (element.style.display === 'none') {
                    element.style.display = 'block'; // O flex, dependiendo del contenedor
                } else {
                    element.style.display = 'none';
                }
            });
        });
        // --- *** Fin del Event Listener *** ---


        // Bloque de materiales comentado en el original
        const xxmaterialesContainer = xxzznoteElement.querySelector(`#materiales-${note.id_capacitacion}`); // Get the container inside this specific noteElement
        note.materiales.forEach((material, index) => {
            const fullPublicFilePath3 = `${window.location.origin}/sistemaInstitucional/uploads/capacitacion/${material.nombre_material}`;
            const googleViewerUrl3 = `https://docs.google.com/viewer?url=${encodeURIComponent(fullPublicFilePath3)}&embedded=true`;

            const previewLinkBase3 = `../../uploads/capacitacion/${material.nombre_material}`;
            const downloadLink = `../../uploads/capacitacion/${material.nombre_material}`;
            const materialNumber = index + 1;


// Obtener la extensión del nombre del archivo
const fileExtension3 = material.nombre_material.split('.').pop().toLowerCase();

// Array de extensiones de imagen comunes
const imageExtensions3 = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

// Determinar la URL de vista previa condicionalmente
let previewLink3 = previewLinkBase3;
if (!imageExtensions3.includes(fileExtension3) && window.location.origin !== 'http://localhost') {
  previewLink3 = googleViewerUrl3;
}

            const xxmaterialLinkHTML = `
                <p>
                    <a href="${previewLink3}" target="_blank" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Ver Material-${materialNumber}">
                        <i class="bi bi-eye-fill me-1"></i>
                    </a>
                    <a href="${downloadLink}" target="_blank" download="${material.nombre_material}" class="link-primary link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" title="Descargar Material-${materialNumber}">
                        <i class="bi bi-download-fill me-1"></i> Descargar Material ${materialNumber}
                    </a>
                </p>
            `;
            xxmaterialesContainer.innerHTML += xxmaterialLinkHTML;
        });


        xxzznotesContainer.appendChild(xxzznoteElement);
    });

    const xxzztooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...xxzztooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}




