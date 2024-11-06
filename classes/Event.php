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

    public static function getAllEvents(PDO $db): array {
        $stmt = $db->query("SELECT * FROM events");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEventById(PDO $db, int $eventId): ?array {
        $stmt = $db->prepare("SELECT * FROM events WHERE event_id = :eventId");
        $stmt->bindParam(':eventId', $eventId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
