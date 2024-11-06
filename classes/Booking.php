<?php
namespace Classes;

use PDO;

class Booking {
    private int $userId;
    private int $eventId;
    private int $numTickets;
    private float $totalPrice;
    private string $status;

    public function __construct(int $userId, int $eventId, int $numTickets, float $totalPrice, string $status = "Pending") {
        $this->userId = $userId;
        $this->eventId = $eventId;
        $this->numTickets = $numTickets;
        $this->totalPrice = $totalPrice;
        $this->status = $status;
    }

    public static function createPendingBooking(PDO $db, int $userId, int $eventId, int $numTickets, float $totalPrice): int {
        // إنشاء حجز مع حالة "Pending"
        $stmt = $db->prepare("INSERT INTO bookings (user_id, event_id, num_tickets, total_price, status) VALUES (:userId, :eventId, :numTickets, :totalPrice, 'Pending')");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':eventId', $eventId);
        $stmt->bindParam(':numTickets', $numTickets);
        $stmt->bindParam(':totalPrice', $totalPrice);
        
        if ($stmt->execute()) {
            return $db->lastInsertId();
        }
        return 0;
    }

    public static function confirmBooking(PDO $db, int $bookingId): bool {
        // تحديث حالة الحجز إلى "Confirmed" بعد الدفع
        $stmt = $db->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = :bookingId");
        $stmt->bindParam(':bookingId', $bookingId);
        
        return $stmt->execute();
    }
}
