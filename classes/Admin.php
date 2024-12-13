<?php

include_once('User.php');
require_once 'Event.php';

class User {
    private int $userId;
    private string $name;
    private string $email;
    private string $password;
    private string $role;
    private ?string $birthdate;

    public function __construct(int $userId, string $name, string $email, string $password, string $role, ?string $birthdate = null) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->birthdate = $birthdate ?? '';
    }
}

class Admin extends User {
    private $db; // تعريف خاصية db هنا

    // Constructor
    public function __construct($userId, $name, $email, $phoneNumber, $birthdate, $password, $conn) {
        parent::__construct($userId, $name, $email, $phoneNumber, $birthdate, $password);
        $this->db = $conn; // تخزين الاتصال بقاعدة البيانات
    }

    // Method to login
    public function login($name, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE name = :name AND password = :password");
        $stmt->execute(['name' => $name, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
    
        if ($user) {
            // Set user properties if login is successful
            $this->name = $user->name;
            $this->password = $user->password; // Note: Avoid storing passwords directly
    
            header('Location: Selection.html');
            exit();
        } else {
            // تخزين رسالة الخطأ في جلسة
            $_SESSION['error_message'] = "اسم المستخدم أو كلمة المرور غير صحيحة.";
            header('Location: login.php'); // إعادة توجيه إلى صفحة تسجيل الدخول
            exit();
        }
    }

    // Method to update user data
    public function updateUser($userId, $data) {
        // تحضير استعلام SQL لتحديث بيانات المستخدم
        if (!empty($data['password'])) {
            // استخدم كلمة المرور كما هي دون تشفير
            $password = $data['password']; // كلمة المرور النصية
            $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email, 
                    phone_number = :phone_number, 
                    password = :password, 
                    birthdate = :birthdate, 
                    role = :role 
                    WHERE user_id = :user_id";
        } else {
            $sql = "UPDATE users SET 
                    name = :name, 
                    email = :email, 
                    phone_number = :phone_number, 
                    role = :role 
                    WHERE user_id = :user_id";
        }
    
        // تحضير الاستعلام
        $stmt = $this->db->prepare($sql);
    
        // ربط القيم
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone_number', $data['phone_number']);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':role', $data['role']);
    
        // ربط كلمة المرور فقط إذا كانت موجودة
        if (!empty($data['password'])) {
            $stmt->bindParam(':password', $password); // استخدام كلمة المرور النصية
        }
    
        // ربط تاريخ الميلاد فقط إذا كان موجودًا
        if (!empty($data['birthdate'])) {
            $stmt->bindParam(':birthdate', $data['birthdate']);
        }
    
        // تنفيذ الاستعلام
        return $stmt->execute();
    }

    public function deleteUserWithReferences($userId) {
        try {
            // بداية المعاملة
            $this->db->beginTransaction();
    
            // حذف بيانات الحجوزات
            $sqlBookings = "DELETE FROM bookings WHERE user_id = :user_id";
            $stmtBookings = $this->db->prepare($sqlBookings);
            $stmtBookings->bindParam(':user_id', $userId);
            $stmtBookings->execute();
    
            // حذف بيانات الدفع المرتبطة بالحجوزات
            $sqlPayments = "DELETE FROM payments WHERE booking_id IN (SELECT booking_id FROM bookings WHERE user_id = :user_id)";
            $stmtPayments = $this->db->prepare($sqlPayments);
            $stmtPayments->bindParam(':user_id', $userId);
            $stmtPayments->execute();
    
            // حذف بيانات الهدايا
            $sqlGifts = "DELETE FROM gifts WHERE user_id = :user_id";
            $stmtGifts = $this->db->prepare($sqlGifts);
            $stmtGifts->bindParam(':user_id', $userId);
            $stmtGifts->execute();
    
            // حذف التذاكر المرتبطة بالمستخدم
            $sqlTickets = "DELETE FROM tickets WHERE user_id = :user_id";
            $stmtTickets = $this->db->prepare($sqlTickets);
            $stmtTickets->bindParam(':user_id', $userId);
            $stmtTickets->execute();
    
            // حذف المستخدم
            $sqlUser = "DELETE FROM users WHERE user_id = :user_id";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->bindParam(':user_id', $userId);
            $stmtUser->execute();
    
            // إذا تم كل شيء بنجاح، قم بتأكيد المعاملة
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // في حالة حدوث خطأ، قم بالتراجع عن المعاملة
            $this->db->rollBack();
            // تسجيل الخطأ
            error_log($e->getMessage()); // سجل الخطأ في ملف السجل
            return false;
        }
    }

        // دالة للبحث عن مستخدم بواسطة الاسم
        public function getUserByName($name) {
            $sql = "SELECT * FROM users WHERE name LIKE :name LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $searchTerm = '%' . $name . '%';
            $stmt->bindParam(':name', $searchTerm);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

    
            // دالة للبحث عن مستخدم بواسطة ID
    public function getUserById($userId) {
        // تأكد من أن لديك اتصال قاعدة البيانات متاحًا
        if (!isset($this->db)) {
            throw new Exception("Database connection not initialized.");
        }
    
        // تحضير استعلام SQL للبحث عن المستخدم
        $sql = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
    
        // تحضير الاستعلام
        $stmt = $this->db->prepare($sql);
        
        // ربط القيمة
        $stmt->bindParam(':user_id', $userId);
        
        // تنفيذ الاستعلام
        $stmt->execute();
        
        // إرجاع نتائج المستخدم كصف مصفوفة
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    }


    


?>