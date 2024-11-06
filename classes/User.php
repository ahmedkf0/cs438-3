<?php
namespace Classes;

use PDO;

class User {
    private int $userId;
    private string $name;
    private string $email;
    private string $password;

    public function __construct(int $userId, string $name, string $email, string $password) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    // دوال Getter للوصول إلى الخصائص الخاصة
    public function getUserId(): int {
        return $this->userId;
    }

    public function getName(): string {
        return $this->name;
    }

    // دالة تسجيل الدخول
    public static function login(PDO $db, string $email, string $password): ?User {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $userData['password'])) {
                return new User($userData['user_id'], $userData['name'], $userData['email'], $userData['password']);
            }
        }
        return null;
    }

    public function getRole() {
        return $this->role;
    }
    

    // دالة إنشاء حساب جديد
    public static function register($db, $name, $email, $password, $role) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }
    
}
