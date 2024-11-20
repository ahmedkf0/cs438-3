<?php
namespace Classes;

use PDO;
use PDOException;

class Gift {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createGift(int $bookingId, string $recipientEmail): bool {
        try {
            // التحقق من وجود المستلم بالبريد الإلكتروني
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $recipientEmail);
            $stmt->execute();
            $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$recipient) {
                throw new Exception("المستلم غير موجود.");
            }

            // تحديث الحجز لتعيين معرف المستلم
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET recipient_id = :recipientId 
                WHERE booking_id = :bookingId
            ");
            $stmt->bindParam(':recipientId', $recipient['id']);
            $stmt->bindParam(':bookingId', $bookingId);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating gift: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            return false;
        }
    }

    public function getGiftsForRecipient(int $recipientId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM bookings 
                WHERE recipient_id = :recipientId
            ");
            $stmt->bindParam(':recipientId', $recipientId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching gifts: " . $e->getMessage());
            return [];
        }
    }
}
