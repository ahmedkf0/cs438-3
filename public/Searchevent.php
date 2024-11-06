<?php
session_start(); // بدء جلسة جديدة أو استئناف الحالية
include_once('Connection.pdo.php'); // تضمين ملف الاتصال بقاعدة البيانات

// استعلام للحصول على جميع الأحداث من قاعدة البيانات
$stmt = $conn->prepare("SELECT * FROM events");
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// معالجة طلب البحث عن الحدث
if (isset($_POST['search_event'])) {
    $event_id = $_POST['event_id']; // الحصول على رقم الحدث من المدخلات
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event_data = $stmt->fetch(PDO::FETCH_ASSOC); // البحث عن الحدث

    // إذا تم العثور على الحدث، احفظه في الجلسة
    if ($event_data) {
        $_SESSION['event_data'] = $event_data; // حفظ بيانات الحدث في الجلسة
        $_SESSION['success_message'] = "تم العثور على الحدث بنجاح."; // رسالة نجاح
        header('Location: editevent.php'); // إعادة توجيه إلى صفحة تعديل الحدث
        exit();
    } else {
        $_SESSION['error_message'] = "لم يتم العثور على الحدث."; // رسالة خطأ
    }
}

// معالجة طلب حذف الحدث
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id']; // الحصول على رقم الحدث من المدخلات
    $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event_data = $stmt->fetch(PDO::FETCH_ASSOC); // البحث عن الحدث

    // إذا تم العثور على الحدث، احفظه في الجلسة
    if ($event_data) {
        $_SESSION['event_data'] = $event_data; // حفظ بيانات الحدث في الجلسة
        $_SESSION['success_message'] = "تم العثور على الحدث بنجاح."; // رسالة نجاح
        header('Location: eventdelete.php'); // إعادة توجيه إلى صفحة حذف الحدث
        exit();
    } else {
        $_SESSION['error_message'] = "لم يتم العثور على الحدث."; // رسالة خطأ
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بحث وتعديل الأحداث</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f8ff; /* لون خلفية هادئ */
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #ffffff; /* لون خلفية أبيض */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        h2 {
            color: #2980b9; /* لون العنوان الثانوي */
            border-bottom: 2px solid #2980b9; /* خط سفلي */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .search-box {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }
        input[type="text"] {
            width: 70%;
            padding: 12px;
            margin-right: 10px;
            border: 2px solid #2980b9; /* لون الحدود */
            border-radius: 5px;
            transition: border-color 0.3s; /* تأثير عند التركيز */
        }
        input[type="text"]:focus {
            border-color: #3498db; /* تغيير لون الحدود عند التركيز */
            outline: none; /* إزالة الإطار الافتراضي */
        }
        button {
            padding: 12px;
            background-color: #27ae60; /* لون الزر */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s; /* تأثير عند التركيز */
            margin-left: 5px; /* المسافة بين الأزرار */
        }
        button:hover {
            background-color: #219150; /* تغيير لون الزر عند التمرير */
        }
        .event-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #fafafa;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* ظل خفيف */
            transition: transform 0.2s, box-shadow 0.2s; /* تأثير عند التمرير */
        }
        .event-item:hover {
            transform: translateY(-5px); /* رفع العنصر عند الماوس */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* ظل أكبر عند التمرير */
        }
        .event-item h3 {
            margin: 0;
            color: #2980b9; /* لون العنوان */
            font-size: 1.6em; /* حجم أكبر */
        }
        .event-item p {
            margin: 5px 0;
            line-height: 1.5; /* زيادة المسافة بين الأسطر */
        }
        .edit-button {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #007bff; /* لون الزر */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s; /* تأثير عند التركيز */
        }
        .edit-button:hover {
            background-color: #0056b3; /* تغيير لون الزر عند التمرير */
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #28a745; /* لون الزر */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s; /* تأثير عند التركيز */
        }
        .back-button:hover {
            background-color: #218838; /* تغيير لون الزر عند التمرير */
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="eventmanagement.php" class="back-button">الرجوع إلى الخلف</a>
        <h1>بحث وتعديل الأحداث</h1>
        <!-- نموذج البحث عن الحدث -->
        <div class="search-box">
            <form method="POST" action="">
                <input type="text" name="event_id" placeholder="أدخل رقم الحدث للبحث" required>
                <button type="submit" name="search_event">بحث عن حدث</button>
            </form>
        </div>

        <!-- عرض جميع الأحداث المتاحة -->
        <h2>جميع الأحداث المتاحة</h2>
        <?php if ($events): ?>
            <?php foreach ($events as $event): ?>
                <div class="event-item">
                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p><strong>رقم الحدث:</strong> <?php echo htmlspecialchars($event['event_id']); ?></p>
                    <p><strong>الوصف:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                    <p><strong>التاريخ:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                    <p><strong>الوقت:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                    <p><strong>الموقع:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p><strong>السعر:</strong> <?php echo htmlspecialchars($event['price']); ?> ر.س</p>
                    <p><strong>عدد المقاعد المتاحة:</strong> <?php echo htmlspecialchars($event['available_seats']); ?></p>
                    <p><strong>الحد الأدنى للعمر:</strong> <?php echo htmlspecialchars($event['age_restriction']); ?> سنة</p>
                    <form method="POST" action="">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                        <button type="submit" name="edit_event" class="edit-button">تعديل البحث</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>لا توجد أحداث متاحة.</p>
        <?php endif; ?>
    </div>
</body>
</html>