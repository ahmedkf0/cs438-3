<?php
require_once __DIR__ . '/../config/db.php';
use PHPUnit\Framework\TestCase;
use Config\Database;
use Classes\Gift;

class GiftTest extends TestCase
{
    private $db;
    private $gift;

    protected function setUp(): void
    {
        // إنشاء اتصال بقاعدة البيانات
        $database = new Database();
        $this->db = $database->connect();

        // تنظيف قاعدة البيانات قبل كل اختبار
        $this->db->exec("DELETE FROM gifts");
        $this->db->exec("DELETE FROM users");
        $this->db->exec("DELETE FROM events");

        // إعداد بيانات أولية
        $this->db->exec("INSERT INTO users (user_id, name, email) VALUES (1, 'Test Sender', 'sender@example.com')");
        $this->db->exec("INSERT INTO users (user_id, name, email) VALUES (2, 'Test Recipient', 'recipient@example.com')");
        $this->db->exec("INSERT INTO events (event_id, title, price) VALUES (1, 'Test Event', 50.00)");

        // إنشاء كائن Gift
        $this->gift = new Gift($this->db);
    }

    public function testCreateGiftForEventSuccess()
    {
        // تعريف البيانات
        $userId = 1;
        $eventId = 1;
        $recipientEmail = 'recipient@example.com';
        $totalPrice = 100.00;
        $numTickets = 2;

        // تنفيذ العملية
        $result = $this->gift->createGiftForEvent($userId, $eventId, $recipientEmail, $totalPrice, $numTickets);

        // التحقق من نجاح العملية
        $this->assertTrue($result, "Failed to create gift for event.");

        // التحقق من إدراج الهدية في قاعدة البيانات
        $stmt = $this->db->query("SELECT * FROM gifts WHERE user_id = 1 AND event_id = 1");
        $giftData = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($giftData, "Gift not found in database.");
        $this->assertEquals($recipientEmail, $giftData['recipient_email'], "Recipient email does not match.");
        $this->assertEquals($totalPrice, $giftData['total_price'], "Total price does not match.");
    }

    public function testCreateGiftForEventInvalidData()
    {
        // بريد إلكتروني غير صالح
        $result = $this->gift->createGiftForEvent(1, 1, 'invalid_email', 100.00, 2);
        $this->assertFalse($result, "Gift creation should fail with invalid email.");

        // عدد تذاكر غير صالح
        $result = $this->gift->createGiftForEvent(1, 1, 'recipient@example.com', 100.00, -1);
        $this->assertFalse($result, "Gift creation should fail with negative tickets.");

        // سعر إجمالي غير صالح
        $result = $this->gift->createGiftForEvent(1, 1, 'recipient@example.com', -50.00, 2);
        $this->assertFalse($result, "Gift creation should fail with negative total price.");
    }

    public function testGetGiftsForRecipient()
    {
        // إعداد بيانات الهدايا
        $this->gift->createGiftForEvent(1, 1, 'recipient@example.com', 100.00, 2);

        // الحصول على الهدايا للمستلم
        $recipientEmail = 'recipient@example.com';
        $gifts = $this->gift->getGiftsForRecipient($recipientEmail);

        // التحقق من استرجاع الهدايا بشكل صحيح
        $this->assertNotEmpty($gifts, "No gifts found for recipient.");
        $this->assertCount(1, $gifts, "Incorrect number of gifts retrieved.");
        $this->assertEquals(1, $gifts[0]['event_id'], "Event ID does not match.");
        $this->assertEquals(100.00, $gifts[0]['total_price'], "Total price does not match.");
    }

    public function testGetGiftsForNonExistentRecipient()
    {
        // التحقق من استرجاع الهدايا لبريد غير موجود
        $gifts = $this->gift->getGiftsForRecipient('nonexistent@example.com');
        $this->assertEmpty($gifts, "Gifts should be empty for a nonexistent recipient.");
    }

    public function testCreateGiftForNonExistentUserOrEvent()
    {
        // مستخدم غير موجود
        $result = $this->gift->createGiftForEvent(999, 1, 'recipient@example.com', 100.00, 2);
        $this->assertFalse($result, "Gift creation should fail for non-existent user.");

        // حدث غير موجود
        $result = $this->gift->createGiftForEvent(1, 999, 'recipient@example.com', 100.00, 2);
        $this->assertFalse($result, "Gift creation should fail for non-existent event.");
    }

    public function testCreateGiftForLargeValues()
    {
        // قيم كبيرة
        $result = $this->gift->createGiftForEvent(1, 1, 'recipient@example.com', 1000000.00, 1000);
        $this->assertTrue($result, "Gift creation failed for large values.");
    }
}
