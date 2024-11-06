<?php
// تضمين جميع الملفات المطلوبة
include_once('Connection.pdo.php');
require_once '../classes/Ticket.php';
require_once '../classes/Discount.php';
require_once '../classes/Booking.php';
require_once '../classes/payment.php';
require_once '../classes/User.php'; 
require_once '../classes/Event.php';

    

?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إزالة الحدث</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .delete-event-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s;
        }

        .delete-event-container:hover {
            transform: scale(1.02);
        }

        h1, h2 {
            color: #333;
            text-align: center;
        }

        form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            width: 200px;
        }

        button {
            padding: 10px 15px;
            border: none;
            background-color: #5cb85c;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s;
        }

        table th {
            background-color: #f8f8f8;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        #delete-message {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
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
    <main class="delete-event-container">
    <a href="eventmanagement.php" class="back-button">الرجوع إلى الخلف</a>
        <h1>إزالة حدث</h1>
        
        <!-- نموذج البحث عن الحدث -->
        <form method="post" action="">
            <input type="text" name="event_id" id="search-event-id" placeholder="ابحث برقم الحدث" required>
            <button type="submit">حدف الحدث</button>
        </form>

        <!-- عرض جميع الأحداث المتاحة -->
        <h2>الأحداث المتاحة</h2>
        <table>
            <thead>
                <tr>
                    <th>رقم الحدث</th>
                    <th>اسم الحدث</th>
                    <th>وصف الحدث</th>
                    <th>تاريخ الحدث</th>
                    <th>وقت الحدث</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // جلب الأحداث
                $events = Event::fetchAllEvents($conn);
                if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($event['event_id']); ?></td>
                            <td><?php echo htmlspecialchars($event['title']); ?></td>
                            <td><?php echo htmlspecialchars($event['description']); ?></td>
                            <td><?php echo htmlspecialchars($event['date']); ?></td>
                            <td><?php echo htmlspecialchars($event['time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">لا توجد أحداث متاحة.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="delete-message">
            <?php
           if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
        
            // استرجاع بيانات الحدث من قاعدة البيانات باستخدام event_id
            $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = :event_id");
            $stmt->bindParam(':event_id', $event_id);
            $stmt->execute();
            $event_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($event_data) {
                // إنشاء كائن من كلاس Event مع البيانات المسترجعة
                $event = new Event (
                    $event_data['title'],
                    $event_data['description'],
                    $event_data['date'],
                    $event_data['time'],
                    $event_data['location'],
                    $event_data['price'],
                    $event_data['available_seats'],
                    $event_data['age_restriction']
                );
                
                // تعيين event_id
                $event->setEventId($event_data['event_id']); // تعيين event_id المناسب
            
                // استدعاء دالة الحذف
                $message = $event->deleteEvent($conn);
                echo $message; // عرض الرسالة
            } else {
                echo "<div class='error-message'>الحدث غير موجود.</div>";
            }
        }
            ?>
        </div>
    </main>
</body>
</html>