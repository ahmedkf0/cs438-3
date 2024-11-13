<?php
session_start();
require_once '../config/db.php';
require_once '../classes/User.php';

use Config\Database;
use Classes\User;

$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user = User::login($db, $email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user->getUserId();
        $_SESSION['user_name'] = $user->getName();
        $_SESSION['role'] = $user->getRole();

        if ($user->getRole() === 'admin') {
            header("Location: Selection.html");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";
    }
}


?>

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- شريط التنقل -->
    <div class="navbar">
        <a href="index.php">الفعاليات</a>
        <a href="my_bookings.php">حجوزاتي</a>
        <a href="register.php">إنشاء حساب جديد</a>
    </div>

    <div class="login-container">
        <h2>تسجيل الدخول</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="login.php">
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="password">كلمة المرور:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <button type="submit" class="btn">تسجيل الدخول</button>
        </form>
        <p>ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
    </div>

</body>
</html>