document.getElementById('form-tipo').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch('../../backend/controller/admin/formularios/tipos_formularios/crear_tipo.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Éxito', data.message, 'success').then(() => {
                    fetch('./ui/formulario/cont_dinamic_tipo_form.php')
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('cont-form').innerHTML = html;
                            activarBuscador();
                            cambiarEstados() 
                            form.reset();
                        })
                        .catch(error => {
                            console.error(error);
                            document.getElementById('cont-form').innerHTML = 'Error al cargar contenido.';
                        });
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
//buscador
function activarBuscador() {
    const searchInput = document.getElementById('search-input');
    const cards = document.querySelectorAll('.accordion');


    searchInput.addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();

        cards.forEach(card => {
            const title = card.querySelector('h3');
            if (title) {
                const titleText = title.textContent.toLowerCase();
                if (titleText.includes(searchValue)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    });

    function filtrarPorEstado(estado) {
        cards.forEach(card => {
            const estadoSpan = card.querySelector('span'); // primer span es el estado
            if (estadoSpan) {
                const estadoTexto = estadoSpan.textContent.trim().toLowerCase();
                if (estadoTexto === estado.toLowerCase()) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            }
        });
    }

    document.getElementById('filt_Permanente').addEventListener('click', () => {
        filtrarPorEstado('permanente');
    });

    document.getElementById('filt_Habilitado').addEventListener('click', () => {
        filtrarPorEstado('habilitado');
    });

    document.getElementById('filt_Inactivo').addEventListener('click', () => {
        filtrarPorEstado('inactivo');
    });
    document.getElementById('filt_Todos').addEventListener('click', () => {
        cards.forEach(card => card.style.display = '');
    });
}


document.addEventListener('DOMContentLoaded', function () {
    activarBuscador()
    cambiarEstados() 
});


function truncateText(selector, maxLength) {
    document.querySelectorAll(selector).forEach(el => {
        if (el.textContent.length > maxLength) {
            el.textContent = el.textContent.substring(0, maxLength) + '...';
        }
    });
}

truncateText('.dynamic-truncate', 50);

function cambiarEstados() {
    document.querySelectorAll(".cambiar-estado").forEach(item => {
        item.addEventListener("click", function (event) {
            event.preventDefault();
    
            let idFormulario = parseInt(this.getAttribute("data-id"), 10); // Convertir a número
            let nuevoEstado = this.getAttribute("data-estado");
    
            let formData = new FormData();
            formData.append("id", idFormulario);
            formData.append("estado", nuevoEstado);
    
            fetch("../../backend/controller/admin/formularios/tipos_formularios/actualizar_estado.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => {
                            fetch('./ui/formulario/cont_dinamic_tipo_form.php')
                            .then(res => res.text())
                            .then(html => {
                                document.getElementById('cont-form').innerHTML = html;
                                activarBuscador();
                                cambiarEstados() 
                            })
                            .catch(error => {
                                console.error(error);
                                document.getElementById('cont-form').innerHTML = 'Error al cargar contenido.';
                            });
                        });
    
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
}


