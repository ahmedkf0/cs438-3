<?php
//......
// Observable Class
class ReferralObservable {
    private array $observers = [];

    public function attach(ReferralObserver $observer): void {
        $this->observers[] = $observer;
    }

    public function notifyObservers(array $data): void {
        foreach ($this->observers as $observer) {
            $observer->update($data);
        }
    }
}

// Observer Interface
interface ReferralObserver {
    public function update(array $data): void;
}

// Concrete Observer for Logging
class ReferralLogger implements ReferralObserver {
    public function update(array $data): void {
        // Log the referral
        error_log("Referral created: " . json_encode($data));
    }
}

// Concrete Observer for Rewarding Points
class ReferralRewarder implements ReferralObserver {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function update(array $data): void {
        $referrerId = $data['referrer_id'];
        $rewardPoints = 10; // Example reward points

        // Update reward points for the referrer
        $stmt = $this->db->prepare("UPDATE users SET reward_points = reward_points + :points WHERE user_id = :referrerId");
        $stmt->bindParam(':points', $rewardPoints, PDO::PARAM_INT);
        $stmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// Referral Controller
class ReferralController {
    private ReferralModel $referralModel;
    private ReferralObservable $observable;

    public function __construct(PDO $db) {
        $this->referralModel = new ReferralModel($db);
        $this->observable = new ReferralObservable();

        // Attach observers
        $this->observable->attach(new ReferralLogger());
        $this->observable->attach(new ReferralRewarder($db));
    }

    public function handleReferral(int $userId, string $referredEmail): void {
        if ($this->validateEmail($referredEmail)) {
            $result = $this->referralModel->registerReferral($userId, $referredEmail);

            if ($result) {
                // Notify observers
                $this->observable->notifyObservers([
                    'referrer_id' => $userId,
                    'referred_email' => $referredEmail,
                ]);

                header('Location: success.php');
                exit();
            } else {
                header('Location: error.php?error=registration_failed');
                exit();
            }
        } else {
            header('Location: error.php?error=invalid_email');
            exit();
        }
    }

    private function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// Referral Model
class ReferralModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function registerReferral(int $referrerId, string $referredEmail): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO referrals (referrer_id, referred_email, referred_status, created_at)
                VALUES (:referrerId, :referredEmail, 'pending', NOW())
            ");
            $stmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
            $stmt->bindParam(':referredEmail', $referredEmail, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error registering referral: " . $e->getMessage());
            return false;
        }
    }
}
