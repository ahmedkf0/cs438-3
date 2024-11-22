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
        echo "<p class='error'>يرجى تسجيل الدخول لتقديم تقييم.</p>";
    } elseif ($review->addReview($eventId, $userId, $rating, $reviewText)) {
        echo "<p class='success'>تمت إضافة المراجعة بنجاح.</p>";
    } else {
        echo "<p class='error'>حدث خطأ أثناء إضافة المراجعة. حاول مرة أخرى.</p>";
    }
}

// حذف التقييم عند تقديم طلب الحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $reviewId = (int)$_POST['review_id'];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo "<p class='error'>يرجى تسجيل الدخول لحذف التقييم.</p>";
    } elseif ($review->deleteReview($reviewId, $userId)) {
        echo "<p class='success'>تم حذف التقييم بنجاح.</p>";
    } else {
        echo "<p class='error'>حدث خطأ أثناء حذف التقييم. حاول مرة أخرى.</p>";
    }
}
?>

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
                    // جلب متوسط التقييم للفعالية
                    $averageRating = $review->getAverageRating($event->getEventId());
                    // جلب جميع المراجعات للفعالية
                    $reviews = $review->getReviewsByEvent($event->getEventId());
                ?>
                <div class="event-card">
                    <h2><?php echo htmlspecialchars($event->getTitle()); ?></h2>
                    <p><?php echo htmlspecialchars($event->getDescription()); ?></p>
                    <p><strong>الموقع:</strong> <?php echo htmlspecialchars($event->getLocation()); ?></p>
                    <p><strong>السعر:</strong> <?php echo htmlspecialchars($event->getPrice()); ?> دينار ليبي</p>
                    <p><strong>المقاعد المتاحة:</strong> <?php echo htmlspecialchars($event->getAvailableSeats()); ?></p>
                    <p><strong>العمر الأدنى:</strong> <?php echo htmlspecialchars($event->getAgeRestriction()); ?> سنوات</p>
                    <p><strong>تاريخ الحدث:</strong> <?php echo htmlspecialchars($event->getDate()); ?></p>
                    <p><strong>توقيت الحدث:</strong> <?php echo htmlspecialchars($event->getTime()); ?></p>
                    <p><strong>متوسط التقييم:</strong> <?php echo $averageRating > 0 ? number_format($averageRating, 1) . ' / 5' : 'لم يتم التقييم بعد'; ?></p>
                    
                    <!-- الأزرار -->
                    <div class="btn-group">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn" href="booking.php?event_id=<?php echo $event->getEventId(); ?>">حجز عادي</a>
        <a class="btn gift-btn" href="gift_booking.php?event_id=<?php echo $event->getEventId(); ?>">إهداء حجز</a>
    <?php else: ?>
        <a class="btn" href="login.php">حجز عادي</a>
        <a class="btn gift-btn" href="login.php">إهداء حجز</a>
    <?php endif; ?>
</div>


                    <!-- عرض المراجعات -->
                    <div class="reviews-container">
                        <h3>المراجعات:</h3>
                        <?php if (!empty($reviews)): ?>
                            <?php foreach ($reviews as $r): ?>
                                <div class="review">
                                    <p><strong><?php echo htmlspecialchars($r['user_name']); ?>:</strong> <?php echo htmlspecialchars($r['rating']); ?>/5</p>
                                    <p><?php echo htmlspecialchars($r['review_text']); ?></p>
                                    <small>تمت الإضافة في: <?php echo htmlspecialchars($r['created_at']); ?></small>

                                    <!-- زر الحذف -->
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $r['user_id']): ?>
                                        <form method="POST" action="" class="delete-review-form">
                                            <input type="hidden" name="review_id" value="<?php echo $r['review_id']; ?>">
                                            <button type="submit" name="delete_review" class="delete-btn">حذف</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>لا توجد مراجعات حتى الآن.</p>
                        <?php endif; ?>
                    </div>

                    <!-- نموذج إضافة تقييم -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="add-review">
                            <h3>إضافة تقييمك:</h3>
                            <form method="POST" action="">
                                <input type="hidden" name="event_id" value="<?php echo $event->getEventId(); ?>">
                                <label for="rating-<?php echo $event->getEventId(); ?>">التقييم (1-5):</label>
                                <select name="rating" id="rating-<?php echo $event->getEventId(); ?>" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                                <label for="review_text-<?php echo $event->getEventId(); ?>">التعليق:</label>
                                <textarea name="review_text" id="review_text-<?php echo $event->getEventId(); ?>" rows="3" required></textarea>
                                <button type="submit" name="add_review">إضافة مراجعة</button>
                            </form>
                        </div>
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
