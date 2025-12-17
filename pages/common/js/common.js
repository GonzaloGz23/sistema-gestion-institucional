//console.log('Funciones globales personalizadas');

// detectar el tama usado actualmente
const getTheme = () => document.documentElement.getAttribute("data-bs-theme") || "light";


// Función para mostrar alertas de SweetAlert2
const mostrarAlerta = (tipo, mensaje, titulo = "Atención") => {
    const tema = getTheme(); // Detecta el tema actual

    Swal.fire({
        title: titulo,
        text: mensaje,
        icon: tipo, // success, error, warning, info
        confirmButtonText: "OK",
        allowOutsideClick: false,
        background: tema === "dark" ? "#2a2a2a" : "#ffffff", // Fondo según el tema
        color: tema === "dark" ? "#ffffff" : "#333333", // Texto adaptativo
        customClass: {
            popup: tema === "dark" ? "swal-dark-mode" : "", // Clase para mejorar estilos en oscuro
            confirmButton: "swal-confirm-button" // Clase personalizada para el botón
        }
    });
};

// Ejemplo de uso:
// mostrarAlerta("success", "Operación realizada con éxito", "¡Éxito!");
// mostrarAlerta("error", "Hubo un problema al procesar la solicitud", "¡Error!");
// mostrarAlerta("info", "Esta es una notificación informativa", "Info");

// por el momento no se utiliza  (se usa modales en su lugar :D)
// const confirmarAccion = (mensaje, accionConfirmada) => {
//     const tema = getTheme(); // Detecta el tema actual

const confirmarAccion = (mensaje, accionConfirmada) => {
    const tema = getTheme();

    Swal.fire({
        text: mensaje,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
        background: tema === "dark" ? "#2a2a2a" : "#ffffff",
        color: tema === "dark" ? "#ffffff" : "#333333",
        customClass: {
            popup: tema === "dark" ? "swal-dark-mode" : "",
            confirmButton: "swal-confirm-button"
        }
    }).then((result) => {
        if (result.isConfirmed) {
            accionConfirmada();
        }
    });
};

