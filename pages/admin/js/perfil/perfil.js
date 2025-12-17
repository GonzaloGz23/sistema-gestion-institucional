async function cargarDatos() {
    const tbody = document.getElementById('tablaCuerpo');

    // Limpiar cuerpo de tabla
    tbody.innerHTML = `
    <tr id="spinnerRow">
      <td colspan="5" class="text-center">
        <div class="spinner-border text-primary" role="status" style="margin: 10px auto;">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </td>
    </tr>
  `;

    try {
        const response = await fetch('./ui/perfil/dispositivos_notifi.php');
        if (!response.ok) throw new Error('Error al obtener datos');

        const datos = await response.json();

        // Eliminar spinner
        const spinnerRow = document.getElementById('spinnerRow');
        if (spinnerRow) spinnerRow.remove();

        // Agregar filas con datos
        datos.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${item.fecha}</td>
        <td>${item.dispositivo}</td>
        <td>${item.navegador}</td>
        <td>${item.estado}</td>
        <td>
          <input type="checkbox" data-id="${item.id}" name="chequeado" ${item.checkead == 1 ? 'checked' : ''}>
        </td>
      `;
            tbody.appendChild(tr);
        });

    } catch (error) {
        const spinnerRow = document.getElementById('spinnerRow');
        if (spinnerRow) {
            spinnerRow.innerHTML = `<td colspan="5" class="text-danger">Error cargando datos</td>`;
        }
        console.error(error);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    cargarDatos();

    const btnActualizar = document.getElementById('actualizar');
    if (btnActualizar) {
        btnActualizar.addEventListener('click', function () {
            cargarDatos();
        });
    }
});

document.getElementById('tablaCuerpo').addEventListener('change', function (e) {
    if (e.target && e.target.name === 'chequeado') {
        const id = e.target.getAttribute('data-id');
        const estado = e.target.checked ? 1 : 0;

        const fila = e.target.closest('tr');
        const celdaEstado = fila.children[3]; // índice 3 corresponde a <td> estado

        // Guardamos estados previos para restaurar en caso de error
        const estadoAnterior = celdaEstado.textContent;
        const checkedAnterior = !e.target.checked;

        // Mostrar texto de carga temporal
        celdaEstado.textContent = 'Actualizando...';

        fetch('../../backend/controller/admin/perfil/estado_notificacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                estado: estado
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const estadoTexto = e.target.checked ? 'Activo' : 'Inactivo';
                    celdaEstado.textContent = estadoTexto;

                } else {
                    celdaEstado.textContent = estadoAnterior;
                    e.target.checked = checkedAnterior; // Restaurar checkbox
                    //alert('Error al actualizar');
                }
            })
            .catch(err => {
                console.error(err);
                celdaEstado.textContent = estadoAnterior;
                e.target.checked = checkedAnterior; // Restaurar checkbox
                //alert('Error de conexión');
            });
    }
});
