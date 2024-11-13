<?php
namespace Classes;

use Exception;

class Payment {
    private int $bookingId;
    private string $paymentMethod;
    private float $amount;
    private string $status;

    public function __construct(int $bookingId, string $paymentMethod, float $amount, string $status = 'pending') {
        $this->bookingId = $bookingId;
        $this->paymentMethod = $paymentMethod;
        $this->amount = $amount;
        $this->status = $status;
    }

    // دالة محاكاة لمعالجة الدفع
    public function processPayment(): bool {
        try {
            // هنا يمكنك إضافة منطق خاص بعملية الدفع الفعلية مثل الاتصال بـ API دفع خارجي
            // في هذه المحاكاة، سنقوم فقط بتغيير الحالة إلى "completed"
            $this->status = 'completed';
            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء معالجة الدفع: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // Getters for accessing payment details
    public function getBookingId(): int {
        return $this->bookingId;
    }

    public function getPaymentMethod(): string {
        return $this->paymentMethod;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getStatus(): string {
        return $this->status;
    }
}
