
document.addEventListener('DOMContentLoaded', function () {
    // Selecciona todos los botones
    const botones = document.querySelectorAll('.button_form');
    cambiarEstados()

    botones.forEach(boton => {
        boton.addEventListener('click', function () {
            const idFormulario = this.parentElement.querySelector('.id_form').value;

            const formData = new FormData();
            formData.append("id_form", idFormulario);

            fetch('./ui/formulario/visualizar_form.php', { // Cambiá la ruta si es distinta
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    document.getElementById('contenedorFormulario').innerHTML = data;
                    window.scrollTo({ top: document.getElementById('contenedorFormulario').offsetTop, behavior: 'smooth' });
                    activarCalificacion(document.getElementById('contenedorFormulario'))
                    
                })
                .catch(error => {
                    console.error('Error al cargar el formulario:', error);
                    document.getElementById('contenedorFormulario').innerHTML = '<p class="text-danger">Hubo un error al cargar el formulario.</p>';
                });
        });
    });
});
function activarCalificacion(contenedor) {
    const calContainers = contenedor.querySelectorAll('.calificacion');
    calContainers.forEach(calContainer => {
        const labels = calContainer.querySelectorAll('label');
        let currentRating = 0;

        labels.forEach(label => {
            const value = parseInt(label.getAttribute('data-value'));

            label.addEventListener('mouseover', () => updateStars(value));
            label.addEventListener('click', () => {
                currentRating = value;
                updateStars(currentRating);
                const input = calContainer.querySelector(`input[value="${value}"]`);
                if (input) input.checked = true;
            });
            label.addEventListener('mouseout', () => updateStars(currentRating));
        });

        function updateStars(value) {
            labels.forEach(label => {
                const icon = label.querySelector('i');
                const starValue = parseInt(label.getAttribute('data-value'));
                if (icon) {
                    icon.classList.toggle('bi-star-fill', starValue <= value);
                    icon.classList.toggle('bi-star', starValue > value);
                }
            });
        }
    });
}


function cambiarEstados() {
    document.querySelectorAll(".cambiar-estado").forEach(item => {
        item.addEventListener("click", function (event) {
            event.preventDefault();

            let idFormulario = parseInt(this.getAttribute("data-id"), 10); // Convertir a número
            let nuevoEstado = this.getAttribute("data-estado");

            let formData = new FormData();
            formData.append("id", idFormulario);
            formData.append("estado", nuevoEstado);

            fetch("../../backend/controller/admin/formularios/misformularios/publicacion_form.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => {
                            // Actualizar el texto del badge
                            this.closest(".card-body").querySelector(".badge").textContent = nuevoEstado;
                        });
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
}
