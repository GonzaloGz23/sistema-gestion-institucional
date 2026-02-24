/**
 * ==========================================
 * GESTOR DE CATEGORÍAS CONDICIONALES
 * ==========================================
 * 
 * Sistema modular y reutilizable para manejar categorías jerárquicas:
 * - Categorías Generales (obligatorias)
 * - Categorías Específicas (opcionales, dependen de generales)
 * - Subcategorías (opcionales, dependen de específicas)
 * 
 * Funcionalidades:
 * - Carga dinámica de opciones
 * - Habilitación/deshabilitación condicional
 * - Validación de dependencias
 * - Eventos de cambio en cascada
 * - Restablecimiento de selecciones hijas
 * 
 * Uso:
 * const categoriasManager = new CategoriasManager({
 *     selectores: {
 *         general: '#categoriaGeneral',
 *         especifica: '#categoriaEspecifica', 
 *         subcategoria: '#subcategoria'
 *     }
 * });
 */

class CategoriasManager {
    constructor(config) {
        this.config = {
            selectores: {
                general: '#categoriaGeneral',
                especifica: '#categoriaEspecifica',
                subcategoria: '#subcategoria'
            },
            endpoints: {
                generales: '/sistema-gestion-institucional/backend/controller/admin/categorias/obtener_generales.php',
                especificas: '/sistema-gestion-institucional/backend/controller/admin/categorias/obtener_especificas.php',
                subcategorias: '/sistema-gestion-institucional/backend/controller/admin/categorias/obtener_subcategorias.php'
            },
            textos: {
                seleccionar: 'Seleccione una opción',
                cargando: 'Cargando...',
                sinOpciones: 'Sin opciones disponibles',
                errorCarga: 'Error al cargar opciones'
            },
            ...config
        };
        
        this.elementos = {
            general: null,
            especifica: null,
            subcategoria: null
        };
        
        this.datos = {
            generales: [],
            especificas: [],
            subcategorias: []
        };
        
        this.inicializar();
    }
    
    // ==========================================
    // INICIALIZACIÓN
    // ==========================================
    
    inicializar() {
        // console.log('🎯 Inicializando Gestor de Categorías...');
        
        // Obtener elementos DOM
        this.obtenerElementos();
        
        // Validar elementos
        if (!this.validarElementos()) {
            console.error('❌ Error: No se pudieron encontrar todos los elementos requeridos');
            return;
        }
        
        // Debug: Verificar estado inicial de los elementos
        // console.log('🔍 Estado inicial de selects:');
        // console.log('General disabled:', this.elementos.general.prop('disabled'));
        // console.log('Específica disabled:', this.elementos.especifica.prop('disabled'));
        // console.log('Subcategoría disabled:', this.elementos.subcategoria.prop('disabled'));
        
        // Configurar estados iniciales
        this.configurarEstadosIniciales();
        
        // Debug: Verificar estado después de configurar
        // console.log('🔍 Estado después de configurar:');
        // console.log('General disabled:', this.elementos.general.prop('disabled'));
        // console.log('Específica disabled:', this.elementos.especifica.prop('disabled'));
        // console.log('Subcategoría disabled:', this.elementos.subcategoria.prop('disabled'));
        
        // Configurar eventos
        this.configurarEventos();
        
        // Cargar categorías generales
        this.cargarCategoriasGenerales();
        
        // console.log('✅ Gestor de Categorías inicializado correctamente');
    }
    
    obtenerElementos() {
        this.elementos.general = $(this.config.selectores.general);
        this.elementos.especifica = $(this.config.selectores.especifica);
        this.elementos.subcategoria = $(this.config.selectores.subcategoria);
    }
    
    validarElementos() {
        return this.elementos.general.length > 0 && 
               this.elementos.especifica.length > 0 && 
               this.elementos.subcategoria.length > 0;
    }
    
    configurarEstadosIniciales() {
        // NO deshabilitar la categoría general - se maneja en cargarCategoriasGenerales
        
        // Las categorías específicas y subcategorías inician deshabilitadas hasta que se seleccione su padre
        this.elementos.especifica.prop('disabled', true);
        this.elementos.subcategoria.prop('disabled', true);
        
        // Agregar opciones por defecto con mensajes apropiados
        this.agregarOpcionPorDefecto(this.elementos.especifica, 'Seleccione una categoría general primero');
        this.agregarOpcionPorDefecto(this.elementos.subcategoria, 'Seleccione una categoría específica primero');
    }
    
    // ==========================================
    // GESTIÓN DE EVENTOS
    // ==========================================
    
    configurarEventos() {
        // Evento de cambio en categoría general
        this.elementos.general.on('change', (e) => {
            const valorSeleccionado = $(e.target).val();
            this.onCategoriaGeneralChange(valorSeleccionado);
        });
        
        // Evento de cambio en categoría específica
        this.elementos.especifica.on('change', (e) => {
            const valorSeleccionado = $(e.target).val();
            this.onCategoriaEspecificaChange(valorSeleccionado);
        });
        
        // Evento de cambio en subcategoría (para validaciones futuras)
        this.elementos.subcategoria.on('change', (e) => {
            const valorSeleccionado = $(e.target).val();
            this.onSubcategoriaChange(valorSeleccionado);
        });
    }
    
    onCategoriaGeneralChange(valorSeleccionado) {
        // console.log('🔄 Categoría General seleccionada:', valorSeleccionado);
        
        if (valorSeleccionado && valorSeleccionado !== '') {
            // Resetear categorías hijas
            this.resetearSelect(this.elementos.especifica);
            this.resetearSelect(this.elementos.subcategoria);
            this.elementos.subcategoria.prop('disabled', true);
            
            // Cargar categorías específicas para esta general
            this.cargarCategoriasEspecificas(valorSeleccionado);
        } else {
            // Si no hay selección, resetear y deshabilitar todo
            this.resetearSelect(this.elementos.especifica);
            this.resetearSelect(this.elementos.subcategoria);
            this.elementos.especifica.prop('disabled', true);
            this.elementos.subcategoria.prop('disabled', true);
        }
    }
    
    onCategoriaEspecificaChange(valorSeleccionado) {
        // console.log('🔄 Categoría Específica seleccionada:', valorSeleccionado);
        
        if (valorSeleccionado && valorSeleccionado !== '') {
            // Resetear subcategorías
            this.resetearSelect(this.elementos.subcategoria);
            
            // Cargar subcategorías para esta específica
            this.cargarSubcategorias(valorSeleccionado);
        } else {
            // Si no hay selección, resetear y deshabilitar subcategorías
            this.resetearSelect(this.elementos.subcategoria);
            this.elementos.subcategoria.prop('disabled', true);
        }
    }
    
    onSubcategoriaChange(valorSeleccionado) {
        // console.log('🔄 Subcategoría seleccionada:', valorSeleccionado);
        // Aquí se pueden agregar validaciones adicionales si es necesario
    }
    
    // ==========================================
    // CARGA DE DATOS DESDE API
    // ==========================================
    
    async cargarCategoriasGenerales() {
        try {
            // console.log('📡 Cargando categorías generales...');
            
            this.mostrarCargando(this.elementos.general);
            
            const response = await fetch(this.config.endpoints.generales, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.datos.generales = data.categorias;
                this.poblarSelect(this.elementos.general, data.categorias);
                
                // IMPORTANTE: Habilitar el select después de cargar las opciones
                this.elementos.general.prop('disabled', false);
                
                // console.log('✅ Categorías generales cargadas:', data.categorias.length);
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('❌ Error al cargar categorías generales:', error);
            this.mostrarError(this.elementos.general);
            // En caso de error, también habilitar para que el usuario pueda intentar de nuevo
            this.elementos.general.prop('disabled', false);
        }
    }
    
    async cargarCategoriasEspecificas(idGeneral) {
        try {
            // console.log('📡 Cargando categorías específicas para ID:', idGeneral);
            
            this.mostrarCargando(this.elementos.especifica);
            
            const response = await fetch(`${this.config.endpoints.especificas}?categoria_general_id=${idGeneral}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.datos.especificas = data.categorias;
                
                if (data.categorias.length > 0) {
                    // Hay categorías específicas disponibles
                    this.poblarSelect(this.elementos.especifica, data.categorias);
                    this.elementos.especifica.prop('disabled', false);
                    // console.log('✅ Categorías específicas cargadas:', data.categorias.length);
                } else {
                    // No hay categorías específicas para esta general
                    this.mostrarSinOpciones(this.elementos.especifica);
                    this.elementos.especifica.prop('disabled', true);
                    // console.log('ℹ️ No hay categorías específicas para la general seleccionada');
                }
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('❌ Error al cargar categorías específicas:', error);
            this.mostrarError(this.elementos.especifica);
        }
    }
    
    async cargarSubcategorias(idEspecifica) {
        try {
            // console.log('📡 Cargando subcategorías para ID:', idEspecifica);
            
            this.mostrarCargando(this.elementos.subcategoria);
            
            const response = await fetch(`${this.config.endpoints.subcategorias}?categoria_especifica_id=${idEspecifica}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.datos.subcategorias = data.categorias;
                
                if (data.categorias.length > 0) {
                    // Hay subcategorías disponibles
                    this.poblarSelect(this.elementos.subcategoria, data.categorias);
                    this.elementos.subcategoria.prop('disabled', false);
                    // console.log('✅ Subcategorías cargadas:', data.categorias.length);
                } else {
                    // No hay subcategorías para esta específica
                    this.mostrarSinOpciones(this.elementos.subcategoria);
                    this.elementos.subcategoria.prop('disabled', true);
                    // console.log('ℹ️ No hay subcategorías para la específica seleccionada');
                }
            } else {
                throw new Error(data.error || 'Error desconocido');
            }
            
        } catch (error) {
            console.error('❌ Error al cargar subcategorías:', error);
            this.mostrarError(this.elementos.subcategoria);
        }
    }
    
    // ==========================================
    // MANIPULACIÓN DE SELECTS
    // ==========================================
    
    poblarSelect(elemento, opciones) {
        // Limpiar opciones existentes
        elemento.empty();
        
        // Agregar opción por defecto
        this.agregarOpcionPorDefecto(elemento);
        
        // Agregar opciones de datos
        opciones.forEach(opcion => {
            elemento.append(new Option(opcion.nombre, opcion.id));
        });
        
        // NO modificar el estado disabled aquí - se maneja en las funciones de carga
    }
    
    agregarOpcionPorDefecto(elemento, texto = null) {
        const textoOpcion = texto || this.config.textos.seleccionar;
        elemento.append(new Option(textoOpcion, ''));
    }
    
    mostrarCargando(elemento) {
        elemento.empty();
        elemento.append(new Option(this.config.textos.cargando, ''));
        elemento.prop('disabled', true);
    }
    
    mostrarError(elemento) {
        elemento.empty();
        elemento.append(new Option(this.config.textos.errorCarga, ''));
        elemento.prop('disabled', true);
    }
    
    mostrarSinOpciones(elemento) {
        elemento.empty();
        elemento.append(new Option(this.config.textos.sinOpciones, ''));
        elemento.prop('disabled', true);
    }
    
    resetearSelect(elemento) {
        elemento.empty();
        this.agregarOpcionPorDefecto(elemento);
        elemento.val('');
    }
    
    // ==========================================
    // MÉTODOS PÚBLICOS PARA USO EXTERNO
    // ==========================================
    
    /**
     * Establece valores de categorización programáticamente
     * @param {Object} valores - {general: id, especifica: id, subcategoria: id}
     */
    async establecerValores(valores) {
        try {
            // console.log('🎯 Estableciendo valores de categorización:', valores);
            
            // Establecer categoría general
            if (valores.general) {
                this.elementos.general.val(valores.general);
                
                // Esperar a que se carguen las específicas
                await this.onCategoriaGeneralChangeAsync(valores.general);
                
                // Establecer categoría específica si existe
                if (valores.especifica && !this.elementos.especifica.prop('disabled')) {
                    // Pequeña espera para asegurar que se carguen las opciones
                    await new Promise(resolve => setTimeout(resolve, 300));
                    this.elementos.especifica.val(valores.especifica);
                    
                    // Esperar a que se carguen las subcategorías
                    await this.onCategoriaEspecificaChangeAsync(valores.especifica);
                    
                    // Establecer subcategoría si existe
                    if (valores.subcategoria && !this.elementos.subcategoria.prop('disabled')) {
                        // Esperar a que se carguen las subcategorías
                        await new Promise(resolve => setTimeout(resolve, 300));
                        this.elementos.subcategoria.val(valores.subcategoria);
                    }
                }
            }
            
            // console.log('✅ Valores de categorización establecidos correctamente');
            
        } catch (error) {
            console.error('❌ Error al establecer valores:', error);
        }
    }
    
    // Versiones async de los métodos de cambio para uso en establecerValores
    async onCategoriaGeneralChangeAsync(valorSeleccionado) {
        this.onCategoriaGeneralChange(valorSeleccionado);
        // Esperar a que termine la carga
        await this.esperarCarga();
    }
    
    async onCategoriaEspecificaChangeAsync(valorSeleccionado) {
        this.onCategoriaEspecificaChange(valorSeleccionado);
        // Esperar a que termine la carga
        await this.esperarCarga();
    }
    
    // Método auxiliar para esperar que termine la carga
    async esperarCarga(maxIntentos = 20) {
        let intentos = 0;
        while (intentos < maxIntentos) {
            // Esperar un poco
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Verificar si hay elementos cargando
            const cargandoEspecifica = this.elementos.especifica.find('option').first().text().includes('Cargando');
            const cargandoSub = this.elementos.subcategoria.find('option').first().text().includes('Cargando');
            
            if (!cargandoEspecifica && !cargandoSub) {
                break; // Ya no está cargando
            }
            
            intentos++;
        }
    }
    
    /**
     * Obtiene los valores actuales de categorización
     * @returns {Object} - {general: id, especifica: id, subcategoria: id}
     */
    obtenerValores() {
        return {
            general: this.elementos.general.val(),
            especifica: this.elementos.especifica.val(),
            subcategoria: this.elementos.subcategoria.val()
        };
    }
    
    /**
     * Valida que las categorías seleccionadas sean válidas
     * @returns {Object} - {valido: boolean, errores: array}
     */
    validarSeleccion() {
        const errores = [];
        
        // Categoría general es obligatoria
        if (!this.elementos.general.val()) {
            errores.push('Debe seleccionar una categoría general');
        }
        
        // Si hay categoría específica seleccionada, debe ser válida
        const valorEspecifica = this.elementos.especifica.val();
        if (valorEspecifica && !this.elementos.especifica.prop('disabled')) {
            const existeEspecifica = this.datos.especificas.some(cat => cat.id == valorEspecifica);
            if (!existeEspecifica) {
                errores.push('La categoría específica seleccionada no es válida');
            }
        }
        
        // Si hay subcategoría seleccionada, debe ser válida
        const valorSubcategoria = this.elementos.subcategoria.val();
        if (valorSubcategoria && !this.elementos.subcategoria.prop('disabled')) {
            const existeSubcategoria = this.datos.subcategorias.some(cat => cat.id == valorSubcategoria);
            if (!existeSubcategoria) {
                errores.push('La subcategoría seleccionada no es válida');
            }
        }
        
        return {
            valido: errores.length === 0,
            errores: errores
        };
    }
    
    /**
     * Resetea todas las selecciones
     */
    resetear() {
        this.elementos.general.val('');
        this.resetearSelect(this.elementos.especifica);
        this.resetearSelect(this.elementos.subcategoria);
        this.elementos.especifica.prop('disabled', true);
        this.elementos.subcategoria.prop('disabled', true);
    }
}

// ==========================================
// FUNCIONES DE UTILIDAD GLOBALES
// ==========================================

/**
 * Crea una instancia del gestor de categorías con configuración por defecto
 * @param {Object} config - Configuración personalizada
 * @returns {CategoriasManager}
 */
function crearGestorCategorias(config = {}) {
    return new CategoriasManager(config);
}
