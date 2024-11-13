<?php
session_start(); // بدء جلسة جديدة أو استئناف الحالية
include_once('Connection.pdo.php'); // تضمين ملف الاتصال بقاعدة البيانات

class Events {
    private $conn;
    private $event_id;
    private $title;
    private $description;
    private $date;
    private $time;
    private $location;
    private $price;
    private $available_seats;
    private $age_restriction;
    private $message;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->message = "";
        $this->loadEventData();
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

    // دوال get للحصول على الخصائص
    public function getEventId() {
        return $this->event_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDate() {
        return $this->date;
    }

    public function getTime() {
        return $this->time;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getAvailableSeats() {
        return $this->available_seats;
    }

    public function getAgeRestriction() {
        return $this->age_restriction;
    }
}

$events = new Events($conn); // إنشاء كائن من فصل Events
$events->updateEvent(); // محاولة تحديث الحدث
$message = $events->displayMessage(); // استرجاع الرسالة المعروضة

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل حدث</title>
    <style>
        /* تنسيقات الصفحة */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .event-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: auto;
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border: 1px solid #d6e9c6;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            border: 1px solid #ebccd1;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <main class="event-container">
        <h1>تعديل حدث</h1>
        <?php if ($message) echo $message; // عرض الرسالة إذا كانت موجودة ?>
        
        <form method="POST" id="edit-form">
            <label for="event-id">رقم الحدث:</label>
            <input type="text" name="event_id" value="<?php echo htmlspecialchars($events->getEventId()); ?>" readonly>
            
            <label for="event-title">عنوان الحدث:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($events->getTitle()); ?>" required>
            
            <label for="event-description">شرح عن الحدث:</label>
            <textarea name="description" required><?php echo htmlspecialchars($events->getDescription()); ?></textarea>
            
            <label for="event-date">تاريخ الحدث:</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($events->getDate()); ?>" required>
            
            <label for="event-time">وقت الحدث:</label>
            <input type="time" name="time" value="<?php echo htmlspecialchars($events->getTime()); ?>" required>
            
            <label for="event-location">موقع الحدث:</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($events->getLocation()); ?>" required>
            
            <label for="event-price">سعر الحدث:</label>
            <input type="number" name="price" value="<?php echo htmlspecialchars($events->getPrice()); ?>" required>
            
            <label for="available-seats">عدد الكراسي المتاحة:</label>
            <input type="number" name="available_seats" value="<?php echo htmlspecialchars($events->getAvailableSeats()); ?>" required>
            
            <label for="min-age">الحد الأدنى للعمر:</label>
            <input type="number" name="age_restriction" value="<?php echo htmlspecialchars($events->getAgeRestriction()); ?>" required>
            
            <button type="submit" name="updateevent">تعديل الحدث</button>
        </form>
    </main>
</body>
</html>