<?php
namespace Classes;

use PDO;
use PDOException;

class Gift {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createGiftForEvent($userId, $eventId, $recipientEmail, $totalPrice, $numTickets) {
        try {
            // تحقق من صحة البريد الإلكتروني
            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid recipient email: $recipientEmail");
                return false;
            }
    
            // تحقق من أن عدد التذاكر أكبر من 0
            if ($numTickets <= 0) {
                error_log("Number of tickets must be greater than 0: $numTickets");
                return false;
            }
    
            // تحقق من أن السعر الإجمالي أكبر من 0
            if ($totalPrice <= 0) {
                error_log("Total price must be greater than 0: $totalPrice");
                return false;
            }
    
            // تحقق من وجود المستخدم
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                error_log("User not found: $userId");
                return false;
            }
    
            // تحقق من وجود الحدث
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM events WHERE event_id = :event_id");
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                error_log("Event not found: $eventId");
                return false;
            }
    
            // إعداد الاستعلام
            $stmt = $this->db->prepare("
                INSERT INTO gifts (user_id, event_id, recipient_email, total_price, num_tickets)
                VALUES (:user_id, :event_id, :recipient_email, :total_price, :num_tickets)
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':recipient_email', $recipientEmail, PDO::PARAM_STR);
            $stmt->bindParam(':total_price', $totalPrice, PDO::PARAM_STR);
            $stmt->bindParam(':num_tickets', $numTickets, PDO::PARAM_INT);
    
            // تنفيذ الاستعلام
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Gift creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    
    
    

    public function getGiftsForRecipient(string $recipientEmail): array {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM gifts 
                WHERE recipient_email = :recipientEmail
            ");
            $stmt->bindParam(':recipientEmail', $recipientEmail, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching gifts: " . $e->getMessage());
            return [];
        }
    }
    
    
}
