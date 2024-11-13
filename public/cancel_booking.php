<?php
require_once '../config/db.php';
require_once '../classes/Booking.php';

use Config\Database;
use Classes\Booking;

$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    Booking::cancelBooking($db, $bookingId);
    echo "تم إلغاء الحجز بنجاح!";
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إلغاء الحجز</title>
</head>
<body>
    <h1>إلغاء الحجز</h1>
    <form method="POST" action="cancel_booking.php">
        <label for="booking_id">رقم الحجز:</label>
        <input type="number" name="booking_id" id="booking_id" required>
        <button type="submit">إلغاء الحجز</button>
    </form>
</body>
</html>
