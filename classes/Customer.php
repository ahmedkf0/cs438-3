<?php
namespace Classes;

require_once 'User.php';
require_once 'Ticket.php';

class Customer extends User {
    private array $bookings;

    public function __construct(int $userId, string $name, string $email, string $phoneNumber, string $birthdate, string $password) {
        parent::__construct($userId, name, email, phoneNumber, birthdate, password);
        $this->bookings = [];
    }

    public function bookTicket(Event $event, int $numberOfTickets) {}
    public function cancelBooking(int $bookingId) {}
    public function viewBookings() {}
}
