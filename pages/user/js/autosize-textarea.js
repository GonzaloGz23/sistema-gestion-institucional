/**
 * Función para ajustar automáticamente la altura de los textareas según su contenido
 * Esta función elimina la necesidad de barras de desplazamiento en los textareas
 */
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar autosize a todos los textareas existentes al cargar la página
    const allTextareas = document.querySelectorAll('textarea');
    allTextareas.forEach(textarea => {
        applyAutosize(textarea);
    });
    
    // Aplicar autosize cuando se muestra un modal
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        const textareas = modal.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            applyAutosize(textarea);
        });
    });

    // Observador de mutaciones para detectar nuevos textareas añadidos dinámicamente
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    // Verificar si el nodo es un textarea
                    if (node.nodeName === 'TEXTAREA') {
                        applyAutosize(node);
                    }
                    // Verificar si el nodo contiene textareas
                    if (node.querySelectorAll) {
                        const textareas = node.querySelectorAll('textarea');
                        textareas.forEach(textarea => {
                            applyAutosize(textarea);
                        });
                    }
                });
            }
        });
    });

    // Configurar el observador para monitorear todo el documento
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

/**
 * Aplica el ajuste automático de altura a un textarea específico
 * @param {HTMLTextAreaElement} textarea - El elemento textarea a ajustar
 */
function applyAutosize(textarea) {
    if (!textarea) return;

    // Guardar el estilo original de altura
    const originalHeight = textarea.style.height;

    // Función para ajustar la altura
    const adjustHeight = function() {
        // Verificar si el textarea es visible
        const isVisible = textarea.offsetParent !== null;
        
        // Si no es visible, intentamos ajustar después
        if (!isVisible) {
            // Intentar ajustar después de un breve retraso
            setTimeout(() => adjustHeight(), 50);
            return;
        }
        
        // Restablecer la altura para calcular correctamente el scrollHeight
        textarea.style.height = 'auto';
        
        // Establecer la altura basada en el contenido
        textarea.style.height = textarea.scrollHeight + 'px';
    };

    // Ajustar altura inicial
    adjustHeight();

    // Ajustar altura cuando cambie el contenido
    textarea.addEventListener('input', adjustHeight);
    
    // Ajustar altura cuando el textarea reciba el foco
    textarea.addEventListener('focus', adjustHeight);
    
    // Ajustar altura cuando el textarea sea visible (útil para modales)
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                adjustHeight();
            }
        });
    });
    observer.observe(textarea);
    
    // Ajustar altura cuando cambie el valor programáticamente
    const originalSetAttribute = textarea.setAttribute;
    textarea.setAttribute = function(name, value) {
        originalSetAttribute.call(this, name, value);
        if (name === 'value') {
            adjustHeight();
        }
    };
}

/**
 * Función global para aplicar autosize a un textarea específico
 * Útil para llamar desde otros scripts cuando se crean textareas dinámicamente
 * @param {HTMLTextAreaElement|string} textarea - El elemento textarea o su ID
 */
function autosizeTextarea(textarea) {
    if (typeof textarea === 'string') {
        textarea = document.getElementById(textarea);
    }
    applyAutosize(textarea);
}