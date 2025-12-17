<?php
include '../../../../backend/config/database.php';


$tipoFormulario = $pdo->prepare("SELECT *,
    DATE_FORMAT(`fecha_creacion`, '%d/%m/%Y') AS fecha,
    CASE 
      WHEN `estados` = 'habilitado' THEN 'text-success'
      WHEN `estados` = 'inactivo' THEN 'text-danger'
      WHEN `estados` = 'permanente' THEN 'text-secondary'
    END texto
    FROM `tipos_formularios`  ORDER BY `tipos_formularios`.`id_tipos_formularios` DESC");

$tipoFormulario->execute();

foreach ($tipoFormulario as $t) {
    echo '  
        <div class="col-md-6 col-xl-3 col-12 card-item">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="' . $t['texto'] . '">' . $t['estados'] . '</span>
                        </div>
                        <div class="btn-group">
                            <button type="button" class="btn  border-0" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> 
                                <i class="bi bi-three-dots-vertical ' . $t['texto'] . '"></i>
                            </button>

                            ';
    if ($t['estados'] == 'permanente') {
    } else {


        echo '
                         
                        <div class="dropdown-menu">
                            <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="habilitado">Habilitado</a>
                            <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="inactivo">Inactivo</a>
                            <a class="dropdown-item cambiar-estado" href="#" data-id="' . $t['id_tipos_formularios'] . '" data-estado="permanente">Permanente</a>
                        </div>';
    }
    echo '
                            </div>
                    </div>
                    <div class="text-center my-4">
                        <h3 class="text-truncate"  data-bs-toggle="tooltip" title="' . $t['nombre'] . '">' . $t['nombre'] . '</h3>
                        <p  class="dynamic-truncate"  data-bs-toggle="tooltip" title="' . $t['descripcion'] . '">' . $t['descripcion'] . '</p>
                    </div>
                </div>
                <div class="card-footer text-muted text-center">
                    <p class="mb-0">' . $t['fecha'] . '</p>
                </div>
            </div>
        </div>';
}
?>
<script>
    //buscador
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const cards = document.querySelectorAll('.card-item'); // Seleccionamos todas las tarjetas

        // Agregar un evento para el input de búsqueda
        searchInput.addEventListener('input', function() {
            const searchValue = searchInput.value.toLowerCase(); // Convertimos la entrada a minúsculas para hacerlo insensible a mayúsculas

            // Recorremos todas las tarjetas
            cards.forEach(card => {
                const cardName = card.querySelector('h3').innerText.toLowerCase(); // Nombre del formulario

                // Si el nombre contiene el valor de búsqueda, mostramos la tarjeta
                if (cardName.includes(searchValue)) {
                    card.style.display = ''; // Muestra la tarjeta
                } else {
                    card.style.display = 'none'; // Oculta la tarjeta
                }
            });
        });
    });

    function truncateText(selector, maxLength) {
        document.querySelectorAll(selector).forEach(el => {
            if (el.textContent.length > maxLength) {
                el.textContent = el.textContent.substring(0, maxLength) + '...';
            }
        });
    }

    truncateText('.dynamic-truncate', 50);
</script>