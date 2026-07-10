<?php

namespace App;

use PDO;
use PDOException;

class Database {
    private $conn;
    public function getConnection() {
        $this->conn = null;

        try {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $db_name = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASS'];
            $dsn = "mysql:host={$host};port={$port};dbname={$db_name}";
            
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo json_encode(['error' => 'Connection error: ' . $exception->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}