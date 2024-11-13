<?php

class Report {
    // السمات المحمية
    protected $report_id;
    protected $admin_id;
    protected $report_type;
    protected $date_generated;
    protected $role_id;

    // constructor لتعيين السمات
    public function __construct($report_id, $admin_id, $report_type, $date_generated, $role_id) {
        $this->report_id = $report_id;
        $this->admin_id = $admin_id;
        $this->report_type = $report_type;
        $this->date_generated = $date_generated;
        $this->role_id = $role_id;
    }

    // دوال get للسمات
    public function getReportId() {
        return $this->report_id;
    }

    public function getAdminId() {
        return $this->admin_id;
    }

    public function getReportType() {
        return $this->report_type;
    }

    public function getDateGenerated() {
        return $this->date_generated;
    }

    public function getRoleId() {
        return $this->role_id;
    }

    // دالة لحساب إجمالي التذاكر المباعة
    public function getTotalTicketsSold($conn) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_tickets FROM bookings");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total_tickets'] ?? 0;
    }

    // دالة لإحصاء الحدث الأكثر مبيعًا
    public function getMostPopularEvent($conn) {
        $stmt = $conn->prepare("
            SELECT event_id, COUNT(*) AS ticket_count 
            FROM bookings 
            GROUP BY event_id 
            ORDER BY ticket_count DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // دالة لتقديم تقرير مبيعات التذاكر
    public function getTicketSalesReport($conn) {
        $stmt = $conn->prepare("
            SELECT event_id, SUM(total_price) AS total_sales 
            FROM bookings 
            GROUP BY event_id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}