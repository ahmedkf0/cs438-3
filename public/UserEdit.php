<?php

// تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php'; // تأكد من أن هذا المسار صحيح
require_once '../classes/Admin.php';



$message = "";
$userDetails = [];

// معالجة البحث عن المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    // تأكد من تمرير جميع المعاملات المطلوبة
    $admin = new Admin($userId, '', '', '', '', '', $conn);
    $userDetails = $admin->getUserById($userId); // يجب أن تكون لديك دالة getUserById في Admin.php
}

// تحديث بيانات المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // يجب أن تمرر المعلومات اللازمة
    $userId = $_POST['user_id'];
    $admin = new Admin($userId, $_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['birthdate'], $_POST['password'], $conn);
    
    // استدعاء الدالة لتحديث المستخدم
 // إنشاء كائن Admin
 $admin = new Admin($userId, $_POST['name'], $_POST['email'], $_POST['phone_number'], $_POST['birthdate'], $password, $conn);

 // استدعاء الدالة لتحديث المستخدم
 if ($admin->updateUser($userId, $_POST)) {
     $message = "تم تحديث البيانات بنجاح!";
     
     // إعادة تعيين القيم بعد نجاح التحديث
     $userId = ""; // إعادة تعيين user_id
     $name = "";
     $email = "";
     $phone_number = "";
     $birthdate = "";
     $password = "";
 } else {
     $message = "حدث خطأ أثناء تحديث البيانات.";
 }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل بيانات المستخدم</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef2f3; /* لون خلفية لطيف */
        }

        main {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        main:hover {
            transform: translateY(-2px);
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #34495e;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            transition: border-color 0.3s;
            font-size: 14px;
        }

        input:focus, textarea:focus {
            border-color: #2980b9;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s;
        }

        button:hover {
            background-color: #219150;
            transform: translateY(-1px);
        }

        #delete-message {
            color: #c0392b;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main>
    <a href="UserMangment.php" style="display: inline-block; margin-bottom: 20px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">
        الرجوع للخلف
    </a>
        <h1>تعديل بيانات المستخدم</h1>
        <form method="POST" id="search-form">
            <label for="user_id">البحث بواسطة معرف المستخدم : </label>
            <input type="text" id="user_id" name="user_id" required placeholder=" ادخل معرف المستخدم  " value="<?php echo isset($userDetails['user_id']) ? $userDetails['user_id'] : ''; ?>">
            <button type="submit">بحث</button>
        </form>

        <?php if (!empty($userDetails)): ?>
            <div id="user-details">
                <h2>تفاصيل المستخدم</h2>
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $userDetails['user_id']; ?>">

                    <label for="name">اسم المستخدم:</label>
                    <input type="text" id="name" name="name" required value="<?php echo $userDetails['name']; ?>">

                    <label for="email">البريد الالكتروني:</label>
                    <input type="email" id="email" name="email" required value="<?php echo $userDetails['email']; ?>">
          
                    <label for="password">كلمة المرور:</label>
                    <input type="password" id="password" name="password" placeholder="تحديث كلمة المرور إذا رغبت">

                    <label for="phone_number">رقم الهاتف:</label>
                    <input type="text" id="phone_number" name="phone_number" required value="<?php echo $userDetails['phone_number']; ?>">

                    <label for="birthdate">تاريخ الميلاد:</label>
                    <input type="date" id="birthdate" name="birthdate" required value="<?php echo $userDetails['birthdate']; ?>">

                    <label for="role">الدور:</label>
                    <select id="role" name="role" required>
                        <option value="admin" <?php echo $userDetails['role'] === 'admin' ? 'selected' : ''; ?>>ادمن</option>
                        <option value="client" <?php echo $userDetails['role'] === 'client' ? 'selected' : ''; ?>>عميل</option>
                    </select>

                    <button type="submit" name="update">تحديث البيانات</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div id="delete-message"><?php echo $message; ?></div>
        <?php endif; ?>
    </main>
</body>
</html>