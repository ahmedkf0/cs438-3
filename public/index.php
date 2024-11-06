<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Event.php';

use Config\Database;
use Classes\Event;

$db = (new Database())->connect();

if (!$db) {
    die("Database connection error. Please check your configuration.");
}

$events = Event::getAllEvents($db);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الفعاليات</title>
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

    <h1>الفعاليات المتاحة</h1>

    <div class="events-container">
        <?php foreach ($events as $event): ?>
            <div class="event-card">
                <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                <p><?php echo htmlspecialchars($event['description']); ?></p>
                <p><strong>الموقع:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                <p><strong>السعر:</strong> <?php echo htmlspecialchars($event['price']); ?> ريال</p>
                <p><strong>المقاعد المتاحة:</strong> <?php echo htmlspecialchars($event['available_seats']); ?></p>
                <a class="btn" href="booking.php?event_id=<?php echo $event['event_id']; ?>">احجز الآن</a>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

