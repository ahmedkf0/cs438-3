<?php
namespace Classes;

class GiftTicket {
    private int $giftTicketId;
    private int $giverId;
    private int $recipientId;
    private string $message;

    public function __construct(int $giftTicketId, int $giverId, int $recipientId, string $message) {
        $this->giftTicketId = $giftTicketId;
        $this->giverId = $giverId;
        $this->recipientId = $recipientId;
        $this->message = $message;
    }

    public function sendGiftTicket() {
        // Logic to send gift ticket
    }
}
