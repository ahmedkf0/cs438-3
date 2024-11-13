<?php
include_once('Connection.pdo.php');
require_once '../classes/Event.php';
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة حدث</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .add-event-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .back-button {
            display: block;
            margin-bottom: 20px;
            text-align: center;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 4px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #0056b3;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .success-message {
            color: #5cb85c;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 4px;
        }

        .error-message {
            color: #a94442;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <main class="add-event-container">
        <a href="eventmanagement.php" class="back-button">الرجوع إلى إدارة الأحداث</a>
        <h1>إضافة حدث جديد</h1>
        <?php
        $message = "";
        if (isset($_POST['addevent'])) {
            $event = new Event(
                $_POST['title'],
                $_POST['description'],
                $_POST['date'],
                $_POST['time'],
                $_POST['location'],
                $_POST['price'],
                $_POST['available_seats'],
                $_POST['age_restriction']
            );
            $message = $event->insertEvent($conn);
        }

        // عرض الرسالة في الأعلى
        if ($message) {
            echo $message;
        }
        ?>
        <form id="add-event-form" method="POST">
            <label for="event-title">عنوان الحدث:</label>
            <input type="text" name="title" required>
            <label for="event-description">شرح عن الحدث:</label>
            <textarea name="description" required></textarea>
            <label for="event-date">تاريخ الحدث:</label>
            <input type="date" name="date" required>
            <label for="event-time">وقت الحدث:</label>
            <input type="time" name="time" required>
            <label for="event-location">موقع الحدث:</label>
            <input type="text" name="location" required>
            <label for="event-price">سعر الحدث:</label>
            <input type="number" name="price" required>
            <label for="available-seats">عدد الكراسي المتاحة:</label>
            <input type="number" name="available_seats" required>
            <label for="min-age">الحد الأدنى للعمر:</label>
            <input type="number" name="age_restriction" required>
            <button type="submit" name="addevent">إضافة الحدث</button>
        </form>
    </main>
</body>
</html>