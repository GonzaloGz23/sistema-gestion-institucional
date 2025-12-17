````markdown
# 📋 Módulo de Revisión de Capacitaciones

## 🎯 Propósito
Bandeja donde los usuarios pueden revisar capacitaciones enviadas por otros equipos, corregir errores y gestionar el flujo de aprobación.

## 📁 Estructura de Archivos
```
pages/admin/
├── revisionCapacitaciones.php          # Página principal del módulo
└── js/revision-capacitaciones/
    ├── revision-data.js                # Funciones AJAX reales + obtener capacitación
    ├── revision-ui.js                  # Lógica de interfaz de usuario
    └── README.md                       # Esta documentación
└── ../common/js/
    └── categorias-manager.js           # Gestor modular de categorías jerárquicas (COMÚN)
```

## 🔄 Estados del Workflow
```
En Espera → En Revisión → Aprobado → Cerrado
    ↑           ↑           ↑           ↑
    └───────────┴───────────┴───────────┘
    (Retroceso manual permitido)
```

## 📊 Estructura de Datos

### Categorización Jerárquica (IMPLEMENTADA)
- **Alcance:** interno | estatal
- **Tipo:** curso | taller  
- **Categoría General:** desarrollo | gestión | diseño | datos | seguridad
- **Categoría Específica:** web | mobile | backend | frontend | etc. (condicionada a general)
- **Subcategoría:** javascript | php | python | react | mysql | etc. (condicionada a específica)
- **Modalidad:** presencial | virtual | mixto
- **Lugar:** (solo si presencial o mixto)

### Datos Básicos
- Nombre, slogan, objetivo, descripción
- Destinatarios, requisitos

### Fechas y Ubicación
- Fechas: inscripción, inicio, fin
- Duración clase (minutos), cantidad encuentros, cupos
- Horarios múltiples: día, hora inicio, hora fin

### Contenido
- Temas (obligatorios) y subtemas (opcionales)

### Gestión
- Imagen del curso
- Equipo creador
- Link inscripción (auto-generado, no editable)
- Estado actual

## 🎨 Funcionalidades Implementadas

### ✅ Frontend (Completamente Implementado)
- [x] **Interfaz de listado con DataTables** - Configuración completa con filtros
- [x] **Modal de edición con formulario por tabs** - UI/UX completa
- [x] **Sistema de validaciones completo por tabs** - Validaciones específicas para cada sección
- [x] **Cargador jerárquico de categorías** - CategoriasManager refactorizado como componente reutilizable
- [x] **Gestión de estados visuales** - Estados, botones y mapeo de colores
- [x] **Gestión de horarios dinámicos** - Agregar/eliminar/validar horarios
- [x] **Gestión de temas dinámicos** - CRUD completo con upload de archivos
- [x] **Funciones de debug para validaciones** - testValidacionCompleta(), testValidacionPorTabs()
- [x] **Integración con arquitectura dual** - BD sistema_institucional + sistema_cursos
- [x] **Componente reutilizable categorías** - Movido a `/pages/common/js/` para uso global
- [x] **Sistema de archivos con preview** - Upload, visualización y eliminación

### ✅ Backend (IMPLEMENTADO - PENDIENTE TESTING)
- [x] **Controlador listar_capacitaciones.php** - Listado con BD distribuidas
- [x] **Controlador obtener_capacitacion.php** - Detalles completos con JOINs
- [x] **APIs de categorías jerárquicas** - obtener_generales.php, obtener_especificas.php, obtener_subcategorias.php
- [x] **Conexión con base de datos `sistema_cursos`** - Arquitectura dual
- [x] **Reconstrucción de jerarquía de categorías** - Mapeo completo en backend
- [x] **Filtrado por entidad** - Seguridad y aislamiento de datos
- [x] **Validaciones de autenticación** - Control de acceso
- [x] **Mapeo de estados** - Compatibilidad BD ↔ Frontend
- [x] **Controlador editar_capacitacion.php COMPLETO** - FULL SYNC con DELETE real, validaciones, transacciones
- [x] **Integración frontend ↔ backend** - Llamadas HTTP reales implementadas
- [x] **Optimización BD** - Eliminadas columnas 'esta_eliminado' innecesarias
- [ ] **Testing funcional completo** - Verificar flujo end-to-end en diferentes escenarios
- [ ] Controlador cambiar_estado.php (workflow)
- [ ] Exportación real PDF/Word

## 🔧 Configuración Técnica

### Sistema de Validaciones Implementado
```javascript
// Validaciones por pestaña (EXCLUYE pestaña "Gestión")
validarFormularioCompleto() {
    validarCategorizacion()    // Campos obligatorios + lugar condicional
    validarDatosBasicos()     // Todos los campos obligatorios
    validarFechas()           // Fechas + coherencia horarios/encuentros
    validarContenido()        // Al menos un tema obligatorio
}

// Validaciones específicas:
- Categorización: lugar obligatorio solo si modalidad ≠ virtual
- Fechas: cantidad horarios = cantidad encuentros
- Contenido: al menos 1 tema (subtemas opcionales)
- Gestión: EXCLUIDA del proceso de validación
```

### Arquitectura de Categorías Modular
```javascript
// Gestor centralizado y reutilizable
class CategoriasManager {
    constructor(selectores, endpoints)
    async cargarGenerales()
    async cargarEspecificas(generalId) 
    async cargarSubcategorias(especificaId)
    habilitarSelector(tipo)
    deshabilitarSelector(tipo)
}

// Endpoints API implementados:
/backend/controller/admin/revision-capacitaciones/categorias/obtener_generales.php
/backend/controller/admin/revision-capacitaciones/categorias/obtener_especificas.php?general_id=X
/backend/controller/admin/revision-capacitaciones/categorias/obtener_subcategorias.php?especifica_id=Y
```

### Base de Datos Distribuidas
- **BD Principal:** `sistema_institucional` (usuarios, entidades, equipos)
- **BD Cursos:** `sistema_cursos` (capacitaciones, estados, horarios, temas, categorías)
- **Conexión:** Archivos `database.php` y `database_courses.php`
- **Relación:** `equipos.id_equipo` ↔ `capacitaciones.equipo_id`
- **Filtro:** Por `id_entidad` del usuario actual

### Arquitectura Backend
```
backend/controller/admin/revision-capacitaciones/
├── listar_capacitaciones.php              ✅ IMPLEMENTADO
├── obtener_capacitacion.php               ✅ IMPLEMENTADO
├── categorias/
│   ├── obtener_generales.php              ✅ IMPLEMENTADO
│   ├── obtener_especificas.php            ✅ IMPLEMENTADO
│   └── obtener_subcategorias.php          ✅ IMPLEMENTADO
├── editar_capacitacion.php                📋 ESTRUCTURA PREPARADA
├── cambiar_estado.php                     ⏳ PENDIENTE
├── generar_pdf.php                        ⏳ PENDIENTE
└── generar_word.php                       ⏳ PENDIENTE
```

### Mapeo de Estados
```
Base de Datos → Frontend
─────────────────────────
borrador     → borrador
en espera    → en_espera
en revisión  → en_revision
aprobado     → aprobado
cerrado      → cerrado (anteriormente "finalizado")
```

### Responsividad
- **Escritorio (>991px):** Pestañas horizontales
- **Tablet/Móvil (≤991px):** Todas las pestañas se muestran verticalmente una debajo de otra

### Librerías
- DataTables (tabla responsiva)
- Bootstrap 5 (UI)
- SweetAlert2 (alertas)
- jQuery (manipulación DOM)

## 📋 Próximos Pasos Planificados

### 🎯 Fase 1: Testing y Validación (INMEDIATO - PENDIENTE)
1. **Testing funcional completo del guardado (🧪 URGENTE)**
   - ✅ Validaciones frontend funcionando
   - ✅ Integración HTTP implementada
   - ✅ Backend con DELETE real implementado
   - 🧪 **PENDIENTE:** Testing end-to-end con diferentes escenarios:
     - Modificar solo datos básicos
     - Modificar horarios (agregar/quitar)
     - Modificar temas y subtemas
     - Cambiar categorización completa
     - Validar transacciones en caso de error
     - Verificar actualización de estados

2. **Validación de optimizaciones BD (✅ COMPLETADO)**
   - ✅ Eliminadas columnas 'esta_eliminado' innecesarias
   - ✅ DELETE real en lugar de soft delete
   - ✅ Sin duplicación de registros
   - ✅ Consultas optimizadas

3. **Verificación de casos edge**
   - Testing con datos límite
   - Manejo de errores de conexión
   - Validación de permisos
   - Comportamiento con datos corruptos

### 🎯 Fase 2: Funcionalidades Avanzadas
1. **Exportación real de documentos**
   - PDF con información específica
   - Word/Office con plantillas
   - Plantillas personalizables

2. **Validaciones y permisos avanzados**
   - Validación de campos por estado
   - Sistema de roles específicos
   - Permisos granulares por acción

3. **Optimizaciones y mejoras**
   - Cache de consultas frecuentes
   - Paginación en backend
   - Filtros avanzados (fecha, estado, equipo)

### 🎯 Fase 3: Integración Completa
1. **Notificaciones en tiempo real**
   - Alertas por cambios de estado
   - Integración con sistema de notificaciones

2. **Dashboard de métricas**
   - Estadísticas de capacitaciones
   - Tiempos de revisión
   - Reportes de gestión

3. **API para integración externa**
   - Endpoints RESTful
   - Documentación Swagger
   - Autenticación por tokens

## 🐛 Problemas Conocidos y Resoluciones

### ✅ Resueltos
- ✅ Columnas de tabla desbalanceadas (ID oculto agregado)
- ✅ Modalidad "mixto" agregada
- ✅ Responsive design en pestañas
- ✅ Modo tablet/móvil (≤991px) muestra todas las pestañas verticalmente
- ✅ Información completa disponible en todos los dispositivos
- ✅ Arquitectura de BD distribuidas implementada
- ✅ Filtrado por entidad funcionando
- ✅ **Error tabla 'equipos' en BD incorrecta** - Separadas consultas por conexión
- ✅ **JavaScript function redeclaration** - Eliminada duplicación de funciones
- ✅ **Categorías bloqueadas/deshabilitadas** - Implementada lógica condicional inteligente
- ✅ **Modal carga datos simulados** - Implementado mapeo completo de datos reales
- ✅ **Categorías jerárquicas no funcionaban** - Gestor modular CategoriasManager implementado
- ✅ **Console logs saturando debugger** - Comentados para producción, preservados para debug
- ✅ **Sistema de validaciones implementado** - Validación completa por pestañas con reglas específicas

### ⚠️ En Seguimiento
- **Guardado temporal:** Funciona solo en memoria hasta backend completo
- **Exportación:** Solo muestra alertas hasta implementar generadores reales
- **Estados workflow:** Cambio manual hasta implementar cambiar_estado.php
- **Validaciones:** Implementadas pero pendientes de integración con guardado real

### 🔧 Notas Técnicas
- **Sesiones requeridas:** El módulo requiere usuario autenticado
- **Permisos:** Actualmente valida autenticación, permisos específicos pendientes
- **CORS:** Configurado para same-origin (cookies de sesión)
- **Debug logs:** Comentados pero preservados para desarrollo futuro

## 💡 Decisiones de Diseño

### Arquitectura Modular
- **CategoriasManager (COMÚN):** Clase reutilizable para gestión jerárquica de categorías - Ubicado en `/pages/common/js/categorias-manager.js`
- **Separación de responsabilidades:** revision-data.js (AJAX), revision-ui.js (DOM), categorias-manager.js (lógica categorías - COMÚN)
- **Estado inteligente:** Los selectores se habilitan/deshabilitan según el contexto

### Guardado Integral
- **Decisión:** Un botón "Guardar Cambios" guarda todas las pestañas
- **Razón:** El usuario revisor necesita visión completa antes de aprobar
- **Comportamiento:** Cambio automático a "en_revision" al guardar

### URL del Módulo
```
/sistemaInstitucional/pages/admin/revisionCapacitaciones.php
```

### Estructura de Archivos Actualizada
```
pages/admin/
├── revisionCapacitaciones.php                    # Página principal del módulo
├── js/revision-capacitaciones/
│   ├── revision-data.js                          # AJAX real completo
│   ├── revision-ui.js                            # Lógica de interfaz de usuario
│   └── README.md                                 # Esta documentación
├── ../common/js/
│   └── categorias-manager.js                     # Gestor modular de categorías (COMÚN/REUTILIZABLE)
└── backend/controller/admin/revision-capacitaciones/
    ├── listar_capacitaciones.php                ✅ IMPLEMENTADO
    ├── obtener_capacitacion.php                 ✅ IMPLEMENTADO
    ├── categorias/
    │   ├── obtener_generales.php                ✅ IMPLEMENTADO
    │   ├── obtener_especificas.php              ✅ IMPLEMENTADO
    │   └── obtener_subcategorias.php            ✅ IMPLEMENTADO
    ├── editar_capacitacion.php                  📋 ESTRUCTURA PREPARADA
    ├── cambiar_estado.php                       ⏳ PENDIENTE
    ├── generar_pdf.php                          ⏳ PENDIENTE
    └── generar_word.php                         ⏳ PENDIENTE
```

### Log de Cambios
```
📅 7 de septiembre de 2025 - v2.0.0 - BACKEND COMPLETO
✅ Implementado completamente editar_capacitacion.php con todas las funciones
✅ Integración frontend ↔ backend con llamadas HTTP reales
✅ Validaciones completas del backend con mapeo correcto de datos
✅ Optimización: DELETE real en lugar de soft delete para cronogramas y temas
✅ Eliminadas columnas 'esta_eliminado' innecesarias de BD
✅ Correcciones de mapeo: alcance/modalidad, estructura jerárquica de temas
✅ Sistema transaccional completo con rollback en caso de errores
⚠️  PENDIENTE: Testing funcional completo end-to-end

📅 6 de septiembre de 2025 - v1.2.2
✅ Implementado sistema completo de validaciones por pestañas
✅ Validación de categorización con lugar condicional según modalidad
✅ Validación de coherencia entre horarios configurados y cantidad de encuentros
✅ Validación de campos obligatorios en todas las pestañas (excepto Gestión)
✅ Sistema de validaciones listo para integración con backend

📅 6 de septiembre de 2025 - v1.2.1
✅ Movido categorias-manager.js a /pages/common/js/ para reutilización en otros módulos
✅ Actualizado path de carga en revisionCapacitaciones.php
✅ Actualizada documentación con nueva estructura de archivos
✅ Componente ahora es completamente reutilizable entre módulos

📅 4 de septiembre de 2025 - v1.2.0
✅ Implementado obtener_capacitacion.php - Detalles completos con JOINs
✅ Creado CategoriasManager - Gestor modular y reutilizable
✅ Implementadas APIs de categorías jerárquicas (generales, específicas, subcategorías)
✅ Mapeo completo de datos reales al modal - Todos los campos poblados
✅ Lógica condicional inteligente en categorías - Enable/disable según contexto
✅ Resolución de errores críticos (BD, JavaScript, UI)
✅ Console logs comentados para producción - Preservados para debug
✅ Documentación actualizada con arquitectura modular

📅 3 de septiembre de 2025 - v1.1.0
✅ Implementado backend listar_capacitaciones.php
✅ Integración AJAX real en revision-data.js  
✅ Arquitectura de BD distribuidas funcionando
✅ Filtrado por entidad implementado
✅ Mapeo de estados BD ↔ Frontend
✅ Validación de autenticación

📅 2 de septiembre de 2025 - v1.0.0  
✅ Maqueta funcional completa con responsive mejorado
✅ 5 pestañas con datos simulados
✅ Sistema responsivo para móvil/tablet
```

---
**Fecha creación:** 30 de agosto de 2025  
**Última actualización:** 7 de septiembre de 2025  
**Estado:** ✅ Backend completo implementado - 🧪 Testing funcional pendiente  
**Versión:** 2.0.0 - BACKEND COMPLETO + OPTIMIZACIONES BD

````
