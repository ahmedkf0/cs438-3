<?php
namespace Classes;

use PDO;
use Exception;

class Referral {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // 1. تسجيل إحالة جديدة
    public function createReferral(int $referrerId, string $referredEmail): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO referrals (referrer_id, referred_email, referred_status) 
                                        VALUES (:referrer_id, :referred_email, 'pending')");
            $stmt->bindParam(':referrer_id', $referrerId);
            $stmt->bindParam(':referred_email', $referredEmail);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إنشاء الإحالة: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // 2. إكمال الإحالة وإضافة مكافآت
    public function completeReferral(string $referredEmail, int $rewardPoints = 10): bool {
        try {
            // تحديث حالة الإحالة إلى "completed"
            $updateReferral = $this->db->prepare("UPDATE referrals SET referred_status = 'completed' 
                                                  WHERE referred_email = :referred_email AND referred_status = 'pending'");
            $updateReferral->bindParam(':referred_email', $referredEmail);
            $updateReferral->execute();

            // إضافة نقاط المكافآت للمُحيل
            $rewardUpdate = $this->db->prepare("UPDATE users 
                                                SET reward_points = reward_points + :reward_points 
                                                WHERE user_id = (SELECT referrer_id FROM referrals WHERE referred_email = :referred_email)");
            $rewardUpdate->bindParam(':reward_points', $rewardPoints);
            $rewardUpdate->bindParam(':referred_email', $referredEmail);
            $rewardUpdate->execute();

            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إكمال الإحالة: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    // 3. استرجاع الإحالات الخاصة بمستخدم معين
    public function getReferrals(int $referrerId): array {
        try {
            $stmt = $this->db->prepare("SELECT referred_email, referred_status, created_at 
                                        FROM referrals WHERE referrer_id = :referrer_id");
            $stmt->bindParam(':referrer_id', $referrerId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء استرجاع الإحالات: " . $e->getMessage() . "</p>";
            return [];
        }
    }

    // 4. استرجاع رابط الإحالة
    public function getReferralLink(int $userId): ?string {
        try {
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $referralCode = $stmt->fetch(PDO::FETCH_ASSOC)['referral_code'];

            if ($referralCode) {
                return "https://example.com/register.php?ref=" . $referralCode;
            }
            return null;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء استرجاع رابط الإحالة: " . $e->getMessage() . "</p>";
            return null;
        }
    }

    // 5. إنشاء كود إحالة جديد (إذا لم يكن موجودًا)
    public function generateReferralCode(int $userId): bool {
        try {
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $existingCode = $stmt->fetch(PDO::FETCH_ASSOC)['referral_code'];

            if (!$existingCode) {
                $newCode = substr(md5(uniqid($userId, true)), 0, 8);
                $updateStmt = $this->db->prepare("UPDATE users SET referral_code = :referral_code WHERE user_id = :user_id");
                $updateStmt->bindParam(':referral_code', $newCode);
                $updateStmt->bindParam(':user_id', $userId);
                return $updateStmt->execute();
            }
            return true; // الكود موجود بالفعل
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إنشاء كود الإحالة: " . $e->getMessage() . "</p>";
            return false;
        }
    }
}
