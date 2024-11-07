<?php
include_once('Connection.pdo.php');
require_once '../classes/Ticket.php';
require_once '../classes/discount.php';
require_once '../classes/booking.php';
require_once '../classes/payment.php';
require_once '../classes/User.php';
require_once '../classes/Event.php';
require_once '../classes/Admin.php';
require_once '../classes/report.php';

// تأكد من تمرير المتغيرات المناسبة إلى الكلاس Report
$report = new Report(null, null, null, null, null); // عليك ضبط القيم حسب الحاجة

$totalTickets = $report->getTotalTicketsSold($conn);
$mostPopularEvent = $report->getMostPopularEvent($conn);
$salesReport = $report->getTicketSalesReport($conn);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقارير</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .report-container {
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
        h2 {
            color: #2c3e50;
            margin-top: 20px;
        }
        p {
            margin: 5px 0;
        }
        .report-section {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fafafa;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <main class="report-container">
        <a href="Selection.html" class="back-button">الرجوع إلى الخلف</a>
        <h1>التقارير</h1>
        
        <div class="report-section">
            <h2>إجمالي التذاكر المباعة</h2>
            <p><?php echo $totalTickets; ?> تذكرة</p>
        </div>

        <div class="report-section">
            <h2>الحدث الأكثر شعبية</h2>
            <?php if ($mostPopularEvent): ?>
                <p>رقم الحدث: <?php echo htmlspecialchars($mostPopularEvent['event_id']); ?></p>
                <p>عدد التذاكر المباعة: <?php echo htmlspecialchars($mostPopularEvent['ticket_count']); ?></p>
            <?php else: ?>
                <p>لا توجد أحداث متاحة.</p>
            <?php endif; ?>
        </div>

        <div class="report-section">
            <h2>تقرير مبيعات التذاكر</h2>
            <?php if ($salesReport): ?>
                <ul>
                    <?php foreach ($salesReport as $report): ?>
                        <li>رقم الحدث: <?php echo htmlspecialchars($report['event_id']); ?> - إجمالي المبيعات: <?php echo htmlspecialchars($report['total_sales']); ?> ر.س</li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>لا توجد مبيعات مسجلة.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>