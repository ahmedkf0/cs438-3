<?php
require_once '../config/db.php';
require_once '../classes/Payment.php';

use Config\Database;
use Classes\Payment;

$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = $_POST['booking_id'];
    $paymentMethod = $_POST['payment_method'];
    $amount = $_POST['amount'];
    
    $payment = new Payment($bookingId, $paymentMethod, $amount);
    $payment->completePayment();
    
    echo "تمت عملية الدفع بنجاح!";
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
        <select name="payment_method" id="payment_method">
            <option value="credit_card">بطاقة ائتمان</option>
            <option value="paypal">PayPal</option>
        </select>
        <input type="hidden" name="booking_id" value="1"> <!-- مثال على الحجز -->
        <input type="hidden" name="amount" value="100"> <!-- مثال على المبلغ -->
        <button type="submit">أدفع الآن</button>
    </form>
</body>
</html>
