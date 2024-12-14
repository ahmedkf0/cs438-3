<?php
// مصنع الإحالة (Referral Factory)
class ReferralFactory {
    // إنشاء مراقب (Observer) بناءً على النوع
    public static function createObserver(string $type, PDO $db = null): ReferralObserver {
        return match ($type) {
            'logger' => new ReferralLogger(), // مراقب لتسجيل الأحداث
            'rewarder' => new ReferralRewarder($db), // مراقب لمنح النقاط
            default => throw new Exception("نوع المراقب غير معروف: $type"),
        };
    }

    // إنشاء كائن للتحكم في الإحالات
    public static function createReferralController(PDO $db): ReferralController {
        return new ReferralController($db);
    }

    // إنشاء كائن للنموذج الخاص بالإحالات
    public static function createReferralModel(PDO $db): ReferralModel {
        return new ReferralModel($db);
    }
}

// الكائن المراقب (Observable Class)
class ReferralObservable {
    private array $observers = []; // قائمة بالمراقبين

    // إرفاق مراقب جديد
    public function attach(ReferralObserver $observer): void {
        $this->observers[] = $observer;
    }

    // إشعار جميع المراقبين عند حدوث حدث
    public function notifyObservers(array $data): void {
        foreach ($this->observers as $observer) {
            $observer->update($data);
        }
    }
}

// واجهة المراقب (Observer Interface)
interface ReferralObserver {
    public function update(array $data): void; // وظيفة يتم استدعاؤها عند الإشعار
}

// مراقب لتسجيل الأحداث
class ReferralLogger implements ReferralObserver {
    public function update(array $data): void {
        // تسجيل الإحالة في ملف السجل
        error_log("تم إنشاء إحالة جديدة: " . json_encode($data));
    }
}

// مراقب لمنح النقاط
class ReferralRewarder implements ReferralObserver {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function update(array $data): void {
        $referrerId = $data['referrer_id']; // رقم المحيل
        $rewardPoints = 10; // النقاط المكتسبة لكل إحالة

        // تحديث النقاط للمحيل في قاعدة البيانات
        $stmt = $this->db->prepare("UPDATE users SET reward_points = reward_points + :points WHERE user_id = :referrerId");
        $stmt->bindParam(':points', $rewardPoints, PDO::PARAM_INT);
        $stmt->bindParam(':referrerId', $referrerId, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// المتحكم في الإحالات (Referral Controller)
class ReferralController {
    private ReferralModel $referralModel; // النموذج الخاص بالإحالات
    private ReferralObservable $observable; // الكائن المراقب

    public function __construct(PDO $db) {
        $this->referralModel = ReferralFactory::createReferralModel($db); // إنشاء النموذج باستخدام المصنع
        $this->observable = new ReferralObservable();

        // إرفاق المراقبين باستخدام المصنع
        $this->observable->attach(ReferralFactory::createObserver('logger'));
        $this->observable->attach(ReferralFactory::createObserver('rewarder', $db));
    }

    // معالجة طلب الإحالة
    public function handleReferral(int $userId, string $referredEmail): void {
        if ($this->validateEmail($referredEmail)) {
            // تسجيل الإحالة
            $result = $this->referralModel->registerReferral($userId, $referredEmail);

            if ($result) {
                // إشعار المراقبين
                $this->observable->notifyObservers([
                    'referrer_id' => $userId,
                    'referred_email' => $referredEmail,
                ]);

                // التوجيه إلى صفحة النجاح
                header('Location: success.php');
                exit();
            } else {
                // التوجيه إلى صفحة الخطأ
                header('Location: error.php?error=registration_failed');
                exit();
            }
        } else {
            // التوجيه إلى صفحة الخطأ بسبب بريد إلكتروني غير صالح
            header('Location: error.php?error=invalid_email');
            exit();
        }
    }

    // التحقق من صحة البريد الإلكتروني
    private function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

// النموذج الخاص بالإحالات (Referral Model)
class ReferralModel {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // تسجيل الإحالة في قاعدة البيانات
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
            // تسجيل الخطأ في السجل
            error_log("خطأ أثناء تسجيل الإحالة: " . $e->getMessage());
            return false;
        }
    }
}

// مثال على استخدام النظام
//try {
    // افترض أن $db هو كائن اتصال PDO
 //   $db = new PDO('mysql:host=localhost;dbname=test', 'username', 'password');
   // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // إنشاء كائن التحكم في الإحالات باستخدام المصنع
   // $referralController = ReferralFactory::createReferralController($db);

    // معالجة إحالة (بيانات مثال)
   // $referralController->handleReferral(1, 'referred@example.com');

//} catch (Exception $e) {
    // تسجيل الخطأ في حالة وجود مشكلة
//    error_log("خطأ: " . $e->getMessage());
//}
