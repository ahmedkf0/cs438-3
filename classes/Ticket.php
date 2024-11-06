<?php
namespace Classes;

class Ticket {
    private int $ticketId;
    private int $eventId;
    private int $userId;
    private string $seatNumber;
    private float $price;
    private bool $isDiscounted;
    private string $status;

    public function __construct(int $ticketId, int $eventId, int $userId, string $seatNumber, float $price, bool $isDiscounted, string $status) {
        $this->ticketId = $ticketId;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->seatNumber = $seatNumber;
        $this->price = $price;
        $this->isDiscounted = $isDiscounted;
        $this->status = $status;
    }

    public function confirmBooking() {}
    public function cancelBooking() {}
}
