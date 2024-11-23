<?php
session_start();
require_once '../config/db.php';
require_once '../classes/Event.php';
require_once '../classes/Review.php';

use Config\Database;
use Classes\Event;
use Classes\Review;

$db = (new Database())->connect();

if (!$db) {
    die("Database connection error. Please check your configuration.");
}

// جلب الفعاليات
$events = Event::getAllEvents($db);

// إنشاء كائن المراجعات
$review = new Review($db);

// إضافة التقييم عند تقديم النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $eventId = (int)$_POST['event_id'];
    $rating = (int)$_POST['rating'];
    $reviewText = $_POST['review_text'];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        $_SESSION['message'] = "<p class='error'>يرجى تسجيل الدخول لتقديم تقييم.</p>";
    } else {
        // تحقق إذا كان المستخدم قد قام بالحجز لهذا الحدث
        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = :userId AND event_id = :eventId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        $hasBooking = $stmt->fetchColumn();

        if (!$hasBooking) {
            $_SESSION['message'] = "<p class='error'>لا يمكنك تقييم حدث لم تقم بحجزه.</p>";
        } else {
            // تحقق إذا كان المستخدم قد قام بالفعل بتقييم هذا الحدث
            $stmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = :userId AND event_id = :eventId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $hasReviewed = $stmt->fetchColumn();

            if ($hasReviewed) {
                $_SESSION['message'] = "<p class='error'>لقد قمت بالفعل بتقييم هذا الحدث.</p>";
            } else {
                // أضف التقييم
                $stmt = $db->prepare("INSERT INTO reviews (user_id, event_id, rating, review_text, created_at) VALUES (:userId, :eventId, :rating, :reviewText, NOW())");
                $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                $stmt->bindParam(':eventId', $eventId, PDO::PARAM_INT);
                $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
                $stmt->bindParam(':reviewText', $reviewText, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "<p class='success'>تمت إضافة التقييم بنجاح.</p>";
                } else {
                    $_SESSION['message'] = "<p class='error'>حدث خطأ أثناء إضافة التقييم. حاول مرة أخرى.</p>";
                }
            }
        }
    }

    // إعادة التوجيه لتجنب إعادة إرسال النموذج
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $reviewId = (int)$_POST['review_id'];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        $_SESSION['message'] = "<p class='error'>يرجى تسجيل الدخول لحذف التقييم.</p>";
    } else {
        // التحقق من أن المستخدم هو صاحب التقييم
        $stmt = $db->prepare("SELECT user_id FROM reviews WHERE review_id = :reviewId");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->execute();
        $reviewOwner = $stmt->fetchColumn();

        if ($reviewOwner == $userId) {
            // حذف التقييم
            $stmt = $db->prepare("DELETE FROM reviews WHERE review_id = :reviewId");
            $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['message'] = "<p class='success'>تم حذف التقييم بنجاح.</p>";
            } else {
                $_SESSION['message'] = "<p class='error'>حدث خطأ أثناء حذف التقييم. حاول مرة أخرى.</p>";
            }
        } else {
            $_SESSION['message'] = "<p class='error'>لا يمكنك حذف تقييم ليس من إضافتك.</p>";
        }
    }

    // إعادة التوجيه لتجنب إعادة إرسال الطلب
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_SESSION['message'])): ?>
    <div class="message-container">
        <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']); // مسح الرسالة بعد عرضها
        ?>
    </div>
<?php endif; ?>


<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الفعاليات</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- شريط التنقل -->
    <div class="navbar">
        <a href="index.php">الفعاليات</a>
        <a href="my_bookings.php">حجوزاتي</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php" class="logout-btn">تسجيل الخروج</a>
        <?php else: ?>
            <a href="login.php" class="login-btn">تسجيل الدخول</a>
        <?php endif; ?>
    </div>

    <h1>الفعاليات المتاحة</h1>

    <div class="events-container">
        <?php if (!empty($events)): ?>
            <?php foreach ($events as $event): ?>
                <?php 
                    $eventIdParam = $event->getEventId(); // تعريف متغير للـ event_id
                    // جلب متوسط التقييم للفعالية
                    $averageRating = $review->getAverageRating($eventIdParam);
                    // جلب جميع المراجعات للفعالية
                    $reviews = $review->getReviewsByEvent($eventIdParam);
                ?>
                <div class="event-card">
                    <h2><?php echo htmlspecialchars($event->getTitle()); ?></h2>
                    <p><?php echo htmlspecialchars($event->getDescription()); ?></p>
                    <p><strong>الموقع:</strong> <?php echo htmlspecialchars($event->getLocation()); ?></p>
                    <p><strong>السعر:</strong> <?php echo htmlspecialchars($event->getPrice()); ?> دينار ليبي</p>
                    <p><strong>المقاعد المتاحة:</strong> <?php echo htmlspecialchars($event->getAvailableSeats()); ?></p>
                    <p><strong>العمر الأدنى:</strong> <?php echo htmlspecialchars($event->getAgeRestriction()); ?> سنوات</p>
                    <p><strong>متوسط التقييم:</strong> <?php echo $averageRating > 0 ? number_format($averageRating, 1) . ' / 5' : 'لم يتم التقييم بعد'; ?></p>
                    
                    <!-- الأزرار -->
                    <div class="btn-group">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a class="btn" href="booking.php?event_id=<?php echo $eventIdParam; ?>">حجز عادي</a>
                            <a class="btn gift-btn" href="gift_booking.php?event_id=<?php echo $eventIdParam; ?>">إهداء حجز</a>
                        <?php else: ?>
                            <a class="btn" href="login.php">حجز عادي</a>
                            <a class="btn gift-btn" href="login.php">إهداء حجز</a>
                        <?php endif; ?>
                    </div>
                    
                    
                    <div class="reviews-container">
                        <h3>المراجعات:</h3>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $r): ?>
                                <div class="review">
                                    <p><strong>اسم المستخدم:</strong> <?php echo htmlspecialchars($r['user_name']); ?></p>
                                    <p><strong>التقييم:</strong> <?php echo htmlspecialchars($r['rating']); ?>/5</p>
                                    <p><?php echo htmlspecialchars($r['review_text']); ?></p>
                                    <small>تمت الإضافة في: <?php echo htmlspecialchars($r['created_at']); ?></small>

                                    <!-- زر الحذف يظهر فقط للمستخدم الذي أضاف التقييم -->
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['user_id']): ?>
                                        <form method="POST" action="" class="delete-review-form">
                                            <input type="hidden" name="review_id" value="<?php echo $r['review_id']; ?>">
                                            <button type="submit" name="delete_review" class="delete-btn">حذف تقيمي</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>لا توجد مراجعات بعد.</p>
                        <?php endif; ?>
                    </div>


                    <!-- نموذج إضافة تقييم -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php
                        $userId = $_SESSION['user_id'];

                        // تحقق من وجود حجز
                        $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = :userId AND event_id = :eventId");
                        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                        $stmt->bindParam(':eventId', $eventIdParam, PDO::PARAM_INT);
                        $stmt->execute();
                        $hasBooking = $stmt->fetchColumn();

                        // تحقق من وجود مراجعة مسبقة
                        $stmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = :userId AND event_id = :eventId");
                        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                        $stmt->bindParam(':eventId', $eventIdParam, PDO::PARAM_INT);
                        $stmt->execute();
                        $hasReviewed = $stmt->fetchColumn();
                        ?>

                        <?php if ($hasBooking && !$hasReviewed): ?>
                            <div class="add-review">
                                <h3>إضافة تقييمك:</h3>
                                <form method="POST" action="">
                                    <input type="hidden" name="event_id" value="<?php echo $eventIdParam; ?>">
                                    <label for="rating-<?php echo $eventIdParam; ?>">التقييم (1-5):</label>
                                    <select name="rating" id="rating-<?php echo $eventIdParam; ?>" required>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                    </select>
                                    <label for="review_text-<?php echo $eventIdParam; ?>">التعليق:</label>
                                    <textarea name="review_text" id="review_text-<?php echo $eventIdParam; ?>" rows="3" required></textarea>
                                    <button type="submit" name="add_review">إضافة مراجعة</button>
                                </form>
                            </div>
                        <?php elseif ($hasReviewed): ?>
                            <p>لقد قمت بتقييم هذا الحدث مسبقًا.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>يرجى <a href="login.php">تسجيل الدخول</a> لإضافة مراجعة.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>لا توجد فعاليات متاحة حاليًا.</p>
        <?php endif; ?>
    </div>

</body>
</html>
