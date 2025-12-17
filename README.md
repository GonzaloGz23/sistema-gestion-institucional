# Sistema Institucional - Gestión Administrativa

Sistema web integral para la gestión administrativa de una entidad pública, desarrollado con PHP y MySQL. Incluye módulos para gestión de empleados, capacitaciones, formularios dinámicos, reservas de espacios, y sistema de notificaciones push.

## 🚀 Características Principales

### Gestión de Recursos Humanos
- **Empleados**: Alta, baja, modificación y consulta de empleados
- **Equipos y Áreas**: Organización jerárquica de la estructura institucional
- **Legajos Digitales**: Gestión documental de empleados
- **Prestaciones**: Administración de beneficios y prestaciones

### Sistema de Capacitaciones
- **Gestión de Cursos**: Creación y administración de capacitaciones internas y externas
- **Inscripciones**: Sistema de registro de participantes con cupos
- **Seguimiento**: Control de asistencia y finalización de capacitaciones
- **Categorización**: Organización por categorías generales, específicas y subcategorías
- **Modalidades**: Soporte para cursos presenciales, virtuales y mixtos

### Formularios Dinámicos
- **Constructor de Formularios**: Creación de formularios personalizados sin código
- **Tipos de Campos**: Texto, número, fecha, selección, archivo, etc.
- **Asignación**: Distribución de formularios a empleados o equipos específicos
- **Respuestas**: Recolección y análisis de respuestas

### Gestión de Espacios
- **Edificios y Espacios**: Catálogo de instalaciones
- **Reservas**: Sistema de reserva de espacios con calendario
- **Equipamiento**: Inventario de equipos y recursos

### Sistema de Notificaciones
- **Notificaciones Push**: Integración con Firebase Cloud Messaging
- **Notificaciones en Tiempo Real**: Alertas instantáneas para usuarios
- **Gestión de Dispositivos**: Registro y administración de dispositivos

### Administración de Archivos
- **Gestor de Archivos**: Organización de documentos institucionales
- **Permisos**: Control de acceso por roles
- **Versionado**: Historial de cambios en documentos

## 🛠️ Stack Tecnológico

### Backend
- **PHP 7.4+**: Lenguaje principal del servidor
- **MySQL/MariaDB**: Base de datos relacional
- **PDO**: Capa de abstracción de base de datos

### Frontend
- **HTML5/CSS3**: Estructura y estilos
- **JavaScript (ES6+)**: Lógica del cliente
- **Bootstrap 5**: Framework CSS responsivo
- **jQuery**: Manipulación del DOM y AJAX
- **DataTables**: Tablas interactivas con búsqueda y paginación
- **SweetAlert2**: Alertas y modales elegantes
- **ApexCharts**: Gráficos y visualizaciones

### PWA (Progressive Web App)
- **Service Workers**: Funcionalidad offline
- **Web App Manifest**: Instalación como aplicación
- **Cache API**: Optimización de recursos

### Herramientas de Desarrollo
- **Gulp**: Automatización de tareas
- **npm**: Gestión de dependencias
- **Git**: Control de versiones

## 📋 Requisitos del Sistema

- **Servidor Web**: Apache 2.4+ o Nginx
- **PHP**: 7.4 o superior
- **MySQL**: 5.7+ o MariaDB 10.3+
- **Extensiones PHP requeridas**:
  - PDO
  - pdo_mysql
  - mbstring
  - json
  - session
  - fileinfo
  - gd (para procesamiento de imágenes)

## 🔧 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/sistema-institucional.git
cd sistema-institucional
```

### 2. Configurar Base de Datos

Crear dos bases de datos:

```sql
CREATE DATABASE sistema_institucional CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE sistema_cursos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configurar Credenciales

Editar los archivos de configuración con tus credenciales:

**`backend/config/database.php`**:
```php
// Configuración para producción
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_institucional');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

**`backend/config/database_courses.php`**:
```php
// Configuración para producción
define('COURSES_DB_HOST', 'localhost');
define('COURSES_DB_NAME', 'sistema_cursos');
define('COURSES_DB_USER', 'tu_usuario');
define('COURSES_DB_PASS', 'tu_contraseña');
```

### 4. Importar Esquema de Base de Datos

```bash
mysql -u tu_usuario -p sistema_institucional < database/schema_principal.sql
mysql -u tu_usuario -p sistema_cursos < database/schema_cursos.sql
```

### 5. Configurar Firebase (Opcional)

Si deseas usar notificaciones push:

1. Crear un proyecto en [Firebase Console](https://console.firebase.google.com/)
2. Obtener las credenciales de configuración
3. Actualizar `pages/common/scripts.php` y `pages/common/notificaciones_frontend.php` con tus credenciales

### 6. Instalar Dependencias

```bash
npm install
```

### 7. Compilar Assets

```bash
gulp build
```

### 8. Configurar Permisos

```bash
chmod -R 755 backend/
chmod -R 777 uploads/
chmod -R 777 sessions/
```

## 📁 Estructura del Proyecto

```
sistema-institucional/
├── backend/
│   ├── config/              # Configuraciones de BD y sesiones
│   └── controller/          # Controladores PHP
│       ├── admin/           # Controladores administrativos
│       └── user/            # Controladores de usuario
├── dist/                    # Assets compilados
│   └── assets/
│       ├── css/
│       ├── js/
│       └── images/
├── pages/
│   ├── admin/               # Páginas administrativas
│   ├── user/                # Páginas de usuario
│   └── common/              # Componentes compartidos
├── src/                     # Fuentes originales
├── uploads/                 # Archivos subidos por usuarios
├── sessions/                # Sesiones PHP
├── manuales/                # Documentación
├── index.php                # Punto de entrada
├── manifest.json            # Manifiesto PWA
├── sw.js                    # Service Worker
└── package.json             # Dependencias npm
```

## 👥 Sistema de Roles

El sistema implementa control de acceso basado en roles:

- **Administrador**: Acceso completo a todos los módulos
- **Gestor de RRHH**: Gestión de empleados y legajos
- **Gestor de Capacitaciones**: Administración de cursos
- **Usuario Estándar**: Acceso a formularios y consultas

## 🔐 Seguridad

- **Autenticación por sesiones**: Sistema de login seguro
- **Validación de permisos**: Control de acceso a nivel de página y módulo
- **Prepared Statements**: Protección contra SQL Injection
- **Sanitización de inputs**: Validación de datos del usuario
- **HTTPS recomendado**: Para entornos de producción

## 📱 PWA (Progressive Web App)

El sistema funciona como PWA, permitiendo:

- Instalación en dispositivos móviles y escritorio
- Funcionamiento offline para recursos estáticos
- Notificaciones push
- Experiencia similar a una app nativa

## 🤝 Contribuciones

Este proyecto fue desarrollado como parte de mi trabajo en una entidad pública. El código ha sido sanitizado para uso en portafolio, eliminando información sensible.

## 📄 Licencia

Este proyecto se presenta únicamente con fines demostrativos para portafolio profesional.

## 📧 Contacto

Para consultas sobre este proyecto, puedes contactarme a través de mi perfil de GitHub.

---

**Nota**: Este es un proyecto de portafolio. Todas las credenciales y datos sensibles han sido reemplazados con valores de ejemplo. Para implementación en producción, asegúrate de configurar correctamente todas las credenciales y medidas de seguridad.
