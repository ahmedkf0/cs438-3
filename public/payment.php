<?php
require_once '../config/db.php';
require_once '../classes/Payment.php';

use Config\Database;
use Classes\Payment;

$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من المدخلات
    $bookingId = $_POST['booking_id'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? null;
    $amount = $_POST['amount'] ?? null;

    if (!$bookingId || !$paymentMethod || !$amount) {
        die("جميع الحقول مطلوبة لإتمام العملية.");
    }

    try {
        // إنشاء عملية الدفع
        $payment = new Payment($bookingId, $paymentMethod, $amount);
        if ($payment->completePayment()) {
            echo "<p style='color: green;'>تمت عملية الدفع بنجاح!</p>";
        } else {
            echo "<p style='color: red;'>فشلت عملية الدفع. الرجاء المحاولة مرة أخرى.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>حدث خطأ: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>الدفع</title>
</head>
<body>
    <h1>إتمام عملية الدفع</h1>
    <form method="POST" action="checkout.php">
        <label for="payment_method">طريقة الدفع:</label>
        <select name="payment_method" id="payment_method" required>
            <option value="credit_card">بطاقة ائتمان</option>
            <option value="paypal">PayPal</option>
        </select>
        
        <!-- الحقول المخفية -->
        <input type="hidden" name="booking_id" value="1"> <!-- استبدل بالقيمة الديناميكية -->
        <input type="hidden" name="amount" value="100"> <!-- استبدل بالقيمة الديناميكية -->
        
        <button type="submit">أدفع الآن</button>
    </form>
</body>
</html>
