<?php
require_once '../config/db.php';

use Config\Database;

$db = (new Database())->connect();

// كود اختبار إدخال حجز جديد
try {
    $db->beginTransaction();

    // إدخال بسيط في جدول bookings
    $stmt = $db->prepare("INSERT INTO bookings (user_id, event_id, num_tickets, total_price, status) VALUES (1, 1, 2, 200, 'confirmed')");
    $stmt->execute();

    // استرجاع lastInsertId بعد الإدخال
    $bookingId = $db->lastInsertId();

    if ($bookingId) {
        echo "Test successful. Booking ID: " . $bookingId;
        $db->commit();
    } else {
        echo "Failed to retrieve booking ID.";
        $db->rollBack();
    }

} catch (\Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage();
}
