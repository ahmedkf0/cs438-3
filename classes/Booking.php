<?php
namespace Classes;

use PDO;
use PDOException;

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

    public static function createPendingBooking(PDO $db, int $userId, int $eventId, int $numTickets, float $totalPrice, ?int $recipientId = null): int {
        try {
            $stmt = $db->prepare("
                INSERT INTO bookings (user_id, event_id, num_tickets, total_price, status, recipient_id)
                VALUES (:userId, :eventId, :numTickets, :totalPrice, 'Pending', :recipientId)
            ");
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':eventId', $eventId);
            $stmt->bindParam(':numTickets', $numTickets);
            $stmt->bindParam(':totalPrice', $totalPrice);
            $stmt->bindParam(':recipientId', $recipientId);
    
            if ($stmt->execute()) {
                return (int) $db->lastInsertId();
            }
        } catch (PDOException $e) {
            error_log("Error creating booking: " . $e->getMessage());
        }
        return 0;
    }
    
    

    public static function confirmBooking(PDO $db, int $bookingId): bool {
        try {
            $stmt = $db->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = :bookingId");
            $stmt->bindParam(':bookingId', $bookingId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error confirming booking: " . $e->getMessage());
            return false;
        }
    }

    public static function cancelBooking(PDO $db, int $bookingId): bool {
        try {
            $stmt = $db->prepare("UPDATE bookings SET status = 'Cancelled' WHERE booking_id = :bookingId");
            $stmt->bindParam(':bookingId', $bookingId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error cancelling booking: " . $e->getMessage());
            return false;
        }
    }

    // دالة لاسترجاع حجز واحد وإنشاء كائن منه
    public static function getBookingById(PDO $db, int $bookingId): ?Booking {
        try {
            $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = :bookingId");
            $stmt->bindParam(':bookingId', $bookingId);
            $stmt->execute();
            $bookingData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($bookingData) {
                return new Booking(
                    $bookingData['user_id'],
                    $bookingData['event_id'],
                    $bookingData['num_tickets'],
                    $bookingData['total_price'],
                    $bookingData['status']
                );
            }
        } catch (PDOException $e) {
            error_log("Error fetching booking: " . $e->getMessage());
        }
        return null;
    }

    // دالة لاسترجاع جميع الحجوزات ككائنات من نوع Booking
    public static function getAllBookings(PDO $db): array {
        try {
            $stmt = $db->query("SELECT * FROM bookings");
            $bookingsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $bookings = [];
            foreach ($bookingsData as $bookingData) {
                $bookings[] = new Booking(
                    $bookingData['user_id'],
                    $bookingData['event_id'],
                    $bookingData['num_tickets'],
                    $bookingData['total_price'],
                    $bookingData['status']
                );
            }
            return $bookings;
        } catch (PDOException $e) {
            error_log("Error fetching bookings: " . $e->getMessage());
            return [];
        }
    }

    // Getter methods
    public function getUserId(): int {
        return $this->userId;
    }

    public function getEventId(): int {
        return $this->eventId;
    }

    public function getNumTickets(): int {
        return $this->numTickets;
    }

    public function getTotalPrice(): float {
        return $this->totalPrice;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public static function giftBooking(PDO $db, int $bookingId, string $recipientEmail): bool {
        try {
            // التحقق من وجود المستلم بالبريد الإلكتروني
            $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $recipientEmail);
            $stmt->execute();
            $recipient = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$recipient) {
                throw new Exception("المستلم غير موجود!");
            }
    
            // تحديث الحجز لتعيين معرف المستلم
            $stmt = $db->prepare("UPDATE bookings SET recipient_id = :recipientId WHERE booking_id = :bookingId");
            $stmt->bindParam(':recipientId', $recipient['id']);
            $stmt->bindParam(':bookingId', $bookingId);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error gifting booking: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }
    
}
