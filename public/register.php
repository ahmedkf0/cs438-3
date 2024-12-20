<?php
require_once '../config/db.php';
require_once '../classes/User.php';

use Config\Database;
use Classes\User;

$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $birthdate = $_POST['birthdate'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];
    $referralCode = $_POST['referral_code'] ?? null; // كود الإحالة

    if ($password !== $confirmPassword) {
        $error = "كلمتا المرور غير متطابقتين.";
    } else {
        $occupation = ($role === 'client') ? $_POST['occupation'] : null;

        $registered = User::register($db, $name, $email, $password, $role, $phone_number, $birthdate, $occupation, $referralCode);

        if ($registered) {
            header("Location: login.php");
            exit();
        } else {
            $error = "فشل في إنشاء الحساب. البريد الإلكتروني قد يكون مسجلاً مسبقاً.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إنشاء حساب</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="navbar">
        <a href="index.php">الفعاليات</a>
        <a href="login.php">تسجيل الدخول</a>
    </div>

    <div class="register-container">
        <h2>إنشاء حساب جديد</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post" action="register.php">
            <label for="name">الاسم:</label>
            <input type="text" name="name" id="name" required>
            <br>
            <label for="email">البريد الإلكتروني:</label>
            <input type="email" name="email" id="email" required>
            <br>
            <label for="phone_number">رقم الهاتف:</label>
            <input type="text" name="phone_number" id="phone_number" required>
            <br>
            <label for="birthdate">تاريخ الميلاد:</label>
            <input type="date" name="birthdate" id="birthdate" required>
            <br>
            <label for="password">كلمة المرور:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <label for="confirm_password">تأكيد كلمة المرور:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <br>
            <label for="role">نوع الحساب:</label>
            <select name="role" id="role" required onchange="toggleOccupation()">
                <option value="client">عميل</option>
                <option value="admin">أدمن</option>
            </select>
            <br>

            <div id="occupation-field" style="display: none;">
                <label for="occupation">الوظيفة:</label>
                <input type="text" name="occupation" id="occupation">
                <br>
            </div>

            <!-- إضافة حقل كود الإحالة -->
            <label for="referral_code">كود الإحالة (اختياري):</label>
            <input type="text" name="referral_code" id="referral_code">
            <br>

            <script>
                function toggleOccupation() {
                    const role = document.getElementById('role').value;
                    document.getElementById('occupation-field').style.display = (role === 'client') ? 'block' : 'none';
                }
            </script>

            <br>
            <button type="submit" class="btn">إنشاء حساب</button>
        </form>
    </div>

</body>
</html>
