<?php
class DatabaseManager
{
    private static $connections = [];

    public static function getConnection($database = 'main')
    {
        if (!isset(self::$connections[$database])) {
            self::$connections[$database] = self::createConnection($database);
        }
        return self::$connections[$database];
    }

    private static function createConnection($database)
    {
        $esLocal = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

        switch ($database) {
            case 'main':
                // Configuración SIGE principal
                $config = [
                    'host' => 'localhost',
                    'dbname' => $esLocal ? 'sistema_institucional' : 'production_db_name',
                    'user' => $esLocal ? 'root' : 'production_db_user',
                    'pass' => $esLocal ? '' : 'your_secure_password_here'
                ];
                break;

            case 'courses':
                // Configuración base de datos de cursos
                $config = [
                    'host' => 'localhost',
                    'dbname' => $esLocal ? 'sistema_cursos' : 'production_courses_db_name',
                    'user' => $esLocal ? 'root' : 'production_courses_db_user',
                    'pass' => $esLocal ? '' : 'your_secure_password_here'
                ];
                break;

            default:
                throw new Exception("Base de datos no configurada: $database");
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
            $pdo->exec("SET time_zone = '-03:00'");
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Error conectando a $database: " . $e->getMessage());
        }
    }

    // Método para transacciones distribuidas
    public static function executeDistributedTransaction($operations)
    {
        $connections = [];

        try {
            // Iniciar transacciones en todas las bases de datos necesarias
            foreach ($operations as $operation) {
                $db = $operation['database'];
                if (!isset($connections[$db])) {
                    $connections[$db] = self::getConnection($db);
                    $connections[$db]->beginTransaction();
                }
            }

            // Ejecutar operaciones
            foreach ($operations as $operation) {
                $pdo = $connections[$operation['database']];
                $stmt = $pdo->prepare($operation['sql']);
                $stmt->execute($operation['params'] ?? []);
            }

            // Confirmar todas las transacciones
            foreach ($connections as $pdo) {
                $pdo->commit();
            }

            return true;

        } catch (Exception $e) {
            // Revertir todas las transacciones
            foreach ($connections as $pdo) {
                if ($pdo->inTransaction()) {
                    $pdo->rollback();
                }
            }
            throw $e;
        }
    }
}
?>