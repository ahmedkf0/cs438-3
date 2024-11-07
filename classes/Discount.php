<?php
namespace Classes;

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
        $discountPercentage = $this->getDiscountPercentage();
        return $price * ((100 - $discountPercentage) / 100);
    }
}
