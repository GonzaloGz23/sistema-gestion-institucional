````markdown
# 📋 Módulo de Revisión de Capacitaciones

## 🎯 Propósito
Bandeja donde los usuarios pueden revisar capacitaciones enviadas por equipos, corregir errores y gestionar el flujo de aprobación.

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

backend/controller/admin/revision-capacitaciones/
├── listar_capacitaciones.php                ✅ IMPLEMENTADO
├── obtener_capacitacion.php                 ✅ IMPLEMENTADO
├── categorias/
│   ├── obtener_generales.php                ✅ IMPLEMENTADO
│   ├── obtener_especificas.php              ✅ IMPLEMENTADO
│   └── obtener_subcategorias.php            ✅ IMPLEMENTADO
├── editar_capacitacion.php                  ✅ IMPLEMENTADO
├── cambiar_estado.php                       ✅ IMPLEMENTADO
├── insert_capacitacion.php                  ✅ IMPLEMENTADO
├── subir_imagen.php                         ✅ IMPLEMENTADO (v2.3.0)
└── README.md                                📚 Documentación backend completa
```

## 🔄 Estados del Workflow
```
En Espera → En Revisión → Aprobado
    ↑           ↑           ↑
    └───────────┴───────────┘
    (Retroceso manual permitido)
```

## 📋 Exportación de Datos
**📄 Texto Plano (v2.2.0):** 
- Disponible solo para capacitaciones aprobadas
- Modal con texto formateado listo para copiar
- Selección manual o copia completa
- Compatible con cualquier programa (Word, Google Docs, etc.)
- **Separación de responsabilidades**: Gestión de estados independiente del guardado de datos
- **Feedback visual**: Cambio de estilos btn-primary ↔ btn-outline-secondary + tooltips
- **Reset automático**: Estado inicial al cerrar modal y cambiar pestañaspos, corregir errores y gestionar el flujo de aprobación.

## 📁 Estructura de Archivos
```
pages/admin/
├── revisionC├── backend/controller/admin/revision-capacitaciones/
    ├── listar_capacitaciones.php                ✅ IMPLEMENTADO
    ├── obtener_capacitacion.php                 ✅ IMPLEMENTADO
    ├── categorias/
    │   ├── obtener_generales.php                ✅ IMPLEMENTADO
    │   ├── obtener_especificas.php              ✅ IMPLEMENTADO
    │   └── obtener_subcategorias.php            ✅ IMPLEMENTADO
    ├── editar_capacitacion.php                  ✅ IMPLEMENTADO
    ├── cambiar_estado.php                       ✅ IMPLEMENTADO (v2.1.0)
    ├── generar_pdf.php                          ⏳ PENDIENTE
    └── generar_word.php                         ⏳ PENDIENTEes.php          # Página principal del módulo
└── js/revision-capacitaciones/
    ├── revision-data.js                # Funciones AJAX reales + obtener capacitación
    ├── revision-ui.js                  # Lógica de interfaz de usuario
    └── README.md                       # Esta documentación
└── ../common/js/
    └── categorias-manager.js           # Gestor modular de categorías jerárquicas (COMÚN)
```

## 🔄 Estados del Workflow
```
En Espera → En Revisión → Aprobado
    ↑           ↑           ↑
    └───────────┴───────────┘
    (Retroceso manual permitido)
```

## 📊 Estructura de Datos

### Estructura de Datos

### Categorización Jerárquica (IMPLEMENTADA)
- **Alcance:** interno | estatal
- **Tipo Capacitación:** curso | taller  
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
- [x] **Control dinámico botón Guardar** - Deshabilitación automática en pestaña Gestión
- [x] **Separación lógica de responsabilidades** - Gestión independiente del guardado de datos
- [x] **Sistema de texto plano** - Modal para exportar datos como texto copiable (v2.2.0)
- [x] **Sistema de archivos con preview** - Upload, visualización y eliminación

### ✅ Backend (COMPLETADO - IMPLEMENTACIÓN TOTAL)
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
- [x] **Testing funcional completo** - Verificado flujo end-to-end en diferentes escenarios
- [x] **Controlador cambiar_estado.php** - Workflow manual completo implementado
- [x] **Sistema de imágenes** - Upload con sanitización y nomenclatura específica (v2.3.0)
- [ ] Exportación real PDF/Word

## 🖼️ Sistema de Imágenes (v2.3.0)

### Funcionalidad
- **Imagen por defecto:** `default-course.webp` cuando no hay imagen
- **Upload independiente:** Botón separado con guardado inmediato
- **Sanitización:** Minúsculas, espacios → guiones bajos, sin acentos
- **Nomenclatura:** `{id}-{timestamp}-{nombre_sanitizado}.{ext}`
  - Ejemplo: `123-20251104_143025-curso_php_avanzado.jpg`

### Validaciones
- **Tipos permitidos:** JPG, PNG, WEBP (GIF removido)
- **Tamaño máximo:** 2MB
- **Validación MIME:** Verificación real del tipo de archivo (no solo extensión)

### Almacenamiento
- **Directorio:** `/images/training/`
- **Base de datos:** Solo nombre de archivo en campo `ruta_imagen` (VARCHAR(255))
- **Eliminación:** Imagen anterior se elimina automáticamente al subir nueva

### Interfaz
- **Previsualización:** Muestra imagen actual o default
- **Botón upload:** Se habilita solo cuando hay archivo válido seleccionado
- **Feedback visual:** Botón cambia de gris a verde cuando hay archivo
- **Alertas:** SweetAlert2 para confirmación y errores

### Backend
- **Endpoint:** `backend/controller/admin/revision-capacitaciones/subir_imagen.php`
- **Método:** POST (multipart/form-data)
- **Parámetros:** `id_capacitacion`, `imagen` (file)
- **Respuesta:** JSON con nombre de archivo generado

Ver documentación completa del backend en:  
📚 `backend/controller/admin/revision-capacitaciones/README.md`

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

### 🎯 Fase 1: Testing y Validación (COMPLETADO ✅)
1. **Testing funcional completo del guardado (✅ COMPLETADO)**
   - ✅ Validaciones frontend funcionando
   - ✅ Integración HTTP implementada
   - ✅ Backend con DELETE real implementado
   - ✅ Testing end-to-end completado y funcionando

2. **Validación de optimizaciones BD (✅ COMPLETADO)**
   - ✅ Eliminadas columnas 'esta_eliminado' innecesarias
   - ✅ DELETE real en lugar de soft delete
   - ✅ Sin duplicación de registros
   - ✅ Consultas optimizadas

3. **Sistema de cambio de estados manual (✅ COMPLETADO - v2.1.0)**
   - ✅ Backend cambiar_estado.php implementado
   - ✅ Validaciones de transición y permisos
   - ✅ Botones dinámicos con habilitación/deshabilitación
   - ✅ Confirmaciones con SweetAlert2
   - ✅ Actualización automática de DataTable y modal
   - ✅ Integración completa frontend ↔ backend

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
/sistema-gestion-institucional/pages/admin/revisionCapacitaciones.php
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
    ├── editar_capacitacion.php                  ✅ IMPLEMENTADO
    ├── cambiar_estado.php                       ✅ IMPLEMENTADO
    ├── generar_pdf.php                          ⏳ PENDIENTE
    └── generar_word.php                         ⏳ PENDIENTE
```

### Log de Cambios
```
📅 10 de septiembre de 2025 - v2.1.2 - CONTROL DINÁMICO BOTÓN GUARDAR
✅ Implementado control inteligente del botón "Guardar" según pestaña activa:
    - Pestaña Gestión: Botón DESHABILITADO (controles independientes)
    - Otras pestañas: Botón HABILITADO (permite guardado de datos)
✅ Feedback visual mejorado: cambio de estilos btn-primary ↔ btn-outline-secondary
✅ Tooltip informativo: explica por qué está deshabilitado en Gestión
✅ Eventos Bootstrap: shown.bs.tab y hidden.bs.modal para control automático
✅ UX optimizada: separación clara entre edición de datos y gestión de estados
✅ Reset automático: botón se resetea al cerrar modal y volver a primera pestaña

📅 10 de septiembre de 2025 - v2.1.1 - CORRECCIÓN ESTRUCTURA BD + PERMISOS SIMPLIFICADOS
✅ Corregido mapeo completo para estructura real de BD:
    - Tabla capacitaciones: campo estado_id (FK) en lugar de estado_actual
    - Tabla estados_capacitacion: IDs y nombres con espacios ("en espera", "en revisión")
    - Mapeo bidireccional: BD ↔ Frontend (con/sin espacios)
✅ Backend: Consultas actualizadas para usar estado_id y JOINs correctos
✅ Frontend: formatearEstado() maneja ambos formatos de estados
✅ Sistema de permisos simplificado: eliminada validación por entidad
✅ Confianza en sistema módulos/roles: si accede al módulo → tiene permisos
✅ Controlador más limpio enfocado solo en lógica de cambio de estado
✅ Compatibilidad total con estructura real de base de datos

📅 10 de septiembre de 2025 - v2.1.0 - SISTEMA DE CAMBIO DE ESTADOS MANUAL COMPLETO
✅ Implementado completamente cambiar_estado.php con validaciones de transición
✅ Backend: Validación de permisos, transacciones, verificación de entidad
✅ Frontend: Botones dinámicos con habilitación/deshabilitación según estado
✅ Confirmaciones con SweetAlert2 usando common.js del proyecto
✅ Actualización automática de DataTable y modal tras cambio de estado
✅ Función cambiarEstadoManual() específica para botones del modal
✅ Función actualizarBotonesEstado() para UI dinámica
✅ Integración completa: Frontend ↔ Backend con manejo de errores
✅ Estados permitidos: en_espera ↔ en_revision ↔ aprobado (bidireccional)
✅ Arquitectura MVC respetada con separación de responsabilidades

📅 8 de septiembre de 2025 - v2.0.1 - CORRECCIÓN CAMPOS ALCANCE/TIPO
✅ Corregido mapeo de campos alcance y tipo_capacitacion:
    - Campo alcance (BD) ↔ Campo #alcance (HTML): interno | estatal 
    - Campo tipo_capacitacion (BD) ↔ Campo #tipoCapacitacion (HTML): curso | taller
✅ Frontend: Corregido mapeo en revision-ui.js (población y recolección de datos)
✅ Backend: Corregido mapeo en editar_capacitacion.php (parámetros y valores por defecto)
✅ Verificado: HTML con selectores correctos y valores apropiados
✅ Documentación actualizada con mapeo definitivo

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
**Última actualización:** 17 de noviembre de 2025  
**Estado:** ✅ SISTEMA COMPLETO - Estados y formatos actualizados  
**Versión:** 2.4.0 - Actualización estados y formatos

## 📋 Historial de Cambios

📅 17 de noviembre de 2025 - v2.4.0 - ACTUALIZACIÓN ESTADOS Y FORMATOS
✅ Estado "Finalizado" cambiado a "Cerrado" con badge bg-danger (rojo)
✅ Sistema de imágenes: Removido soporte para GIF
✅ Formatos aceptados actualizados: JPG, PNG, WEBP únicamente
✅ Validación frontend y backend sincronizada sin GIF
✅ Texto de formatos actualizado en UI: "JPG, PNG, WEBP. Tamaño máximo: 2MB"
✅ Permisos optimizados: 777 en /images/training/ para XAMPP

📅 4 de noviembre de 2025 - v2.3.0 - SISTEMA DE IMÁGENES COMPLETO
✅ Implementado subir_imagen.php con validaciones completas
✅ Sanitización de nombres: minúsculas, espacios → guiones bajos, sin acentos
✅ Nomenclatura específica: id-timestamp-nombre_sanitizado.ext
✅ Validación MIME real (no solo extensión)
✅ Guardado solo del nombre de archivo en BD (campo ruta_imagen)
✅ Eliminación automática de imagen anterior al subir nueva
✅ Directorio de destino: /images/training/
✅ Botón de upload independiente con feedback visual
✅ Imagen por defecto: default-course.webp
✅ Documentación backend completa en README.md del controlador

📅 10 de septiembre de 2025 - v2.2.0 - TEXTO PLANO
✅ Eliminados botones y funciones de exportar PDF/Word
✅ Implementado sistema de texto plano para capacitaciones aprobadas
✅ Modal con formato estructurado y selección manual de texto
✅ Botón "Copiar Todo" para copia completa del contenido
✅ Botón disponible en tabla (solo aprobadas) y modal (dinámico)
✅ Instrucciones claras para el usuario
✅ Compatible con cualquier programa (Word, Google Docs, etc.)

📅 9 de septiembre de 2025 - v2.1.2 - CONTROL BOTÓN GUARDAR
✅ Implementado control dinámico del botón Guardar según pestaña activa
✅ Deshabilitación automática en pestaña "Gestión"
✅ Separación lógica: gestión de estados vs guardado de datos
✅ Feedback visual con cambio de estilos y tooltips informativos
✅ Reset automático al cerrar modal y cambiar pestañas
✅ UX mejorada con separación clara de responsabilidades

````
