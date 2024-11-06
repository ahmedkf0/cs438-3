
<?php
include_once("Connection.pdo.php");
require_once '../classes/User.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Getinfo'])) {
    $name = $_POST['name'];
    $pass = $_POST['password'];

    // إنشاء كائن من كلاس Admin
    $admin = new Admin(0, $name, '', '', 0, $pass); // userId يمكن أن تكون 0 للبدء

    // محاولة تسجيل الدخول
    $admin->login($conn, $name, $pass);
}
?>



<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            margin: 15px auto;
            border-radius: 5px;
            width: 300px; /* عرض محدد للصندوق */
            text-align: center; /* مركز النص */
        }
    </style>
</head>
<body>
    <main class="login-container">
        <h1>تسجيل دخول المشرف</h1>
        <form id="login-form" method="post">
            <label for="name">اسم المشرف:</label>
            <input type="text" name="name" required>
            <label for="password">الرمز السري:</label>
            <input type="password" name="password" required>
            <button type="submit" name="Getinfo">تسجيل الدخول</button>
        </form>

        <!-- عرض رسالة الخطأ إذا كانت موجودة -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?= $_SESSION['error_message']; ?>
            </div>
            <?php unset($_SESSION['error_message']); // مسح الرسالة بعد عرضها ?>
        <?php endif; ?>
    </main>
</body>
</html>