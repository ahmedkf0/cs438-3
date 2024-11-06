<?php
namespace Classes;

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
        // يمكنك هنا إضافة منطق خاص بعملية الدفع الفعلية مثل API الدفع الخارجي
        // سنقوم هنا بمحاكاة الدفع فقط بإرجاع true للدلالة على النجاح
        $this->status = 'completed';
        return true;
    }
}
