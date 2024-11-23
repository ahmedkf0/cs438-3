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
            $stmt = $this->db->prepare("
                INSERT INTO gifts (user_id, event_id, recipient_email, total_price, num_tickets)
                VALUES (:user_id, :event_id, :recipient_email, :total_price, :num_tickets)
            ");
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':recipient_email', $recipientEmail, PDO::PARAM_STR);
            $stmt->bindParam(':total_price', $totalPrice, PDO::PARAM_STR);
            $stmt->bindParam(':num_tickets', $numTickets, PDO::PARAM_INT);
    
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
