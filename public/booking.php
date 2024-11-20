<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Booking.php';
require_once '../classes/Gift.php';
require_once '../classes/Event.php';

use Config\Database;
use Classes\Booking;
use Classes\Event;
use Classes\Gift;

ob_start(); // لتجنب أي طباعة قبل التوجيه

$db = (new Database())->connect();

// التحقق من رقم تعريف الفعالية
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo "<p class='error'>رقم الفعالية غير موجود أو غير صالح.</p>";
    exit();
}

$eventId = (int) $_GET['event_id'];
$event = Event::getEventById($db, $eventId);

if (!$event) {
    echo "<p class='error'>لم يتم العثور على الفعالية المطلوبة.</p>";
    exit();
}

// التحقق من طريقة الإرسال وإنشاء الحجز
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numTickets = (int) $_POST['quantity'];
    $recipientEmail = $_POST['recipient_email'] ?? null;
    $userId = $_SESSION['user_id'];
    $totalPrice = $event['price'] * $numTickets;
    $recipientId = null;
    
    // التحقق من صلاحية عدد التذاكر
    if ($numTickets <= 0) {
        echo "<p class='error'>عدد التذاكر يجب أن يكون أكبر من 0.</p>";
        exit();
    }

    if ($numTickets > $event['available_seats']) {
        echo "<p class='error'>عدد التذاكر المطلوب يتجاوز عدد المقاعد المتاحة.</p>";
        exit();
    }

    if (!empty($recipientEmail)) {
        if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            echo "<p class='error'>البريد الإلكتروني غير صالح.</p>";
            exit();
        }
    
        // البحث عن معرف المستخدم بالبريد الإلكتروني
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $recipientEmail);
        $stmt->execute();
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$recipient) {
            echo "<p class='error'>المستلم غير موجود.</p>";
            exit();
        }
    
        $recipientId = $recipient['user_id']; // تعيين معرف المستلم
    } else {
        $recipientId = null; // إذا لم يتم إدخال بريد إلكتروني
    }
    
    
    // إنشاء حجز
    $bookingId = Booking::createPendingBooking($db, $userId, $eventId, $numTickets, $totalPrice, $recipientId);
    

  
    if ($bookingId) {
        // التحقق من البريد الإلكتروني للمستلم إذا تم إدخاله
        if (!empty($recipientEmail)) {
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                echo "<p class='error'>البريد الإلكتروني غير صالح.</p>";
                exit();
            }
            $gift = new Gift($db);
            $giftSuccess = $gift->createGift($bookingId, $recipientEmail);

            if (!$giftSuccess) {
                echo "<div class='error-message'>فشل في إهداء الحجز. الرجاء المحاولة مرة أخرى.</div>";
            } else {
                echo "<div class='success-message'>تم إهداء الحجز بنجاح!</div>";
            }
        }

        // تحديث المقاعد المتاحة
        $updateSeatsStmt = $db->prepare("UPDATE events SET available_seats = available_seats - :numTickets WHERE event_id = :eventId");
        $updateSeatsStmt->bindParam(':numTickets', $numTickets, PDO::PARAM_INT);
        $updateSeatsStmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $updateSeatsStmt->execute();

        // التوجيه إلى checkout.php
        header("Location: checkout.php?booking_id=$bookingId");
        exit();
    } else {
        echo "<div class='error-message'>فشل في إنشاء الحجز. الرجاء المحاولة مرة أخرى.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حجز الفعالية</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- شريط التنقل -->
    <div class="navbar">
        <a href="index.php">الفعاليات</a>
        <a href="my_bookings.php">حجوزاتي</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
        <?php else: ?>
            <a href="login.php" class="login-btn">تسجيل الدخول</a>
        <?php endif; ?>
    </div>

    <div class="booking-container">
        <h1>حجز الفعالية: <?php echo htmlspecialchars($event['title']); ?></h1>
        <p>السعر الأصلي للتذكرة: <?php echo htmlspecialchars($event['price']); ?> دينار ليبي</p>
        <p>المقاعد المتاحة: <?php echo htmlspecialchars($event['available_seats']); ?></p>
        <form method="POST" action="booking.php?event_id=<?php echo $eventId; ?>">
            <label for="quantity">عدد التذاكر:</label>
            <input type="number" name="quantity" id="quantity" min="1" required>
            
            <label for="recipient_email">إرسال كهدية إلى (بريد إلكتروني):</label>
            <input type="email" name="recipient_email" id="recipient_email" placeholder="اختياري" />

            <button type="submit">احجز الآن</button>
        </form>
    </div>

</body>
</html>
