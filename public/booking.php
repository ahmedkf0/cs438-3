<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Booking.php';
require_once '../classes/Event.php';

use Config\Database;
use Classes\Booking;
use Classes\Event;

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

// تحويل السعر إلى float لضمان العمليات الحسابية
$event['price'] = (float) $event['price'];
$event['available_seats'] = (int) $event['available_seats'];

// التحقق من طريقة الإرسال وإنشاء الحجز
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numTickets = (int) $_POST['quantity'];
    $referralCode = $_POST['referral_code'] ?? null; // كود الإحالة (اختياري)
    $userId = $_SESSION['user_id'];
    $totalPrice = $event['price'] * $numTickets;

    // التحقق من صلاحية عدد التذاكر
    if ($numTickets <= 0) {
        echo "<p class='error'>عدد التذاكر يجب أن يكون أكبر من 0.</p>";
        exit();
    }

    if ($numTickets > $event['available_seats']) {
        echo "<p class='error'>عدد التذاكر المطلوب يتجاوز عدد المقاعد المتاحة.</p>";
        exit();
    }

    // التحقق من كود الإحالة إذا كان موجودًا
    if ($referralCode) {
        $stmt = $db->prepare("SELECT user_id FROM users WHERE referral_code = :referralCode");
        $stmt->bindParam(':referralCode', $referralCode, PDO::PARAM_STR);
        $stmt->execute();
        $referrerId = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'] ?? null;

        // تسجيل الإحالة إذا كان كود الإحالة صحيحًا
        if ($referrerId && $referrerId != $userId) {
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_email, referred_status) 
                                  VALUES (:referrerId, (SELECT email FROM users WHERE user_id = :userId), 'pending')");
            $stmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    // إنشاء حجز
    $bookingId = Booking::createPendingBooking($db, $userId, $eventId, $numTickets, $totalPrice);

    if ($bookingId) {
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
        <p>السعر الأصلي للتذكرة: <?php echo htmlspecialchars(number_format($event['price'], 2)); ?> دينار ليبي</p>
        <p>المقاعد المتاحة: <?php echo htmlspecialchars($event['available_seats']); ?></p>
        <form method="POST" action="booking.php?event_id=<?php echo $eventId; ?>">
            <label for="quantity">عدد التذاكر:</label>
            <input type="number" name="quantity" id="quantity" min="1" required>
            <br>
            
            <!-- حقل كود الإحالة -->
            <label for="referral_code">كود الإحالة (اختياري):</label>
            <input type="text" name="referral_code" id="referral_code">
            <br>

            <button type="submit">احجز الآن</button>
        </form>
    </div>

</body>
</html>
