<?php
namespace App\Core;

use App\Core\Interfaces\DatabaseInterface;

class Database implements DatabaseInterface {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $this->host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: null;
        $this->db_name  = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: null;
        $this->username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: null;
        $this->password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: null;
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new \PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch(\PDOException $exception) {
            throw new \RuntimeException("Error de conexión a la base de datos", 0, $exception);
        }
        return $this->conn;
    }

    public function getHost(): string { return $this->host; }
    public function getDbName(): string { return $this->db_name; }
    public function getUsername(): string { return $this->username; }
    public function getPassword(): string { return $this->password; }
}
