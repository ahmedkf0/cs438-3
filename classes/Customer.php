<?php
namespace Classes;

use PDO;
use DateTime;
use Exception;

require_once 'User.php';

class Customer extends User {
    
    private array $bookings;

    public function __construct(int $userId, string $name, string $email, string $phoneNumber, string $birthdate, string $password) {
        parent::__construct($userId, $name, $email, $phoneNumber, $birthdate, $password);
        $this->bookings = [];
    }

    // إنشاء حجز جديد
    public function bookEvent(PDO $db, int $eventId, int $numTickets, ?string $recipientEmail = null): int {
        try {
            if ($numTickets <= 0) {
                throw new Exception("عدد التذاكر غير صالح.");
            }
    
            $event = $this->getEventById($db, $eventId);
    
            // التحقق من توفر المقاعد
            if ($event['available_seats'] < $numTickets) {
                throw new Exception("عدد المقاعد المتاحة غير كافٍ.");
            }
    
            // جلب معرف المستلم إذا تم تقديم بريد إلكتروني
            $recipientId = null;
            if ($recipientEmail) {
                $stmt = $db->prepare("SELECT user_id FROM users WHERE email = :email");
                $stmt->bindParam(':email', $recipientEmail);
                $stmt->execute();
                $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$recipient) {
                    throw new Exception("المستلم غير موجود.");
                }
    
                $recipientId = $recipient['user_id'];
            }
    
            $totalPrice = $event['price'] * $numTickets;
    
            // إدراج الحجز
            $stmt = $db->prepare("INSERT INTO bookings (user_id, event_id, num_tickets, total_price, status, recipient_id) 
                                  VALUES (:userId, :eventId, :numTickets, :totalPrice, 'Pending', :recipientId)");
            $stmt->bindParam(':userId', $this->userId);
            $stmt->bindParam(':eventId', $eventId);
            $stmt->bindParam(':numTickets', $numTickets);
            $stmt->bindParam(':totalPrice', $totalPrice);
            $stmt->bindParam(':recipientId', $recipientId);
            $stmt->execute();
    
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception("حدث خطأ أثناء الحجز: " . $e->getMessage());
        }
    }
    

    // إلغاء الحجز
    public function cancelBooking(PDO $db, int $bookingId): string {
        try {
            $booking = $this->getBookingById($db, $bookingId);

            if ($booking['hours_until_event'] <= 24) {
                throw new Exception("لا يمكن إلغاء الحجز لأنه متبقي أقل من 24 ساعة على موعد الفعالية.");
            }

            // حذف الحجز
            $deleteStmt = $db->prepare("DELETE FROM bookings WHERE booking_id = :bookingId");
            $deleteStmt->bindParam(':bookingId', $bookingId);
            $deleteStmt->execute();

            // تحديث عدد المقاعد
            $updateSeats = $db->prepare("UPDATE events SET available_seats = available_seats + :numTickets WHERE event_id = :eventId");
            $updateSeats->bindParam(':numTickets', $booking['num_tickets']);
            $updateSeats->bindParam(':eventId', $booking['event_id']);
            $updateSeats->execute();

            return "تم إلغاء الحجز بنجاح.";
        } catch (Exception $e) {
            throw new Exception("حدث خطأ أثناء محاولة إلغاء الحجز: " . $e->getMessage());
        }
    }

    // عرض الحجوزات
    public function viewBookings(PDO $db): array {
        try {
            $stmt = $db->prepare("
                SELECT bookings.booking_id, events.title, bookings.num_tickets, bookings.total_price, 
                       bookings.booking_date, events.date AS event_date, bookings.status,
                       CASE 
                           WHEN bookings.user_id = :userId AND bookings.recipient_id IS NULL THEN 'حجز شخصي'
                           WHEN bookings.recipient_id = :userId THEN 'حجز مهدي إليك'
                           ELSE 'حجز مهدي'
                       END AS booking_type
                FROM bookings
                JOIN events ON bookings.event_id = events.event_id
                WHERE bookings.user_id = :userId OR bookings.recipient_id = :userId
                ORDER BY bookings.booking_date DESC
            ");
            $stmt->bindParam(':userId', $this->userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching bookings: " . $e->getMessage());
            return [];
        }
    }
    
    

    // جلب تفاصيل فعالية
    private function getEventById(PDO $db, int $eventId): array {
        $stmt = $db->prepare("SELECT * FROM events WHERE event_id = :eventId");
        $stmt->bindParam(':eventId', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            throw new Exception("الفعالية غير موجودة.");
        }

        return $event;
    }

    // جلب تفاصيل الحجز
    private function getBookingById(PDO $db, int $bookingId): array {
        $stmt = $db->prepare("SELECT events.date AS event_date, bookings.event_id, bookings.num_tickets, 
                              TIMESTAMPDIFF(HOUR, NOW(), events.date) AS hours_until_event
                              FROM bookings 
                              JOIN events ON bookings.event_id = events.event_id 
                              WHERE bookings.booking_id = :bookingId AND bookings.user_id = :userId");
        $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $this->userId, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$booking) {
            throw new Exception("الحجز غير موجود.");
        }
    
        return $booking;
    }
    
}
