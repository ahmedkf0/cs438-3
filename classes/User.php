<?php
namespace Classes;

use PDO;

class User {
    private int $userId;
    private string $name;
    private string $email;
    private string $password;
    private string $role;
    private ?string $birthdate; // Nullable birthdate

    public function __construct(int $userId, string $name, string $email, string $password, string $role, ?string $birthdate = null) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->birthdate = $birthdate ?? ''; // Assign a default empty string if birthdate is null
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
        $query = "SELECT user_id, name, email, password, role, birthdate FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
    
        if ($stmt->rowCount() > 0) {
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $userData['password'])) {
                return new User(
                    $userData['user_id'],
                    $userData['name'],
                    $userData['email'],
                    $userData['password'],
                    $userData['role'],
                    $userData['birthdate'] // This could be null, so ensure the constructor handles it
                );
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
