<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Booking.php';
require_once '../classes/Event.php';

use Config\Database;
use Classes\Booking;
use Classes\Event;

$db = (new Database())->connect();

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    die("Event ID is missing or invalid.");
}



$eventId = (int) $_GET['event_id'];
$event = Event::getEventById($db, $eventId);

if (!$event) {
    die("Event not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numTickets = (int) $_POST['quantity'];
    $userId = $_SESSION['user_id'];
    $totalPrice = $event['price'] * $numTickets;

    // إنشاء حجز مؤقت
    $bookingId = Booking::createPendingBooking($db, $userId, $eventId, $numTickets, $totalPrice);

    if ($bookingId) {
        // التوجيه إلى checkout.php مع booking_id
        header("Location: checkout.php?booking_id=$bookingId");
        exit();
    } else {
        echo "Failed to create pending booking.";
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
        <p>السعر الأصلي للتذكرة: <?php echo htmlspecialchars($event['price']); ?> ريال</p>
        <form method="POST" action="booking.php?event_id=<?php echo $eventId; ?>">
            <label for="quantity">عدد التذاكر:</label>
            <input type="number" name="quantity" id="quantity" min="1" required>
            <button type="submit" class="btn">احجز الآن</button>
        </form>
    </div>

</body>
</html>
