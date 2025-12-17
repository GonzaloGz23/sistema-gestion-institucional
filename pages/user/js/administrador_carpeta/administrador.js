





function ocultarDuplicadosPorDataId() {
  const elementos = document.querySelectorAll('[data-id]');
  const conteoPorId = {};

  // Contar la frecuencia de cada data-id
  elementos.forEach(elemento => {
    const dataId = elemento.dataset.id;
    conteoPorId[dataId] = (conteoPorId[dataId] || 0) + 1;
  });

  // Ocultar los duplicados
  for (const dataId in conteoPorId) {
    if (conteoPorId[dataId] > 1) {
      const elementosConMismoId = document.querySelectorAll(`[data-id="${dataId}"]`);
      let encontradoUno = false;

      elementosConMismoId.forEach(elemento => {
        if (!encontradoUno) {
          elemento.style.display = ''; // Mostrar el primero encontrado
          encontradoUno = true;
        } else {
          elemento.style.display = 'none'; // Ocultar los demás
        }
      });
    }
  }
}

// Llama a la función cuando el contenido dinámico se carga o cuando sea necesario
document.addEventListener('DOMContentLoaded', ocultarDuplicadosPorDataId);



// administrador.js

//cargar el contenido de la carpeta al cargar la página
async function cargarContenidoDinamico() {
  try {
    const filesContainer = document.getElementById('files-container');
    //const eliminarDropdown = document.getElementById('elim'); // Obtén el elemento con el id "elim"

    if (!filesContainer) {
      console.error('Elemento files-container no encontrado.');
      return;
    }
    const idUseri = document.getElementById('usuarioo').value;
    // Obtener el parámetro 'p' de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const p = urlParams.get('p');

    let url = '../../backend/controller/usuario/administrador-archivo/conseguir_carpeta.php'; // Archivo PHP para obtener los datos

    if (p) {
      url += `?p=${p}&idUser=${idUseri}`; // Agregar el parámetro 'p' si existe
    }

    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.text(); // Obtener la respuesta como texto HTML
    filesContainer.innerHTML = data; // Insertar el HTML en el contenedor

  /*  const colaboradorInputs = document.querySelectorAll('.es_colaborador_input');
 let mostrarEliminar = false;
 colaboradorInputs.forEach(input => {
if (input.value === "0") {
 mostrarEliminar = true;
}
 console.log(mostrarEliminar);
 });

 if (mostrarEliminar) {
 eliminarDropdown.innerHTML = '<button class="dropdown-item text-danger" id="opcionEliminar">Eliminar</button>';
 } else {
 eliminarDropdown.innerHTML = '<button class="dropdown-item text-danger" id="opcionEliminar"></button>'; // O podrías ocultar el elemento si es necesario
 }*/

   

    // Inicializar tooltips de Bootstrap después de cargar el contenido
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Adjuntar los listeners después de cargar el contenido
    adjuntarListeners();


 // Llamar a la función para ocultar duplicados después de cargar el contenido
 ocultarDuplicadosPorDataId();

  } catch (error) {
    console.error('Error al cargar contenido dinámico:', error);
    document.getElementById('files-container').innerHTML = 'Error al cargar el contenido.';
  }
}






document.addEventListener('DOMContentLoaded', function() {

  // Función para cargar el contenido dinámico del modal (contenido-carpeta y footer-carpeta)
  async function cargarContenidoModal() {
    try {
      const contenidoCarpeta = document.getElementById('contenido-carpeta');
      const footerCarpeta = document.getElementById('footer-carpeta');

      if (!contenidoCarpeta || !footerCarpeta) {
        console.error('Elementos contenido-carpeta o footer-carpeta no encontrados.');
        return;
      }

      // Obtener el ID del usuario (asumiendo que está disponible globalmente o en un elemento oculto)
      const usuarioo = document.getElementById('usuarioo').value;
      const carpetapadr = document.getElementById('carpetapadr').value;

      // Construir la URL para obtener el contenido del modal
      let url = '../../backend/controller/usuario/administrador-archivo/carpeta_modal.php';

      // Agregar parámetros a la URL si es necesario (por ejemplo, id de usuario)
      url += `?usuario=${usuarioo}&padre=${carpetapadr}`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json(); // Esperar una respuesta JSON con contenido HTML

      if (data && data.contenido && data.footer) {
        contenidoCarpeta.innerHTML = data.contenido;
        footerCarpeta.innerHTML = data.footer;

        // Inicializar el evento click para el botón "Guardar"
        document.getElementById('guardarcarpeta').addEventListener('click', guardarCarpeta);

      } else {
        console.error('Respuesta JSON incorrecta o incompleta.');
        contenidoCarpeta.innerHTML = '<p>Error al cargar el contenido del modal.</p>';
        footerCarpeta.innerHTML = '';
      }

    } catch (error) {
      console.error('Error al cargar el contenido del modal:', error);
      document.getElementById('contenido-carpeta').innerHTML = '<p>Error al cargar el contenido del modal.</p>';
      document.getElementById('footer-carpeta').innerHTML = '';
    }
  }








  async function cargarContenidoModalarchivo() {
    try {
      const contenidoArchivo = document.getElementById('contenido-archivo');
      const footerArchivo = document.getElementById('button-archivo');

      if (!contenidoArchivo || !footerArchivo) {
        console.error('Elementos contenido-archivo o footer-archivo no encontrados.');
        return;
      }
     // Obtener el ID del usuario (asumiendo que está disponible globalmente o en un elemento oculto)
      const usuarios = document.getElementById('usuarioo').value;
      const carpetapadra = document.getElementById('carpetapadr').value;

      // Construir la URL para obtener el contenido del modal
      let url = '../../backend/controller/usuario/administrador-archivo/archivo_modal.php';

      // Agregar parámetros a la URL si es necesario (por ejemplo, id de usuario)
      url += `?usuario=${usuarios}&padre=${carpetapadra}`;

      const response = await fetch(url);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json(); // Esperar una respuesta JSON con contenido HTML

      if (data && data.contenido && data.footer) {
        contenidoArchivo.innerHTML = data.contenido;
        footerArchivo.innerHTML = data.footer;

        // Inicializar el evento click para el botón "Guardar"
        document.getElementById('guardararchivo').addEventListener('click', guardarArchivo);

      } else {
        console.error('Respuesta JSON incorrecta o incompleta.');
        contenidoCarpeta.innerHTML = '<p>Error al cargar el contenido del modal.</p>';
        footerCarpeta.innerHTML = '';
      }

    } catch (error) {
      console.error('Error al cargar el contenido del modal:', error);
      document.getElementById('contenido-carpeta').innerHTML = '<p>Error al cargar el contenido del modal.</p>';
      document.getElementById('footer-carpeta').innerHTML = '';
    }
  }


// Función para guardar la carpeta
async function guardarCarpeta(event) {
  event.preventDefault();

  let nuevoCarpeta = document.getElementById('nombreCarpeta').value;
  let equipo = document.getElementById('equippo').value;
  let usuari = document.getElementById('usuarioo').value;
  let PadreCarpeta = document.getElementById('carpetapadr').value;
  let guardarButton = document.getElementById('guardarcarpeta'); // Obtener el botón
  let spinnerContainer; // Variable para el contenedor del spinner

  console.log("Nuevo Carpeta:", nuevoCarpeta);
  console.log("Equipo:", equipo);
  console.log("Usuario:", usuari);
  console.log("padre:", PadreCarpeta);


  if (nuevoCarpeta != '') {
   

      try {
        // Deshabilitar el botón antes de la solicitud
        guardarButton.disabled = true;

        // Crear el contenedor del spinner y añadirlo al DOM
        spinnerContainer = document.createElement('div');
        spinnerContainer.classList.add('d-inline-flex', 'align-items-center', 'me-2');
        spinnerContainer.innerHTML = `
          <div class="spinner-border text-primary spinner-border-sm" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        `;
        guardarButton.parentNode.insertBefore(spinnerContainer, guardarButton);

        const response = await fetch("../../backend/controller/usuario/administrador-archivo/guardarcarpeta.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({
            carpeta: nuevoCarpeta,
            equip: equipo,
            usuario: usuari,
            padrecarpeta: PadreCarpeta || null
          })
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

      /*  Swal.fire({
          icon: 'success',
          title: '¡Listo!',
          text: 'Se ha creado una Carpeta.',
        });*/

        mostrarAlerta('success','Se ha creado una Carpeta.','¡Listo!');

        document.getElementById('nombreCarpeta').value = "";
        cargarContenidoDinamico();
        // $('#modalNuevaCarpeta').modal('hide'); // Cierra el modal
      } catch (error) {
        console.error("Error:", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al crear la carpeta.',
        });
      } finally {
        // Habilitar el botón después de la solicitud (éxito o error)
        guardarButton.disabled = false;
        // Eliminar el spinner del DOM
        if (spinnerContainer) {
          spinnerContainer.remove();
        }
      }
    
  }
}
  // Cargar contenido dinámico al abrir el modal
  $('#modalNuevaCarpeta').on('show.bs.modal', function (e) {
      cargarContenidoModal();
  })


   // Cargar contenido dinámico al abrir el modal
   $('#modalNuevoArchivo').on('show.bs.modal', function (e) {
    cargarContenidoModalarchivo();
})


 
  
  // Llamar a la función al cargar la página
  cargarContenidoDinamico();

});



async function guardarArchivo(event) {
  event.preventDefault();
  let archivopuro = $("#archivopuro");
  let archivos = archivopuro[0].files; // Obtiene la lista de archivos seleccionados
  let nombrearchivo = $("#nombrearchivo").val();
  let carpetapadre = $("#carpetapadr").val();
  let guardarArchivoBtn = $("#guardararchivo");

  if (archivos.length > 0) { // Verifica si se seleccionaron archivos
      let form = document.getElementById('addFileManagment');
      let formData = new FormData(form);

      // Itera a través de los archivos y los agrega al FormData
      for (let i = 0; i < archivos.length; i++) {
          formData.append('archivos[]', archivos[i]); // Usa 'archivos[]' para que PHP los reciba como un array
      }

      formData.append('nombrarchivo', nombrearchivo);
      formData.append('padrecarp', carpetapadre);
  
              try {
                  guardarArchivoBtn.prop('disabled', true);
                  // Agregar el spinner antes del botón
                  guardarArchivoBtn.before('<div class="spinner-border text-primary me-2" role="status"><span class="visually-hidden">Loading...</span></div>');

                  const response = await fetch("../../backend/controller/usuario/administrador-archivo/archivoguardar.php", {
                      method: "POST",
                      body: formData
                  });

                  if (!response.ok) {
                      throw new Error(`HTTP error! status: ${response.status}`);
                  }

                  const data = await response.json();

                  // Eliminar el spinner después de la respuesta (éxito o error)
                  guardarArchivoBtn.prev('.spinner-border').remove();

                  if (data.exito) {
                   /*   Swal.fire({
                          icon: 'success',
                          title: '¡Listo!',
                          text: 'Archivos agregados.', // Cambia el mensaje para indicar múltiples archivos
                      }); */


                      mostrarAlerta('success','Archivos agregados.','¡Listo!');


                      $("#nombrearchivo").val("");
                      archivopuro.replaceWith(archivopuro.val('').clone(true));
                      cargarContenidoDinamico();
                  } else {
                      Swal.fire({
                          icon: 'error',
                          title: 'Error',
                          text: data.mensaje,
                      });
                  }
              } catch (error) {
                  console.error("Error al guardar los archivos:", error); // Cambia el mensaje de error
                  Swal.fire({
                      icon: 'error',
                      title: 'Error',
                      text: 'No se pudieron guardar los archivos.', // Cambia el mensaje de error
                  });
              } finally {
                  guardarArchivoBtn.prop('disabled', false);
                  // Asegurarse de que el spinner se elimine en caso de error no controlado
                  guardarArchivoBtn.prev('.spinner-border').remove();
              }
      
  }
}



  let elementoSeleccionado = null;

  function mostrarMenu(e, item, x, y) {
      if (item) {
          e.preventDefault();
          elementoSeleccionado = item;
          console.log(elementoSeleccionado);
           // Acceder al ID del item
    const itemId = item.id;
    const eliminarDropdown = document.getElementById('elim'); // Obtén el elemento con el id "elim"
    const eliminarDropdownn = document.getElementById('elimN'); // Obtén el elemento con el id "elim"

    
    console.log("ID del item:", itemId);


    if (itemId === "0") {
      eliminarDropdown.style.display = 'block';
      eliminarDropdownn.style.display = 'none';

      } 
       if(itemId === "1") {
        eliminarDropdown.style.display = 'none';
        eliminarDropdownn.style.display = 'block';

      }


          const menu = document.getElementById("menuContextual");
          menu.style.top = `${y}px`;
          menu.style.left = `${x}px`;
          menu.style.display = 'block';
      } else {
          document.getElementById("menuContextual").style.display = 'none';
      }
  }
  
  function fetchJSON(url, data) {
      return fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
      }).then(res => res.json());
  }
  
  function crearListaColaboradores(colaboradores) {
      return colaboradores.map(c => `
          <div class="badge bg-secondary-soft bg-opacity-10 text-dark d-flex align-items-center px-2 py-1 rounded-pill" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
              <span>${c.nombre_colaborador} ${c.apellido_colaborador}(${c.alias_equipo})</span>
              <button class="btn btn-sm text-danger eliminar-colaborador" data-idusuario="${c.id_colaborador}">x</button>
          </div>
      `).join('');
  }
  
  function adjuntarListeners() {
      const filesContainer = document.getElementById('files-container');
      const menu = document.getElementById("menuContextual");
  
      filesContainer.addEventListener("contextmenu", e => {
          mostrarMenu(e, e.target.closest(".carpeta-item, .archivo-item"), e.pageX, e.pageY);
      });
  
      let touchTimer;
      filesContainer.addEventListener("touchstart", e => {
          const item = e.target.closest(".carpeta-item, .archivo-item");
          if (item) {
              touchTimer = setTimeout(() => {
                  mostrarMenu(e, item, e.touches[0].pageX, e.touches[0].pageY);
              }, 700);
          }
      });
  
      ["touchend", "touchmove"].forEach(evt => 
          filesContainer.addEventListener(evt, () => clearTimeout(touchTimer))
      );
  
      document.addEventListener("click", () => menu.style.display = "none");
  
      document.getElementById("opcionEliminar").addEventListener("click", () => {
          if (elementoSeleccionado) {
              const { id, tipo } = elementoSeleccionado.dataset;
              tipo === "carpeta" ? eliminarCarpeta(id) : eliminarArchivo(id);
          }
      });
  
      document.getElementById("opcionRenombrar").addEventListener("click", () => {
          if (!elementoSeleccionado) return;
          const { id, nombre, tipo } = elementoSeleccionado.dataset;
          const getThemec = () => document.documentElement.getAttribute("data-bs-theme") || "light";
          const temac = getThemec(); // Detecta el tema actual
          Swal.fire({
              title: `Renombrar ${tipo}`,
              input: 'text',
              inputValue: nombre,
              showCancelButton: true,
              confirmButtonText: 'Guardar',
              background: temac === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
              color: temac === "dark" ? "#ffffff" : "#333333", // Texto adaptativo
              inputValidator: value => !value && '¡El nombre no puede estar vacío!'
          }).then(result => {
              if (result.isConfirmed) {
                  fetchJSON('../../backend/controller/usuario/administrador-archivo/renombrar_carpeta.php', {
                      id, nombre: result.value, tipo
                  }).then(data => {
                      if (data.success) {
                        mostrarAlerta('success',`¡${tipo} nombre cambiado!`,'¡Listo!');
                        cargarContenidoDinamico();
                          //Swal.fire(`¡${tipo} renombrado!`, '', 'success').then(cargarContenidoDinamico);
                      } else {
                          Swal.fire('Error', data.message, 'error');
                      }
                  }).catch(() => Swal.fire('Error', 'Hubo un problema al renombrar', 'error'));
              }
          });
      });
  
      document.getElementById("opcionColaborador").addEventListener("click", () => {
        if (!elementoSeleccionado) return;
    
        const idUseri = document.getElementById('usuarioo').value;
        const { id } = elementoSeleccionado.dataset;

        const getThemex = () => document.documentElement.getAttribute("data-bs-theme") || "light";
        const temax = getThemex(); // Detecta el tema actual
    
        const obtenerUsuarios = () =>
            fetch(`../../backend/controller/usuario/administrador-archivo/obtener_usuarios.php?idUser=${idUseri}`)
                .then(res => res.json());
    
        const obtenerColaboradores = () =>
            fetch(`../../backend/controller/usuario/administrador-archivo/obtener_colaboradores.php?idCarpeta=${id}&idUser=${idUseri}`)
                .then(res => res.json());
    
        Promise.all([obtenerUsuarios(), obtenerColaboradores()])
            .then(([usuarios, colaboradoresGuardados]) => {
                let seleccionados = [];
   
                const crearInputAutocompletar = () => `
                   <div style="position: relative;">
        <input type="text" id="inputColaborador" class="form-control" placeholder="Buscar colaborador...">
        <div id="sugerenciasColaborador" style="position: absolute; z-index: 10; background-color: ${temax === "dark" ? "#2a2a2a" : "#ffffff"}; max-height: 150px; overflow-y: auto; margin-top: 5px; width: 100%; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); left: 0;"></div>
    </div>
    <div id="seleccionadosContainer" style="margin-top: 10px;"></div>
    <div style="display: flex; justify-content: space-between; margin-top: 10px;">
        <button type="button" class="btn btn-primary swal2-confirm mt-3">Guardar</button>
        <button type="button" class="btn btn-secondary swal2-cancel mt-3">Cancelar</button>
    </div>
    <div style="margin-top: 20px;">${crearListaColaboradores(colaboradoresGuardados)}</div>
                `;
    
                Swal.fire({
                    title: 'Agregar Colaborador',
                    html: crearInputAutocompletar(),
                    showCancelButton: false,
                    showConfirmButton: false,
                    allowOutsideClick: false,
  background: temax === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
  color: temax === "dark" ? "#ffffff" : "#333333", // Texto adaptativo
                    didOpen: () => {
                        const input = document.getElementById('inputColaborador');
                        const sugerenciasDiv = document.getElementById('sugerenciasColaborador');
                        const seleccionadosDiv = document.getElementById('seleccionadosContainer');
    
                        input.addEventListener('input', () => {
                            const query = input.value.toLowerCase();
                            sugerenciasDiv.innerHTML = '';
    
                            if (query.length === 0) return;
    
                            const idsColaboradoresGuardados = colaboradoresGuardados.map(c => c.id_colaborador);
    
                            const resultados = usuarios.filter(u =>
                                `${u.nombre} ${u.apellido} (${u.alias_equipo})`.toLowerCase().includes(query) &&
                                !idsColaboradoresGuardados.includes(u.id_empleado)
                            );
    
                            resultados.forEach(u => {
                              const item = document.createElement('input');
                              item.type = 'text';
                              item.classList.add('form-control', 'z-2', 'mt-1');
                              item.readOnly = true;
                              item.value = `${u.nombre} ${u.apellido} (${u.alias_equipo})`;
                                item.addEventListener('click', () => {
                                    if (!seleccionados.some(sel => sel.id_empleado === u.id_empleado)) {
                                        seleccionados.push(u);
                                        actualizarListaSeleccionados();
                                    }
                                    input.value = '';
                                    sugerenciasDiv.innerHTML = '';
                                });
                                sugerenciasDiv.appendChild(item);
                            });
                        });
    
                        const actualizarListaSeleccionados = () => {
                            seleccionadosDiv.innerHTML = seleccionados.map(u => `
                               <div class="badge bg-secondary-soft bg-opacity-10 text-dark d-flex align-items-center justify-content-between px-2 py-1 mt-1" style="gap: 8px;">
            <span>${u.nombre} ${u.apellido} (${u.alias_equipo})</span>
            <button class="btn btn-sm text-danger eliminar-colaborador p-0 quitar-colab" data-id="${u.id_empleado}">x</button>
        </div>
                            `).join('');
    
                            seleccionadosDiv.querySelectorAll('.quitar-colab').forEach(btn => {
                                btn.addEventListener('click', e => {
                                    const idEliminar = e.target.dataset.id;
                                    seleccionados = seleccionados.filter(u => u.id_empleado != idEliminar);
                                    actualizarListaSeleccionados();
                                });
                            });
                        };
    
                        document.querySelector('.swal2-confirm').addEventListener('click', () => {
                          const getTheme = () => document.documentElement.getAttribute("data-bs-theme") || "light";
                          const tema = getTheme(); // Detecta el tema actual

                            if (seleccionados.length === 0) {
                                Swal.fire('⚠️', 'Seleccioná al menos un colaborador', 'warning');
                                return;
                            }
    
                            Promise.all(seleccionados.map(user =>
                                fetch('../../backend/controller/usuario/administrador-archivo/guardar_colaborador.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ id, idusuario: user.id_empleado })
                                }).then(res => res.json())
                            )).then(resultados => {
                                const errores = resultados.filter(r => !r.success);
                                if (errores.length === 0) {
                                 // Swal.fire('✅ ¡Colaboradores agregados!', '', 'success')
                                 Swal.fire({
                                  title: '¡Colaboradores agregados!',
                                  text: '',
                                  icon: 'success',
                                  allowOutsideClick: false,
                                  background: tema === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
                                  color: tema === "dark" ? "#ffffff" : "#333333", // Texto adaptativo
                                  customClass: {
                                    popup: tema === "dark" ? "swal-dark-mode" : "", // Clase para mejorar estilos en oscuro
                                    confirmButton: "swal-confirm-button" // Clase personalizada para el botón
                                  }
                                }).then(() => {
                                        obtenerColaboradores().then(nuevos => {
                                            Swal.update({
                                                html: crearInputAutocompletar().replace(
                                                    crearListaColaboradores(colaboradoresGuardados),
                                                    crearListaColaboradores(nuevos)
                                                )
                                            });
                                            cargarContenidoDinamico();
                                        });
                                    });
                                } else {
                                    Swal.fire('Error', errores[0].message, 'error');
                                }
                            });
                        });
    
                        document.querySelector('.swal2-cancel').addEventListener('click', () => Swal.close());
    
                        document.querySelectorAll('.eliminar-colaborador').forEach(btn =>
                            btn.addEventListener('click', e => {
                              const getThemes = () => document.documentElement.getAttribute("data-bs-theme") || "light";
                              const temas = getThemes(); // Detecta el tema actual
                                const idusuario = e.target.dataset.idusuario;
                                fetchJSON('../../backend/controller/usuario/administrador-archivo/eliminar_colaborador.php', { id, idusuario })
                                    .then(data => {
                                        if (data.success) {
                                          //Swal.fire('Colaborador eliminado', '', 'success')
                                          Swal.fire({
                                            title: 'Colaborador eliminado',
                                            text: '',
                                            icon: 'success',
                                            allowOutsideClick: false,
                                            background: temas === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
                                            color: temas === "dark" ? "#ffffff" : "#333333", // Texto adaptativo
                                            customClass: {
                                              popup: temas === "dark" ? "swal-dark-mode" : "", // Clase para mejorar estilos en oscuro
                                              confirmButton: "swal-confirm-button" // Clase personalizada para el botón
                                            }
                                          }).then(() => {
                                            document.getElementById('swal2-html-container').style.display = 'none';

                                                obtenerColaboradores().then(nuevos => {
                                                    Swal.update({
                                                        html: crearInputAutocompletar().replace(
                                                            crearListaColaboradores(colaboradoresGuardados), // Asegúrate de usar la variable correcta
                                                            crearListaColaboradores(nuevos)         // Asegúrate de usar la variable correcta
                                                        )
                                                    });
                                                    cargarContenidoDinamico();
                                                });
                                            });
                                        } else {
                                            Swal.fire('Error', data.message, 'error');
                                        }
                                    });
                            })
                        );
                    }
                });
            }).catch(() => Swal.fire('Error', 'No se pudieron cargar los datos', 'error'));
    });
  }
  




async function eliminarCarpeta(idcarpe) {
  

  
     // Verificar el tamaño del archivo antes de enviar
     const archivoInput = document.getElementById('archivoInputId'); // Reemplaza 'archivoInputId' con el ID de tu input de archivo
     if (archivoInput && archivoInput.files.length > 0) {
         const tamanoMaximo = 10 * 1024 * 1024; // 10 MB
         if (archivoInput.files[0].size > tamanoMaximo) {
             Swal.fire({
                 icon: 'error',
                 title: 'Error',
                 text: 'El archivo excede el tamaño máximo permitido (10 MB).',
                 customClass: {
                     confirmButton: 'btn btn-danger'
                 }
             });
             return; // Detener el envío del formulario
         }
        }



        try {
            const response = await fetch("../../backend/controller/usuario/administrador-archivo/eliminar_carpeta.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `id=${idcarpe}`
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const respuesta = await response.text(); // o response.json() si el servidor responde con JSON

            if (respuesta === "ok") {
               /* Swal.fire({
                    icon: 'success',
                    title: '¡Listo!',
                    text: 'la Carpeta ha sido eliminada',
                    customClass: {
                        confirmButton: 'btn btn-success'
                    }
                });*/
                mostrarAlerta('success','la Carpeta ha sido eliminada.','¡Listo!');

                setTimeout(function() {
                    
                }, 2000);
                cargarContenidoDinamico();

            } else {
                console.error(respuesta);
            }







        } catch (error) {
            console.error("Error:", error);
            // Manejar el error apropiadamente, por ejemplo, mostrar un mensaje de error al usuario
        }
    
}


async function eliminarArchivo(idarchiv) {
  try {
   

   
      const respuesta = await fetch("../../backend/controller/usuario/administrador-archivo/eliminar_archivo.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${idarchiv}`
      });

      if (respuesta.ok) {
        const textoRespuesta = await respuesta.text();
        if (textoRespuesta === "ok") {
        /*  Swal.fire({
            icon: 'success',
            title: '¡Listo!',
            text: 'el Archivo ha sido eliminado',
            customClass: {
              confirmButton: 'btn btn-success'
            }
          });*/

                    mostrarAlerta('success','el Archivo ha sido eliminado.','¡Listo!');


          // Refresca la página después de que se cierre la alerta
          setTimeout(() => {
            //location.reload(); // Refresca la página
          }, 2000); // Espera 2000 ms (2 segundos) antes de refrescar
          cargarContenidoDinamico();

        } else {
          console.error(textoRespuesta);
        }
      } else {
        console.error("Error en la respuesta del servidor:", respuesta.status);
      }
    
  } catch (error) {
    console.error("Error:", error);
  }
}

  async function cargarBreadcrumb() {
    try {
      const seguirContainer = document.getElementById('seguir');
      if (!seguirContainer) {
        console.error('Elemento seguir no encontrado.');
        return;
      }
  
      const urlParams = new URLSearchParams(window.location.search);
      const p = urlParams.get('p');
  
      if (p) {
        const idUseri = document.getElementById('usuarioo').value;
        let url = '../../backend/controller/usuario/administrador-archivo/conseguir-ruta.php';
        url += `?p=${p}&idUser=${idUseri}`;
  
        const response = await fetch(url);
  
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
  
        const data = await response.json(); // Analizar la respuesta JSON
        seguirContainer.innerHTML = data.breadcrumbs; // Asignar el HTML del breadcrumb
  
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
      } else {
        seguirContainer.innerHTML = ''; // Limpiar el contenedor si no hay parámetro 'p'
      }
    } catch (error) {
      console.error('Error al cargar breadcrumb:', error);
      document.getElementById('seguir').innerHTML = 'Error al cargar el breadcrumb.';
    }
  }
  
  document.addEventListener('DOMContentLoaded', cargarBreadcrumb);















