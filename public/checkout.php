<?php

session_start();
require_once '../config/db.php';
require_once '../classes/Booking.php';
require_once '../classes/Event.php';
require_once '../classes/Discount.php';
require_once '../classes/Payment.php';

use Config\Database;
use Classes\Booking;
use Classes\Event;
use Classes\Discount;
use Classes\Payment;

// التحقق من تسجيل دخول المستخدم
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// الاتصال بقاعدة البيانات
$db = (new Database())->connect();

// التحقق من معرف الحجز
if (!isset($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    echo "<p class='error'>Booking ID is missing or invalid.</p>";
    exit();
}

$bookingId = (int) $_GET['booking_id'];

// جلب تفاصيل الحجز المؤقت
$stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = :bookingId AND status = 'Pending'");
$stmt->bindParam(':bookingId', $bookingId);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "<p class='error'>Pending booking not found.</p>";
    exit();
}

// جلب بيانات الفعالية
$event = Event::getEventById($db, $booking['event_id']);
if (!$event) {
    echo "<p class='error'>Event not found.</p>";
    exit();
}

$originalPrice = (float) $booking['total_price'];
$numTickets = $booking['num_tickets'];

// تحديد المستخدم المعني
$userId = $booking['recipient_id'] ?: $booking['user_id']; // استخدم recipient_id إذا كان متوفرًا، وإلا استخدم user_id

// جلب بيانات المستخدم
$userStmt = $db->prepare("SELECT birthdate, occupation, reward_points FROM users WHERE user_id = :userId");
$userStmt->bindParam(':userId', $userId);
$userStmt->execute();
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p class='error'>User not found.</p>";
    exit();
}

// حساب العمر والخصم بناءً على العمر والوظيفة
$birthdate = new DateTime($user['birthdate']);
$today = new DateTime();
$age = $today->diff($birthdate)->y;
$occupation = $user['occupation'];

// النقاط المكتسبة
$rewardPoints = $user['reward_points'] ?? 0;

// حساب الخصم بناءً على النقاط
$discountPercentageFromPoints = floor($rewardPoints / 50) * 5; // 5% لكل 50 نقطة
$discountPercentageFromPoints = min($discountPercentageFromPoints, 50); // الحد الأقصى 50%

// حساب الخصم الإجمالي
$discount = new Discount($age, $occupation);
$discountedPrice = $discount->applyDiscount($originalPrice);

// إضافة الخصم من النقاط
$additionalDiscount = ($originalPrice * $discountPercentageFromPoints) / 100;
$finalPrice = $discountedPrice - $additionalDiscount;

// نقاط تم استخدامها
$pointsUsed = ($discountPercentageFromPoints / 5) * 50;

// معالجة الدفع
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'];
    $errorMessage = "";

    // التحقق من طريقة الدفع
    switch ($paymentMethod) {
        case 'visa':
            $cardNumber = $_POST['card_number'] ?? null;
            $cardExpiry = $_POST['card_expiry'] ?? null;
            $cardCode = $_POST['card_code'] ?? null;
            $cardholderName = $_POST['cardholder_name'] ?? null;

            if (empty($cardNumber) || empty($cardExpiry) || empty($cardCode) || empty($cardholderName)) {
                $errorMessage = "Please enter all Visa card details.";
            }
            break;

        case 'paypal':
            $paypalEmail = $_POST['paypal_email'] ?? null;
            $paypalCode = $_POST['paypal_code'] ?? null;

            if (empty($paypalEmail) || empty($paypalCode)) {
                $errorMessage = "Please enter both PayPal account email and confirmation code.";
            }
            break;

        case 'mobocash':
            $moboAccountNumber = $_POST['mobo_account_number'] ?? null;
            $moboConfirmationCode = $_POST['mobo_confirmation_code'] ?? null;

            if (empty($moboAccountNumber) || empty($moboConfirmationCode)) {
                $errorMessage = "Please enter both Mobo Cash account number and confirmation code.";
            }
            break;

        case 'edfa3li':
            $accountNumber = $_POST['account_number'] ?? null;
            $accountCode = $_POST['account_code'] ?? null;

            if (empty($accountNumber) || empty($accountCode)) {
                $errorMessage = "Please enter both the account number and account code.";
            }
            break;

        default:
            $errorMessage = "Invalid payment method selected.";
    }

    if (empty($errorMessage)) {
        $payment = new Payment($bookingId, $paymentMethod, $finalPrice, 'completed');
        $paymentSuccess = $payment->processPayment();

        if ($paymentSuccess) {
            if (Booking::confirmBooking($db, $bookingId)) {
                // تحديث المقاعد المتاحة
                $updateSeatsStmt = $db->prepare("
                    UPDATE events SET available_seats = available_seats - :numTickets 
                    WHERE event_id = :eventId AND available_seats >= :numTickets
                ");
                $updateSeatsStmt->bindParam(':numTickets', $numTickets);
                $updateSeatsStmt->bindParam(':eventId', $booking['event_id']);
                $updateSeatsStmt->execute();

                // خصم النقاط المستخدمة
                if ($pointsUsed > 0) {
                    $updatePointsStmt = $db->prepare("UPDATE users SET reward_points = reward_points - :pointsUsed WHERE user_id = :userId");
                    $updatePointsStmt->bindParam(':pointsUsed', $pointsUsed, PDO::PARAM_INT);
                    $updatePointsStmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    $updatePointsStmt->execute();
                }

                header("Location: index.php");
                exit();
            } else {
                echo "<p class='error'>Failed to confirm booking.</p>";
            }
        } else {
            echo "<p class='error'>Payment failed. Please try again.</p>";
        }
    } else {
        echo "<p class='error'>$errorMessage</p>";
    }
}
?>




<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إتمام الدفع</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function togglePaymentFields() {
            const paymentMethod = document.getElementById('payment_method').value;
            document.getElementById('visa_fields').style.display = (paymentMethod === 'visa') ? 'block' : 'none';
            document.getElementById('paypal_fields').style.display = (paymentMethod === 'paypal') ? 'block' : 'none';
            document.getElementById('mobo_fields').style.display = (paymentMethod === 'mobocash') ? 'block' : 'none';
            document.getElementById('account_fields').style.display = (paymentMethod === 'edfa3li') ? 'block' : 'none';
        }
    </script>
</head>
<body > 

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

    <div class="payment-container">
    <h1>إتمام عملية الدفع للفعالية: <?php echo htmlspecialchars($event['title']); ?></h1>
    <p>عدد التذاكر: <?php echo htmlspecialchars($numTickets); ?></p>
    <p>السعر الأصلي للتذكرة: <?php echo htmlspecialchars(number_format($event['price'], 2)); ?> دينار ليبي</p>
    <p>إجمالي السعر الأصلي: <?php echo htmlspecialchars(number_format($originalPrice, 2)); ?> دينار ليبي</p>
    <p>الخصم بناءً على العمر والوظيفة: <?php echo htmlspecialchars(number_format($originalPrice - $discountedPrice, 2)); ?> دينار ليبي</p>
    <p>الخصم بناءً على النقاط: <?php echo htmlspecialchars(number_format($additionalDiscount, 2)); ?> دينار ليبي</p>
    <p><strong>السعر النهائي بعد الخصم: <?php echo htmlspecialchars(number_format($finalPrice, 2)); ?> دينار ليبي</strong></p>

    <form method="POST" action="checkout.php?booking_id=<?php echo $bookingId; ?>">
        <div class="payment-field">
            <label for="payment_method">طريقة الدفع:</label>
            <select name="payment_method" id="payment_method" onchange="togglePaymentFields()">
                <option value="visa">Visa</option>
                <option value="paypal">PayPal</option>
                <option value="mobocash">Mobo Cash</option>
                <option value="edfa3li">ادفع لي</option>
            </select>
        </div>

        <div id="visa_fields" class="payment-field">
            <label for="card_number">رقم البطاقة:</label>
            <input type="text" name="card_number" id="card_number" placeholder="أدخل رقم البطاقة">
            <label for="card_expiry">تاريخ انتهاء الصلاحية:</label>
            <input type="text" name="card_expiry" id="card_expiry" placeholder="MM/YY">
            <label for="card_code">رمز CVV:</label>
            <input type="text" name="card_code" id="card_code" placeholder="أدخل رمز CVV">
            <label for="cardholder_name">اسم حامل البطاقة:</label>
            <input type="text" name="cardholder_name" id="cardholder_name" placeholder="أدخل اسم حامل البطاقة">
        </div>

        <div id="paypal_fields" class="payment-field">
            <label for="paypal_email">البريد الإلكتروني لحساب PayPal:</label>
            <input type="email" name="paypal_email" id="paypal_email" placeholder="أدخل بريد PayPal">
            <label for="paypal_code">رمز التأكيد:</label>
            <input type="text" name="paypal_code" id="paypal_code" placeholder="أدخل رمز التأكيد">
        </div>

        <div id="mobo_fields" class="payment-field">
            <label for="mobo_account_number">رقم حساب Mobo Cash:</label>
            <input type="text" name="mobo_account_number" id="mobo_account_number" placeholder="أدخل رقم الحساب">
            <label for="mobo_confirmation_code">رمز التأكيد:</label>
            <input type="text" name="mobo_confirmation_code" id="mobo_confirmation_code" placeholder="أدخل رمز التأكيد">
        </div>

        <div id="account_fields" class="payment-field">
            <label for="account_number">رقم الحساب:</label>
            <input type="text" name="account_number" id="account_number" placeholder="أدخل رقم الحساب">
            <label for="account_code">رمز الحساب:</label>
            <input type="text" name="account_code" id="account_code" placeholder="أدخل رمز الحساب">
        </div>

        <button type="submit">أدفع الآن</button>
    </form>
</div>

</body>
</html>

