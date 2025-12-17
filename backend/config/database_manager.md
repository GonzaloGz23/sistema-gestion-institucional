# Guía de Uso del Database Manager

## 1. Inicialización del Sistema

```php
// Cargar el gestor
require_once 'backend/config/database_manager.php';

// Obtener conexión principal (SIGE)
$mainDb = DatabaseManager::getConnection('main');

// Obtener conexión de cursos
$coursesDb = DatabaseManager::getConnection('courses');
```

## 2. Operaciones Simples

```php
// Consulta en BD principal
$stmt = $mainDb->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Consulta en BD de cursos
$stmt = $coursesDb->prepare("SELECT * FROM cursos WHERE activo = 1");
$stmt->execute();
$courses = $stmt->fetchAll();
```

## 3. Transacciones Distribuidas

```php
$operations = [
    [
        'database' => 'main',
        'sql' => 'UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?',
        'params' => [$userId]
    ],
    [
        'database' => 'courses',
        'sql' => 'INSERT INTO inscripciones (usuario_id, curso_id) VALUES (?, ?)',
        'params' => [$userId, $courseId]
    ]
];

DatabaseManager::executeDistributedTransaction($operations);
```