<?php
namespace Classes;

use PDO;
use Exception;

class Event {
    private int $eventId;
    private string $title;
    private string $description;
    private string $date;
    private string $time;
    private string $location;
    private float $price;
    private int $availableSeats;
    private int $ageRestriction;

    public function __construct(int $eventId, string $title, string $description, string $date, string $time, string $location, float $price, int $availableSeats, int $ageRestriction) {
        $this->eventId = $eventId;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->price = $price;
        $this->availableSeats = $availableSeats;
        $this->ageRestriction = $ageRestriction;
    }

    // Getter methods
    public function getEventId(): int {
        return $this->eventId;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getTime(): string {
        return $this->time;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function getPrice(): float {
        return $this->price;
    }

    public function getAvailableSeats(): int {
        return $this->availableSeats;
    }

    public function getAgeRestriction(): int {
        return $this->ageRestriction;
    }

    public static function getAllEvents(PDO $db): array {
        try {
            $stmt = $db->query("SELECT * FROM events");
            $eventsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $events = [];
            foreach ($eventsData as $eventData) {
                $events[] = new Event(
                    $eventData['event_id'],
                    $eventData['title'],
                    $eventData['description'],
                    $eventData['date'],
                    $eventData['time'],
                    $eventData['location'],
                    $eventData['price'],
                    $eventData['available_seats'],
                    $eventData['age_restriction']
                );
            }

            return $events;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء جلب الفعاليات: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    public static function getEventById(PDO $db, int $eventId): ?array {
        try {
            $stmt = $db->prepare("SELECT * FROM events WHERE event_id = :eventId");
            $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء جلب بيانات الفعالية: " . $e->getMessage() . "</p>";
            return null;
        }
    }
}
?>
