<?php
namespace Classes;

use PDO;

class Event {
    private int $eventId;
    private string $title;
    private string $description;
    private string $date;
    private string $time;
    private string $location;
    private float $price;
    private int $availableSeats;

    public function __construct(int $eventId, string $title, string $description, string $date, string $time, string $location, float $price, int $availableSeats) {
        $this->eventId = $eventId;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->time = $time;
        $this->location = $location;
        $this->price = $price;
        $this->availableSeats = $availableSeats;
    }

    // Getter methods for each private property
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

    public static function getAllEvents(PDO $db): array {
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
                $eventData['available_seats']
            );
        }

        return $events;
    }

    public static function getEventById(PDO $db, int $eventId): ?array {
        $stmt = $db->prepare("SELECT * FROM events WHERE event_id = :eventId");
        $stmt->bindParam(':eventId', $eventId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>