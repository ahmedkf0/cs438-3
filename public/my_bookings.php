<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Booking.php';

use Config\Database;
use Classes\Booking;

date_default_timezone_set('Asia/Riyadh');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$db = (new Database())->connect();

if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $bookingId = (int) $_GET['cancel'];

    $stmt = $db->prepare("SELECT events.date AS event_date, bookings.event_id, bookings.num_tickets, TIMESTAMPDIFF(HOUR, NOW(), events.date) AS hours_until_event 
                          FROM bookings 
                          JOIN events ON bookings.event_id = events.event_id 
                          WHERE bookings.booking_id = :bookingId AND bookings.user_id = :userId");
    $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        if ($booking['hours_until_event'] > 24) {
            $deleteBooking = $db->prepare("DELETE FROM bookings WHERE booking_id = :bookingId");
            $deleteBooking->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
            $deleteBooking->execute();

            $updateSeats = $db->prepare("UPDATE events SET available_seats = available_seats + :numTickets WHERE event_id = :eventId");
            $updateSeats->bindParam(':numTickets', $booking['num_tickets'], PDO::PARAM_INT);
            $updateSeats->bindParam(':eventId', $booking['event_id'], PDO::PARAM_INT);
            $updateSeats->execute();

            $message = "<p class='success'>تم حذف الحجز بنجاح.</p>";
        } else {
            $message = "<p class='error'>لا يمكن إلغاء الحجز لأنه متبقي أقل من 24 ساعة على موعد الفعالية.</p>";
        }
    } else {
        $message = "<p class='error'>لم يتم العثور على الحجز المطلوب.</p>";
    }
}

$stmt = $db->prepare("SELECT bookings.booking_id, events.title, bookings.num_tickets, bookings.total_price, bookings.booking_date, events.date AS event_date, bookings.status, 
                      TIMESTAMPDIFF(HOUR, NOW(), events.date) AS hours_until_event
                      FROM bookings 
                      JOIN events ON bookings.event_id = events.event_id 
                      WHERE bookings.user_id = :userId 
                      ORDER BY bookings.booking_date DESC");
$stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>حجوزاتي</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- شريط التنقل -->
    <div class="navbar">
        <a href="index.php">الفعاليات</a>
        <a href="my_bookings.php">حجوزاتي</a>
        <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
    </div>

    <h1>حجوزاتك</h1>

    <?php if (!empty($message)) echo $message; ?>

    <?php if (count($bookings) > 0): ?>
        <table>
            <tr>
                <th>رقم الحجز</th>
                <th>اسم الفعالية</th>
                <th>عدد التذاكر</th>
                <th>السعر الإجمالي</th>
                <th>تاريخ الحجز</th>
                <th>تاريخ الفعالية</th>
                <th>إلغاء الحجز</th>
            </tr>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                    <td><?php echo htmlspecialchars($booking['title']); ?></td>
                    <td><?php echo htmlspecialchars($booking['num_tickets']); ?></td>
                    <td><?php echo htmlspecialchars($booking['total_price']); ?> ريال</td>
                    <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['event_date']); ?></td>
                    <td>
                        <?php if ($booking['hours_until_event'] > 24): ?>
                            <a href="my_bookings.php?cancel=<?php echo $booking['booking_id']; ?>" onclick="return confirm('هل أنت متأكد من إلغاء الحجز؟');">إلغاء</a>
                        <?php else: ?>
                            غير متاح
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>ليس لديك أي حجوزات حتى الآن.</p>
    <?php endif; ?>

</body>
</html>
