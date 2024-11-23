<?php
require_once __DIR__ . '/../config/db.php';

use PHPUnit\Framework\TestCase;
use Config\Database;
use Classes\Review;

class ReviewTest extends TestCase {
    private $db;
    private $review;

    protected function setUp(): void {
        // إنشاء اتصال بقاعدة البيانات
        $database = new Database();
        $this->db = $database->connect();
        $this->review = new Review($this->db);

        // تنظيف قاعدة البيانات قبل كل اختبار
        $this->db->exec("DELETE FROM reviews");
        $this->db->exec("DELETE FROM bookings");
        $this->db->exec("DELETE FROM events");
        $this->db->exec("DELETE FROM users");

        // إعداد بيانات أولية
        $this->db->exec("INSERT INTO users (user_id, name) VALUES (1, 'Test User')");
        $this->db->exec("INSERT INTO events (event_id, title) VALUES (1, 'Test Event')");
        $this->db->exec("INSERT INTO bookings (user_id, event_id) VALUES (1, 1)");
    }

    public function testAddReviewSuccess() {
        $result = $this->review->addReview(1, 1, 5, 'Excellent event!');
        $this->assertTrue($result, "Failed to add review.");
    }

    public function testAddReviewWithoutBooking() {
        // محاولة إضافة تقييم بدون حجز
        $result = $this->review->addReview(2, 1, 4, 'Nice event!');
        $this->assertFalse($result, "Review should not be added without booking.");
    }
    

    public function testGetReviewsByEvent() {
        // إضافة تقييم
        $this->review->addReview(1, 1, 4, 'Great event!');
        $reviews = $this->review->getReviewsByEvent(1);

        $this->assertCount(1, $reviews, "Failed to fetch reviews by event.");
        $this->assertEquals('Great event!', $reviews[0]['review_text'], "Review text does not match.");
    }

    public function testGetAverageRating() {
        // إضافة تقييمين
        $this->review->addReview(1, 1, 4, 'Good event');
        $this->review->addReview(1, 1, 5, 'Excellent event!');

        $averageRating = $this->review->getAverageRating(1);
        $this->assertEquals(4.5, $averageRating, "Average rating is incorrect.");
    }

    public function testDeleteReview() {
        // إضافة تقييم
        $this->review->addReview(1, 1, 4, 'Good event');
        $reviewsBefore = $this->review->getReviewsByEvent(1);
        $this->assertCount(1, $reviewsBefore, "Failed to add review.");

        // حذف التقييم
        $reviewId = $reviewsBefore[0]['review_id'];
        $result = $this->review->deleteReview($reviewId, 1);
        $this->assertTrue($result, "Failed to delete review.");

        $reviewsAfter = $this->review->getReviewsByEvent(1);
        $this->assertCount(0, $reviewsAfter, "Review was not deleted.");
    }
}
