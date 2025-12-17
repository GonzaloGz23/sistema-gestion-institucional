document.addEventListener("DOMContentLoaded", async () => {

    const main = document.querySelector("main");
    const idCapacitacion = main.dataset.capacitacion;
    const nombreCapacitacion = main.dataset.capacitacionNombre || "Capacitacion";
    const nombreArchivo = `${nombreCapacitacion}`;

    const spinner = document.getElementById("spinnerCarga");
    const contenedorTabla = document.getElementById("contenedorTabla");
    const tbody = document.getElementById("tablaCapacitacionesBody");
    const mensajeNoDatos = document.getElementById("mensajeNoCapacitaciones");

    spinner.classList.remove("d-none");

    let dataTable = null;

    try {

        const response = await fetch("../../backend/controller/admin/inscriptos-capacitaciones/listar_incriptos.php", {
            method: "POST",
            body: new URLSearchParams({ id: idCapacitacion })
        });

        const result = await response.json();

        spinner.classList.add("d-none");

        if (!result.success || !result.inscriptos || result.inscriptos.length === 0) {
            mensajeNoDatos.classList.remove("d-none");
            return;
        }

        // ---- LIMPIAR TABLA ANTERIOR ----
        tbody.innerHTML = "";

        // ---- RENDERIZAR FILAS ----
        result.inscriptos.forEach((item, index) => {

            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${item.nombre}</td>
                <td>${item.apellido}</td>
                <td>${item.dni}</td>
                <td>${item.sexo}</td>
                <td>${item.edad}</td>
                <td>${item.celular}</td>
                <td>${item.fecha_inscripcion}</td>
                <td>${item.email}</td>
            `;

            tbody.appendChild(tr);
        });

        contenedorTabla.classList.remove("d-none");

        // ---- SI YA HAY UN DATATABLE, SE DESTRUYE ----
        if ($.fn.DataTable.isDataTable("#tablaCapacitaciones")) {
            $("#tablaCapacitaciones").DataTable().clear().destroy();
        }

        // ---- INICIALIZAR DATATABLE ----
        dataTable = $("#tablaCapacitaciones").DataTable({
            responsive: true,
            paging: true,
            ordering: true,
            info: true,
            searching: true,

            dom: "Bfrtip",
            buttons: [
                {
                    extend: "excelHtml5",
                    text: "📗 Excel",
                    title: nombreArchivo,
                    filename: nombreArchivo
                },
                {
                    extend: "csvHtml5",
                    text: "📄 CSV",
                    title: nombreArchivo,
                    filename: nombreArchivo
                },
                {
                    extend: "pdfHtml5",
                    text: "📕 PDF",
                    title: nombreArchivo,
                    filename: nombreArchivo,
                    orientation: "landscape",
                    pageSize: "A4"
                },
                {
                    extend: "print",
                    text: "🖨 Imprimir",
                    title: nombreArchivo
                }
            ],

            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });

    } catch (error) {
        console.error("Error:", error);
        spinner.classList.add("d-none");
        mensajeNoDatos.textContent = "Error al cargar la información.";
        mensajeNoDatos.classList.remove("d-none");
    }
});
