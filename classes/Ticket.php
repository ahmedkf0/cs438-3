<?php
namespace Classes;

use PDO;
use Exception;

class Ticket {
    private int $ticketId;
    private int $eventId;
    private int $userId;
    private string $seatNumber;
    private float $price;
    private bool $isDiscounted;
    private string $status;

    public function __construct(int $ticketId, int $eventId, int $userId, string $seatNumber, float $price, bool $isDiscounted, string $status = 'pending') {
        $this->ticketId = $ticketId;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->seatNumber = $seatNumber;
        $this->price = $price;
        $this->isDiscounted = $isDiscounted;
        $this->status = $status;
    }

    public function confirmBooking(PDO $db): bool {
        try {
            $stmt = $db->prepare("UPDATE tickets SET status = 'confirmed' WHERE ticket_id = :ticketId");
            $stmt->bindParam(':ticketId', $this->ticketId);
            $stmt->execute();
            $this->status = 'confirmed';
            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء تأكيد الحجز: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    public function cancelBooking(PDO $db): bool {
        try {
            $stmt = $db->prepare("UPDATE tickets SET status = 'canceled' WHERE ticket_id = :ticketId");
            $stmt->bindParam(':ticketId', $this->ticketId);
            $stmt->execute();
            $this->status = 'canceled';
            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إلغاء الحجز: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Getters for accessing ticket details
    public function getTicketId(): int {
        return $this->ticketId;
    }

    public function getEventId(): int {
        return $this->eventId;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getSeatNumber(): string {
        return $this->seatNumber;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function isDiscounted(): bool {
        return $this->isDiscounted;
    }

    public function getStatus(): string {
        return $this->status;
    }
}
