# 🔧 Backend - Módulo de Revisión de Capacitaciones

## 📁 Estructura de Archivos

```
backend/controller/admin/revision-capacitaciones/
├── listar_capacitaciones.php           ✅ Listado con filtros y paginación
├── obtener_capacitacion.php            ✅ Detalles completos con JOINs
├── editar_capacitacion.php             ✅ Actualización completa con transacciones
├── cambiar_estado.php                  ✅ Workflow manual de estados
├── insert_capacitacion.php             ✅ Creación de nuevas capacitaciones
├── subir_imagen.php                    ✅ Upload de imágenes de capacitación
└── categorias/
    ├── obtener_generales.php           ✅ Categorías generales
    ├── obtener_especificas.php         ✅ Categorías específicas (filtrado)
    └── obtener_subcategorias.php       ✅ Subcategorías (filtrado)
```

## 🎯 Controladores Implementados

### 📋 listar_capacitaciones.php
**Propósito:** Obtener listado de capacitaciones con filtros

**Método:** GET  
**Headers requeridos:** Session activa  
**Parámetros:** Ninguno (filtra automáticamente por entidad del usuario)

**Respuesta exitosa:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Curso de PHP Avanzado",
      "equipo": "Equipo Backend",
      "estado": "en_revision",
      "fecha_inicio": "2025-11-15",
      "cupos": 30
    }
  ]
}
```

**Características:**
- Filtrado automático por entidad del usuario
- JOIN con tabla equipos para nombre del equipo
- Mapeo de estados de BD a frontend

---

### 🔍 obtener_capacitacion.php
**Propósito:** Obtener detalles completos de una capacitación específica

**Método:** GET  
**Headers requeridos:** Session activa  
**Parámetros:** `id` (query string)

**Ejemplo:** `?id=123`

**Respuesta exitosa:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "nombre": "Curso de PHP Avanzado",
    "alcance": "interno",
    "tipo_capacitacion": "curso",
    "categoria_general_id": 1,
    "categoria_especifica_id": 3,
    "subcategoria_id": 5,
    "modalidad": "presencial",
    "lugar": "Aula 101",
    "slogan": "Aprende PHP moderno",
    "objetivo": "...",
    "descripcion": "...",
    "destinatarios": "...",
    "requisitos": "...",
    "fecha_inscripcion_inicio": "2025-11-01",
    "fecha_inscripcion_fin": "2025-11-10",
    "fecha_inicio": "2025-11-15",
    "fecha_fin": "2025-12-15",
    "duracion_clase": 120,
    "cantidad_encuentros": 8,
    "cupos": 30,
    "ruta_imagen": "123-20251104_143025-php_avanzado.jpg",
    "estado_id": 2,
    "estado": "en_revision",
    "cronogramas": [
      {
        "id": 1,
        "dia_semana": "lunes",
        "hora_inicio": "14:00:00",
        "hora_fin": "16:00:00"
      }
    ],
    "temas": [
      {
        "id": 1,
        "tema": "POO en PHP",
        "subtema": "Clases y objetos",
        "archivo_url": "/uploads/capacitacion/archivos/tema1.pdf"
      }
    ]
  }
}
```

**Características:**
- JOIN múltiples para reconstruir jerarquía completa
- Datos de cronogramas (horarios)
- Datos de temas y subtemas con archivos
- Verificación de pertenencia a entidad

---

### 💾 editar_capacitacion.php
**Propósito:** Actualizar información completa de una capacitación

**Método:** POST  
**Headers requeridos:** Session activa  
**Content-Type:** application/json

**Body de ejemplo:**
```json
{
  "id_capacitacion": 123,
  "nombre": "Curso de PHP Avanzado Actualizado",
  "alcance": "interno",
  "tipo_capacitacion": "curso",
  "categoria_general_id": 1,
  "categoria_especifica_id": 3,
  "subcategoria_id": 5,
  "modalidad": "presencial",
  "lugar": "Aula 101",
  "slogan": "Aprende PHP moderno",
  "objetivo": "...",
  "descripcion": "...",
  "destinatarios": "...",
  "requisitos": "...",
  "fecha_inscripcion_inicio": "2025-11-01",
  "fecha_inscripcion_fin": "2025-11-10",
  "fecha_inicio": "2025-11-15",
  "fecha_fin": "2025-12-15",
  "duracion_clase": 120,
  "cantidad_encuentros": 8,
  "cupos": 30,
  "cronogramas": [
    {
      "dia_semana": "lunes",
      "hora_inicio": "14:00:00",
      "hora_fin": "16:00:00"
    }
  ],
  "temas": [
    {
      "tema": "POO en PHP",
      "subtema": "Clases y objetos",
      "archivo_url": "/uploads/capacitacion/archivos/tema1.pdf"
    }
  ]
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Capacitación actualizada correctamente",
  "id_capacitacion": 123
}
```

**Características:**
- Sistema transaccional completo
- DELETE real de cronogramas y temas antiguos
- Inserción de nuevos registros
- Rollback automático en caso de error
- Validaciones de campos requeridos
- Verificación de pertenencia a entidad

---

### 🔄 cambiar_estado.php
**Propósito:** Cambiar el estado de una capacitación (workflow manual)

**Método:** POST  
**Headers requeridos:** Session activa  
**Content-Type:** application/json

**Body de ejemplo:**
```json
{
  "id_capacitacion": 123,
  "nuevo_estado": "en_revision"
}
```

**Estados permitidos:**
- `en_espera` → `en_revision` → `aprobado`
- Bidireccional (se puede retroceder)

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Estado actualizado correctamente",
  "nuevo_estado": "en_revision",
  "id_capacitacion": 123
}
```

**Características:**
- Validación de transiciones válidas
- Mapeo bidireccional de estados (BD ↔ Frontend)
- Verificación de permisos y entidad
- Sistema transaccional

---

### 🖼️ subir_imagen.php
**Propósito:** Subir imagen para una capacitación con sanitización y nomenclatura específica

**Método:** POST (multipart/form-data)  
**Headers requeridos:** Session activa

**Parámetros:**
- `id_capacitacion` (POST): ID de la capacitación
- `imagen` (FILE): Archivo de imagen

**Nomenclatura de archivo:**
```
{id_capacitacion}-{timestamp}-{nombre_sanitizado}.{ext}
Ejemplo: 123-20251104_143025-curso_php_avanzado.jpg
```

**Validaciones:**
- **Tipos permitidos:** JPG, PNG, WEBP (GIF removido en v2.4.0)
- **Tamaño máximo:** 2MB
- **Validación MIME:** Verificación real del tipo de archivo
- **Sanitización:** Minúsculas, espacios → guiones bajos, sin acentos

**Respuesta exitosa:**
```json
{
  "success": true,
  "message": "Imagen subida correctamente",
  "ruta_imagen": "123-20251104_143025-curso_php_avanzado.jpg",
  "nombre_archivo": "123-20251104_143025-curso_php_avanzado.jpg"
}
```

**Errores comunes:**
```json
{
  "success": false,
  "error": "String data, right truncated: 1406 Data too long for column 'ruta_imagen'"
}
```
⚠️ **Solución:** La columna `ruta_imagen` debe tener suficiente tamaño (VARCHAR(255) recomendado)

**Características:**
- Guarda solo el **nombre del archivo** en BD (no la ruta completa)
- Eliminación automática de imagen anterior
- Permisos 0644 en archivo subido
- Directorio de destino: `/images/training/`
- Actualización automática del campo `ruta_imagen` en tabla `capacitaciones`

**Función de sanitización:**
```php
function sanitizarNombreArchivo($nombre) {
    $nombre = strtolower($nombre);
    $nombre = iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
    $nombre = preg_replace('/[^a-z0-9._-]/', '_', $nombre);
    $nombre = preg_replace('/_+/', '_', $nombre);
    return trim($nombre, '_');
}
```

---

### 📂 APIs de Categorías

#### obtener_generales.php
**Método:** GET  
**Respuesta:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "nombre": "Desarrollo"},
    {"id": 2, "nombre": "Diseño"}
  ]
}
```

#### obtener_especificas.php
**Método:** GET  
**Parámetros:** `general_id`  
**Respuesta:**
```json
{
  "success": true,
  "data": [
    {"id": 3, "nombre": "Web", "categoria_general_id": 1},
    {"id": 4, "nombre": "Mobile", "categoria_general_id": 1}
  ]
}
```

#### obtener_subcategorias.php
**Método:** GET  
**Parámetros:** `especifica_id`  
**Respuesta:**
```json
{
  "success": true,
  "data": [
    {"id": 5, "nombre": "JavaScript", "categoria_especifica_id": 3},
    {"id": 6, "nombre": "PHP", "categoria_especifica_id": 3}
  ]
}
```

---

## 🔐 Seguridad

### Autenticación
Todos los endpoints requieren sesión activa:
```php
require_once __DIR__ . '/../../../config/session_config.php';
require_once __DIR__ . '/../../../config/usuario_actual.php';

if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}
```

### Filtrado por Entidad
Todas las consultas filtran por `id_entidad` del usuario:
```php
$id_entidad = $_SESSION['usuario']['id_entidad'];
WHERE equipos.id_entidad = ?
```

### Validación de Entrada
- **Parámetros numéricos:** `intval()` o `floatval()`
- **Datos JSON:** Validación con `json_decode()` y verificación de errores
- **Archivos:** Validación de tipo MIME real, no solo extensión
- **SQL:** Uso de prepared statements en todas las consultas

---

## 🗃️ Base de Datos

### Arquitectura Dual
- **Base de datos principal:** `sistema_institucional`
  - Usuarios, entidades, equipos
  - Conexión: `database.php` → Variable `$pdo`

- **Base de datos de cursos:** `sistema_cursos`
  - Capacitaciones, estados, cronogramas, temas, categorías
  - Conexión: `database_courses.php` → Variable `$pdoCourses`

### Tablas Principales

#### capacitaciones
```sql
- id (PK)
- nombre
- alcance (interno|estatal)
- tipo_capacitacion (curso|taller)
- categoria_general_id (FK)
- categoria_especifica_id (FK)
- subcategoria_id (FK)
- modalidad (presencial|virtual|mixto)
- lugar (nullable)
- slogan, objetivo, descripcion
- destinatarios, requisitos
- fecha_inscripcion_inicio, fecha_inscripcion_fin
- fecha_inicio, fecha_fin
- duracion_clase (minutos)
- cantidad_encuentros, cupos
- ruta_imagen (VARCHAR(255) - solo nombre archivo)
- equipo_id (FK → sistema_institucional.equipos)
- estado_id (FK)
- borrado (0|1)
```

⚠️ **Importante:** El campo `ruta_imagen` guarda **solo el nombre del archivo**, no la ruta completa.

Ejemplo:
- ✅ Correcto: `123-20251104_143025-curso_php.jpg`
- ❌ Incorrecto: `/sistemaInstitucional/images/training/123-20251104_143025-curso_php.jpg`

La ruta completa se construye en el frontend según necesidad.

#### cronogramas
```sql
- id (PK)
- capacitacion_id (FK)
- dia_semana
- hora_inicio
- hora_fin
```

#### temas
```sql
- id (PK)
- capacitacion_id (FK)
- tema (obligatorio)
- subtema (opcional)
- archivo_url (opcional)
```

#### estados_capacitacion
```sql
- id (PK)
- nombre (en espera|en revisión|aprobado|cerrado|borrador)
```

---

## 🔄 Mapeo de Estados

### Base de Datos → Frontend
```
"en espera"   → "en_espera"
"en revisión" → "en_revision"
"aprobado"    → "aprobado"
"cerrado"     → "cerrado" (anteriormente "finalizado")
"borrador"    → "borrador"
```

### Función de Mapeo (Backend)
```php
function mapearEstadoBD($estadoBD) {
    return str_replace(' ', '_', strtolower($estadoBD));
}

function mapearEstadoFrontend($estadoFrontend) {
    return str_replace('_', ' ', $estadoFrontend);
}
```

---

## 📊 Manejo de Errores

### Respuestas de Error Estándar
```json
{
  "success": false,
  "error": "Descripción del error"
}
```

### Códigos HTTP
- **200:** Operación exitosa
- **400:** Petición inválida (datos faltantes o incorrectos)
- **401:** No autenticado
- **403:** Sin permisos
- **404:** Recurso no encontrado
- **405:** Método no permitido
- **500:** Error del servidor

### Sistema Transaccional
```php
try {
    $pdoCourses->beginTransaction();
    
    // Operaciones de BD...
    
    $pdoCourses->commit();
} catch (Exception $e) {
    $pdoCourses->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
```

---

## 🧪 Testing

### Pruebas Recomendadas

#### 1. Listar Capacitaciones
```bash
curl -X GET "http://localhost/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/listar_capacitaciones.php" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### 2. Obtener Capacitación
```bash
curl -X GET "http://localhost/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/obtener_capacitacion.php?id=123" \
  -H "Cookie: PHPSESSID=tu_session_id"
```

#### 3. Editar Capacitación
```bash
curl -X POST "http://localhost/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/editar_capacitacion.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"id_capacitacion": 123, "nombre": "Nuevo nombre", ...}'
```

#### 4. Cambiar Estado
```bash
curl -X POST "http://localhost/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/cambiar_estado.php" \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -d '{"id_capacitacion": 123, "nuevo_estado": "en_revision"}'
```

#### 5. Subir Imagen
```bash
curl -X POST "http://localhost/sistemaInstitucional/backend/controller/admin/revision-capacitaciones/subir_imagen.php" \
  -H "Cookie: PHPSESSID=tu_session_id" \
  -F "id_capacitacion=123" \
  -F "imagen=@/ruta/a/imagen.jpg"
```

---

## 📝 Notas Técnicas

### Timezone
Todos los endpoints usan:
```php
date_default_timezone_set('America/Argentina/Buenos_Aires');
```

### Encoding
Todas las respuestas JSON usan UTF-8:
```php
header('Content-Type: application/json; charset=utf-8');
```

### Prepared Statements
Todas las consultas usan prepared statements para prevenir SQL injection:
```php
$stmt = $pdoCourses->prepare("SELECT * FROM capacitaciones WHERE id = ?");
$stmt->execute([$id]);
```

### Upload de Archivos
**Directorio de imágenes:** `/images/training/`

**Configuración recomendada:**
```php
// php.ini
upload_max_filesize = 2M
post_max_size = 3M
```

**Permisos del directorio:**
```bash
chmod 755 /opt/lampp/htdocs/sistemaInstitucional/images/training/
chown daemon:daemon /opt/lampp/htdocs/sistemaInstitucional/images/training/
```

---

## 🚀 Próximas Mejoras

- [ ] Sistema de caché para consultas frecuentes
- [ ] Paginación en backend para listar capacitaciones
- [ ] Filtros avanzados (fecha, estado, equipo)
- [ ] Notificaciones por cambios de estado
- [ ] Logs de auditoría para cambios
- [ ] Compresión automática de imágenes
- [ ] Generación de thumbnails
- [ ] API REST completa con versionado

---

**Fecha creación:** 4 de noviembre de 2025  
**Última actualización:** 17 de noviembre de 2025  
**Versión:** 1.1.0  
**Estado:** ✅ IMPLEMENTADO Y ACTUALIZADO

## 📋 Changelog Backend

### v1.1.0 - 17 de noviembre de 2025
- ✅ Estado "Finalizado" actualizado a "Cerrado" en mapeo de estados
- ✅ Removido soporte GIF del sistema de imágenes (subir_imagen.php)
- ✅ Validación MIME actualizada: solo JPG, PNG, WEBP
- ✅ Documentación actualizada con formatos y estados correctos

### v1.0.0 - 4 de noviembre de 2025
- ✅ Implementación inicial completa de todos los controladores
- ✅ Sistema de imágenes con sanitización
- ✅ APIs de categorías jerárquicas
- ✅ Sistema transaccional completo
