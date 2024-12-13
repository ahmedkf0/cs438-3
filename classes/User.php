<?php
namespace Classes;

use PDO;
use Exception;

class User {
    private int $userId;
    private string $name;
    private string $email;
    private string $password;
    private string $role;
    private ?string $birthdate;

    public function __construct(int $userId, string $name, string $email, string $password, string $role, ?string $birthdate = null) {
        $this->userId = $userId;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->birthdate = $birthdate ?? '';
    }

    // دوال Getter للوصول إلى الخصائص الخاصة
    public function getUserId(): int {
        return $this->userId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getRole() {
        return $this->role;
    }

    // دالة تسجيل الدخول
    public static function login(PDO $db, string $email, string $password): ?User {
        try {
            $query = "SELECT user_id, name, email, password, role, birthdate FROM users WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
        
            if ($stmt->rowCount() > 0) {
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $userData['password'])) {
                    return new User(
                        $userData['user_id'],
                        $userData['name'],
                        $userData['email'],
                        $userData['password'],
                        $userData['role'],
                        $userData['birthdate']
                    );
                }
            }
            return null;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء محاولة تسجيل الدخول: " . $e->getMessage() . "</p>";
            return null;
        }
    }

    // دالة إنشاء حساب جديد
    public static function register(PDO $db, $name, $email, $password, $role, $phone_number, $birthdate, $occupation = null, $referralCode = null) {
        try {
            // توليد كود إحالة فريد
            $generatedReferralCode = substr(md5(uniqid($email, true)), 0, 8);
    
            // التحقق من كود الإحالة إذا كان موجودًا
            $referrerId = null;
            if ($referralCode) {
                $referrerQuery = $db->prepare("SELECT user_id FROM users WHERE referral_code = :referralCode");
                $referrerQuery->bindParam(':referralCode', $referralCode);
                $referrerQuery->execute();
                if ($referrerQuery->rowCount() > 0) {
                    $referrerId = $referrerQuery->fetch(PDO::FETCH_ASSOC)['user_id'];
                }
            }
    
            // إنشاء الحساب الجديد
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, phone_number, birthdate, occupation, referral_code) 
                                  VALUES (:name, :email, :password, :role, :phone_number, :birthdate, :occupation, :referral_code)");
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':birthdate', $birthdate);
            $stmt->bindParam(':occupation', $occupation);
            $stmt->bindParam(':referral_code', $generatedReferralCode);
            $stmt->execute();
    
            // إذا كان هناك مُحيل، أضف الإحالة إلى جدول الإحالات
            // التحقق من كود الإحالة أثناء التسجيل
            if (!empty($_POST['referral_code'])) {
                $referralCode = $_POST['referral_code'];
            
                // جلب صاحب كود الإحالة
                $stmt = $db->prepare("SELECT user_id FROM users WHERE referral_code = :referralCode");
                $stmt->bindParam(':referralCode', $referralCode, PDO::PARAM_STR);
                $stmt->execute();
                $referrerId = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'] ?? null;
            
                if ($referrerId) {
                    // زيادة النقاط للمحيل
                    $updatePointsStmt = $db->prepare("UPDATE users SET reward_points = reward_points + 10 WHERE user_id = :referrerId");
                    $updatePointsStmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
                    $updatePointsStmt->execute();
            
                    // تسجيل الإحالة في جدول الإحالات
                    $insertReferralStmt = $db->prepare("INSERT INTO referrals (referrer_id, referred_email, referred_status) 
                                                        VALUES (:referrerId, :email, 'approved')");
                    $insertReferralStmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
                    $insertReferralStmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $insertReferralStmt->execute();
                }
            }
            

    
            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إنشاء الحساب: " . $e->getMessage() . "</p>";
            return false;
        }
    }
    

    public static function completeReferral(PDO $db, $userId) {
        try {
            // استرجاع البريد الإلكتروني للمستخدم
            $userQuery = $db->prepare("SELECT email FROM users WHERE user_id = :user_id");
            $userQuery->bindParam(':user_id', $userId);
            $userQuery->execute();
            $userEmail = $userQuery->fetch(PDO::FETCH_ASSOC)['email'];
    
            // تحديث حالة الإحالة إلى "completed"
            $referralUpdate = $db->prepare("UPDATE referrals SET referred_status = 'completed' 
                                            WHERE referred_email = :referred_email");
            $referralUpdate->bindParam(':referred_email', $userEmail);
            $referralUpdate->execute();
    
            // إضافة نقاط مكافأة للمُحيل
            $rewardPoints = 10; // نقاط المكافآت
            $rewardUpdate = $db->prepare("UPDATE users SET reward_points = reward_points + :reward_points 
                                          WHERE user_id = (SELECT referrer_id FROM referrals WHERE referred_email = :referred_email)");
            $rewardUpdate->bindParam(':reward_points', $rewardPoints);
            $rewardUpdate->bindParam(':referred_email', $userEmail);
            $rewardUpdate->execute();
    
            return true;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء إكمال الإحالة: " . $e->getMessage() . "</p>";
            return false;
        }
    }

    public static function getReferralLink(PDO $db, $userId): ?string {
        try {
            $stmt = $db->prepare("SELECT referral_code FROM users WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $referralCode = $stmt->fetch(PDO::FETCH_ASSOC)['referral_code'];
            return "https://example.com/register.php?ref=" . $referralCode;
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء استرجاع رابط الإحالة: " . $e->getMessage() . "</p>";
            return null;
        }
    }
    
    public static function getReferrals(PDO $db, $userId): array {
        try {
            $stmt = $db->prepare("SELECT referred_email, referred_status, created_at 
                                  FROM referrals WHERE referrer_id = :referrer_id");
            $stmt->bindParam(':referrer_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "<p class='error'>حدث خطأ أثناء استرجاع الإحالات: " . $e->getMessage() . "</p>";
            return [];
        }
    }
    
    
}
