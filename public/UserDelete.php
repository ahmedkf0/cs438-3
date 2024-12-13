<?php
// تضمين ملف الاتصال بقاعدة البيانات
require_once '../config/db.php'; // تأكد من أن هذا المسار صحيح
require_once '../classes/Admin.php';



$message = "";
$userDetails = [];

// معالجة البحث عن المستخدم
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_value'])) {
    $searchValue = $_POST['search_value'];

    // إنشاء كائن Admin
    $admin = new Admin(0, '', '', '', '', '', $conn);

    // تحقق مما إذا كان الإدخال هو رقم (user_id) أو نص (name)
    if (is_numeric($searchValue)) {
        // بحث بواسطة user_id
        $userDetails = $admin->getUserById($searchValue);
    } else {
        // بحث بواسطة name
        $userDetails = $admin->getUserByName($searchValue);
    }

    // إضافة رسالة تصحيح لمعرفة ما إذا كانت البيانات تم استرجاعها
    if (empty($userDetails)) {
        $message = "لم يتم العثور على مستخدم.";
    }
}

// معالجة الحذف
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userIdToDelete = $_POST['user_id_to_delete'];
    $admin = new Admin($userIdToDelete, '', '', '', '', '', $conn);
    
    if ($admin->deleteUserWithReferences($userIdToDelete)) {
        $message = "تم حذف المستخدم   !";
        $userDetails = []; // تفريغ بيانات المستخدم
    } else {
        $message = "حدث خطأ أثناء حذف المستخدم.";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> حدف المستخدم</title>
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

        <h1>حدف المستخدم</h1>

        <form method="POST" id="search-form">
            <label for="search_value"> البحث بواسطة الاسم او معرف المستخدم : </label>
            <input type="text" id="search_value" name="search_value" required placeholder=" الأسم او معرف المستخدم ">
            <button type="submit">بحث</button>
        </form>

        <?php if (!empty($userDetails)): ?>
            <div id="user-details">
                <h2>تفاصيل المستخدم</h2>
                <p>الاسم: <?php echo htmlspecialchars($userDetails['name']); ?></p>
                <p>البريد الإلكتروني: <?php echo htmlspecialchars($userDetails['email']); ?></p>
                <p>رقم الهاتف: <?php echo htmlspecialchars($userDetails['phone_number']); ?></p>

                <form method="POST" action="">
                    <input type="hidden" name="user_id_to_delete" value="<?php echo htmlspecialchars($userDetails['user_id']); ?>">
                    <button type="submit" name="delete_user">حدف المستخدم</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </main>
</body>
</html>