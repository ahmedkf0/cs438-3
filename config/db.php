<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private string $host = 'localhost'; // اسم المضيف
    private string $db_name = 'event_management'; // اسم قاعدة البيانات
    private string $username = 'root'; // اسم المستخدم
    private string $password = ''; // كلمة المرور (فارغة إذا كنت تستخدم XAMPP/WAMP)
    private ?PDO $conn = null;

    public function connect(): ?PDO {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Database connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}

