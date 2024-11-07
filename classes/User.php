<?php
namespace Classes;

use PDO;

class User {
    private int $userId;
    private string $name;
    private string $email;
    private string $password;
    private string $phone_number;
    private string $birthdate;
    private string $role; // Add this property
    
    public function __construct(int $userId, string $name, string $email, string $password, string $role) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->birthdate = $birthdate;
        $this->phone_number = $phone_number;
        $this->password = $password;
        $this->role = $role;
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
                return new User($userData['user_id'], $userData['name'], $userData['email'], $userData['password'], $userData['role']);
            }
        }
        return null;
    }
    

    public function getRole() {
        return $this->role;
    }
    

    // دالة إنشاء حساب جديد
    public static function register($db, $name, $email, $password, $role, $phone_number, $birthdate, $occupation = null) {
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone_number, birthdate, occupation) 
                              VALUES (:name, :email, :password, :role, :phone_number, :birthdate, :occupation)");
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':birthdate', $birthdate);
        $stmt->bindParam(':occupation', $occupation);
        return $stmt->execute();
    }
    
    
}
