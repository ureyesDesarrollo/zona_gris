<?php

namespace App;

use App\Helpers\Logger;
use PDO;
use PDOException;

class Database
{
    private static $instances = [];
    private $pdo;

    private function __construct($type = 'mysql')
    {
        if ($type === 'mysql') {


            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $db   = $_ENV['DB_NAME'] ?? 'progel_system';
            $user = $_ENV['DB_USER'] ?? 'usuario';
            $pass = $_ENV['DB_PASS'] ?? 'password';
            $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
        } elseif ($type === 'sqlsrv') {
            $host = $_ENV['SQLSRV_HOST'] ?? '192.168.0.50';
            $db   = $_ENV['SQLSRV_NAME'] ?? 'PLC_DATA';
            $user = $_ENV['SQLSRV_USER'] ?? 'usuario_sql';
            $pass = $_ENV['SQLSRV_PASS'] ?? 'password_sql';
            $dsn = "sqlsrv:Server=$host;Database=$db";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
        } else {
            throw new \Exception("Tipo de conexión no soportado: $type");
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            Logger::error("Error de conexión: {mensaje}", ['mensaje' => $e->getMessage()]);
            http_response_code(500);
            die('Error interno de base de datos.');
        }
    }

    // Método estático para obtener la instancia única
    public static function getInstance($type = 'mysql')
    {
        if (!isset(self::$instances[$type])) {
            self::$instances[$type] = new self($type);
        }
        return self::$instances[$type];
    }

    // Método para obtener el PDO real
    public function getConnection()
    {
        return $this->pdo;
    }
}
