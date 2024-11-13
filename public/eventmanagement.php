<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة الأحداث</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .event-management-container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #2980b9;
        }
        .options {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        .option {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .option:hover {
            background-color: #0056b3;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <main class="event-management-container">
        <a href="Selection.html" class="back-button">الرجوع إلى الخلف</a>
        <h1>إدارة الأحداث</h1>
        <div class="options">
            <a href="addevent.php" class="option">إضافة حدث</a>
            <a href="Searchevent.php" class="option">تعديل الحدث</a>
            <a href="eventdelete.php" class="option">إزالة الحدث</a>
        </div>
    </main>
</body>
</html>