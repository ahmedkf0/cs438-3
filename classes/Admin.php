<?php

require_once 'User.php';
require_once 'Event.php';


class Admin extends User {
    // Constructor
    public function __construct($userId, $name, $email, $phoneNumber, $birthdate, $password) {
        parent::__construct($userId, $name, $email, $phoneNumber, $birthdate, $password);
    }

    // Method to login
    public function login($conn, $name, $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE name = :name AND password = :password");
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
    // Additional methods specific to Admin can be added here 




  
    
    public function insertEvent($conn) {
        if ($this->validate()) {
            try {
                $sql = "INSERT INTO events (event_id, title, description, date, time, location, price, available_seats, age_restriction) VALUES (:event_id, :title, :description, :date, :time, :location, :price, :available_seats, :age_restriction)";
                $stmt = $conn->prepare($sql);
    
                // تخزين القيم في متغيرات
                $eventId = $this->getEventId();
                $title = $this->getTitle();
                $description = $this->getDescription();
                $date = $this->getDate();
                $time = $this->getTime();
                $location = $this->getLocation();
                $price = $this->getPrice();
                $availableSeats = $this->getAvailableSeats();
                $ageRestriction = $this->getAgeRestriction();
    
                // تمرير المتغيرات إلى bindParam
                $stmt->bindParam(':event_id', $eventId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':date', $date);
                $stmt->bindParam(':time', $time);
                $stmt->bindParam(':location', $location);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':available_seats', $availableSeats);
                $stmt->bindParam(':age_restriction', $ageRestriction);
    
                $stmt->execute();
                return "<div class='success-message'>تم إضافة الحدث</div>";
            } catch(PDOException $e) {
                return "<div class='error-message'>حدث خطأ: " . $e->getMessage() . "</div>";
            }
        } else {
            return "<div class='error-message'>التحقق من البيانات فشل. يرجى التحقق من المدخلات.</div>";
        }
    }
    public function deleteEvent($conn) {
        try {
            // بدء المعاملة
            $conn->beginTransaction();

            // حذف التذاكر المرتبطة
            $ticketStmt = $conn->prepare("DELETE FROM tickets WHERE event_id = :event_id");
            $ticketStmt->execute(['event_id' => $this->event_id]);

            // حذف الخصومات المرتبطة
            $discountStmt = $conn->prepare("DELETE FROM discount WHERE event_id = :event_id");
            $discountStmt->execute(['event_id' => $this->event_id]);

            // حذف الحجوزات المرتبطة
            $bookingStmt = $conn->prepare("DELETE FROM bookings WHERE event_id = :event_id");
            $bookingStmt->execute(['event_id' => $this->event_id]);

            // الحصول على booking_ids المرتبطة بهذا event_id
            $bookingIds = $conn->prepare("SELECT booking_id FROM bookings WHERE event_id = :event_id");
            $bookingIds->execute(['event_id' => $this->event_id]);
            $bookingIdsArray = $bookingIds->fetchAll(PDO::FETCH_COLUMN);

            // حذف طرق الدفع المرتبطة
            if (!empty($bookingIdsArray)) {
                $placeholders = implode(',', array_fill(0, count($bookingIdsArray), '?'));
                $paymentStmt = $conn->prepare("DELETE FROM payments WHERE booking_id IN ($placeholders)");
                $paymentStmt->execute($bookingIdsArray);
            }

            // حذف الحدث نفسه
            $eventStmt = $conn->prepare("DELETE FROM events WHERE event_id = :event_id");
            $eventStmt->execute(['event_id' => $this->event_id]);

            // التحقق من نجاح العملية
            $conn->commit();
            return "The event has been deleted.";
        } catch (Exception $e) {
            // التراجع عن المعاملة في حالة حدوث خطأ
            $conn->rollBack();
            return "حدث خطأ أثناء الحذف: " . $e->getMessage();
        }
    }

    // دالة لجلب جميع الأحداث
    public static function fetchAllEvents($conn) {
        $stmt = $conn->prepare("SELECT event_id, title, description, date, time FROM events");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

     // تحميل بيانات الحدث من الجلسة
     private function loadEventData() {
        if (isset($_SESSION['event_data'])) {
            $event_data = $_SESSION['event_data']; // استرجاع بيانات الحدث
            ($_SESSION['event_data']); // مسح بيانات الحدث من الجلسة بعد استخدامها
            
            // تعيين القيم إلى الخصائص
            $this->event_id = $event_data['event_id'];
            $this->title = $event_data['title'];
            $this->description = $event_data['description'];
            $this->date = $event_data['date'];
            $this->time = $event_data['time'];
            $this->location = $event_data['location'];
            $this->price = $event_data['price'];
            $this->available_seats = $event_data['available_seats'];
            $this->age_restriction = $event_data['age_restriction'];
        } else {
            $_SESSION['error_message'] = "يجب البحث عن حدث أولاً."; // رسالة خطأ إذا لم توجد بيانات
            header('Location: searchevent.php'); // إعادة توجيه إلى صفحة البحث
            exit();
        }
    }
    public function setEventId($event_id) {
        $this->event_id = $event_id;
    }
    // تحديث الحدث في قاعدة البيانات
    public function updateEvent() {
        if (isset($_POST['updateevent'])) {
            $event_id = $_POST['event_id']; // استرجاع event_id من النموذج
    
            // تحديث الحدث في قاعدة البيانات
            $stmt = $this->conn->prepare("UPDATE events SET title = ?, description = ?, date = ?, time = ?, location = ?, price = ?, available_seats = ?, age_restriction = ? WHERE event_id = ?");
            $updated = $stmt->execute([
                $_POST['title'],
                $_POST['description'],
                $_POST['date'],
                $_POST['time'],
                $_POST['location'],
                $_POST['price'],
                $_POST['available_seats'],
                $_POST['age_restriction'],
                $event_id // استخدام event_id من البيانات المستلمة
            ]);

            if ($updated) {
                $_SESSION['success_message'] = "تم تحديث الحدث بنجاح.";
                header('Location: editevent.php'); // إعادة توجيه بعد التحديث
                exit();
            } else {
                $this->message = "<div class='error-message'>حدث خطأ أثناء تحديث الحدث.</div>";
            }
        }
    }

    // عرض الرسالة إذا كانت موجودة
    public function displayMessage() {
        if (isset($_SESSION['error_message'])) {
            $this->message = "<div class='error-message'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']); // مسح رسالة الخطأ بعد استخدامها
        } elseif (isset($_SESSION['success_message'])) {
            $this->message = "<div class='success-message'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']); // مسح رسالة النجاح بعد استخدامها
        }
        return $this->message;
    }
    
    

    private function validate() {
        if (empty($this->event_id) || !ctype_digit($this->event_id)) {
            return false;
        }
        if (empty($this->title) || strlen($this->title) < 3) {
            return false;
        }
        if (empty($this->description) || strlen($this->description) < 10) {
            return false;
        }
        if (empty($this->date) || !preg_match('/\d{4}-\d{2}-\d{2}/', $this->date)) {
            return false;
        }
        if (empty($this->time)) {
            return false;
        }
        if (empty($this->location)) {
            return false;
        }
        if (empty($this->price) || !is_numeric($this->price) || $this->price < 0) {
            return false;
        }
        if (empty($this->available_seats) || !ctype_digit($this->available_seats) || $this->available_seats < 0) {
            return false;
        }
        if (empty($this->age_restriction) || !ctype_digit($this->age_restriction) || $this->age_restriction < 0) {
            return false;
        }
        return true;
    }
}
?>