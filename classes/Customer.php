<?php
namespace Classes;

use PDO;
use DateTime;

require_once 'User.php';

class Customer extends User {
    
    private array $bookings;

    public function __construct(int $userId, string $name, string $email, string $phoneNumber, string $birthdate, string $password) {
        parent::__construct($userId, $name, $email, $phoneNumber, $birthdate, $password);
        $this->bookings = [];
    }

    public function bookEvent(PDO $db, int $eventId, int $numTickets): int {
        // Fetch event details
        $event = Event::getEventById($db, $eventId);
        if (!$event) {
            throw new \Exception("Event not found.");
        }

        // Calculate total price
        $totalPrice = $event['price'] * $numTickets;

        // Insert pending booking
        $stmt = $db->prepare("INSERT INTO bookings (user_id, event_id, num_tickets, total_price, status) VALUES (:userId, :eventId, :numTickets, :totalPrice, 'Pending')");
        $stmt->bindParam(':userId', $this->userId);
        $stmt->bindParam(':eventId', $eventId);
        $stmt->bindParam(':numTickets', $numTickets);
        $stmt->bindParam(':totalPrice', $totalPrice);
        $stmt->execute();

        // Get the last inserted booking ID
        return $db->lastInsertId();
    }

    public function cancelBooking(PDO $db, int $bookingId): string {
        // Fetch booking and event details with the booking ID
        $stmt = $db->prepare("SELECT events.date AS event_date, bookings.event_id, bookings.num_tickets, TIMESTAMPDIFF(HOUR, NOW(), events.date) AS hours_until_event 
                              FROM bookings 
                              JOIN events ON bookings.event_id = events.event_id 
                              WHERE bookings.booking_id = :bookingId AND bookings.user_id = :userId");
        $stmt->bindParam(':bookingId', $bookingId);
        $stmt->bindParam(':userId', $this->userId);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return "<p class='error'>لم يتم العثور على الحجز المطلوب.</p>";
        }

        if ($booking['hours_until_event'] > 24) {
            // Delete booking
            $deleteBooking = $db->prepare("DELETE FROM bookings WHERE booking_id = :bookingId");
            $deleteBooking->bindParam(':bookingId', $bookingId);
            $deleteBooking->execute();

            // Update available seats
            $updateSeats = $db->prepare("UPDATE events SET available_seats = available_seats + :numTickets WHERE event_id = :eventId");
            $updateSeats->bindParam(':numTickets', $booking['num_tickets']);
            $updateSeats->bindParam(':eventId', $booking['event_id']);
            $updateSeats->execute();

            return "<p class='success'>تم حذف الحجز بنجاح.</p>";
        } else {
            return "<p class='error'>لا يمكن إلغاء الحجز لأنه متبقي أقل من 24 ساعة على موعد الفعالية.</p>";
        }
    }

    public function viewBookings(PDO $db): array {
        // Fetch all bookings for the user
        $stmt = $db->prepare("SELECT bookings.booking_id, events.title, bookings.num_tickets, bookings.total_price, bookings.booking_date, events.date AS event_date, bookings.status, 
                              TIMESTAMPDIFF(HOUR, NOW(), events.date) AS hours_until_event
                              FROM bookings 
                              JOIN events ON bookings.event_id = events.event_id 
                              WHERE bookings.user_id = :userId 
                              ORDER BY bookings.booking_date DESC");
        $stmt->bindParam(':userId', $this->userId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
