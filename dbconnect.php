<?php
class Database {
    private $host = 'localhost';  
    private $user = 'root';    
    private $pass = '';       
    private $db = 'courseenrollment'; 
    private $dbconnect = null;

    private static $instance = null;

    private function __construct() {
        try {
            $this->dbconnect = new PDO(
                "mysql:host={$this->host};courseenrollment={$this->courseenrollment}",
                $this->username,
                $this->password
            );
            $this->dbconnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->dbconnect;
    }

    public function closeConnection() {
        $this->dbconnect = null;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->dbconnect->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }

    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->dbconnect->lastInsertId();
    }
}
