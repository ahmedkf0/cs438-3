<?php
namespace Classes;

use Exception;

class Discount {
    private int $age;
    private string $occupation;

    public function __construct(int $age, string $occupation) {
        $this->age = $age;
        $this->occupation = $occupation;
    }

    public function getDiscountPercentage(): float {
        if ($this->occupation === 'student') {
            return 10;
        }
        if ($this->occupation === 'teacher' || $this->occupation === 'military') {
            return 10;
        }
        if ($this->age >= 65) {
            return 5;
        }
        return 0;
    }

    public function applyDiscount(float $price): float {
        try {
            $discountPercentage = $this->getDiscountPercentage();
            return $price * ((100 - $discountPercentage) / 100);
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء تطبيق الخصم: " . $e->getMessage() . "</p>";
            return $price; // Return original price if there's an error
        }
    }
}
