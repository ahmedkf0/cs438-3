<?php
namespace Classes;

class Discount {
    private string $discountType;
    private float $discountPercentage;
    private bool $isActive;

    public function __construct(string $discountType, float $discountPercentage, bool $isActive) {
        $this->discountType = $discountType;
        $this->discountPercentage = $discountPercentage;
        $this->isActive = $isActive;
    }

    public function applyDiscount(float $price): float {
        if ($this->isActive) {
            return $price * ((100 - $this->discountPercentage) / 100);
        }
        return $price;
    }
}
