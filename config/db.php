<?php
namespace Config;

use PDO;
use PDOException;

class Database {
    private string $host = 'localhost';
    private string $db_name = 'event_management'; // تأكد أن هذا الاسم يطابق اسم قاعدة البيانات التي قمت بإنشائها
    private string $username = 'root'; // اسم المستخدم الخاص بقاعدة البيانات
    private string $password = ''; // كلمة المرور (عادةً تكون فارغة في إعدادات XAMPP/WAMP الافتراضية)
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
