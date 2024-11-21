<?php

namespace Classes;

use PDO;

class Review {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // إضافة تقييم وتعليق
    public function addReview($eventId, $userId, $rating, $reviewText) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reviews (event_id, user_id, rating, review_text)
                VALUES (:event_id, :user_id, :rating, :review_text)
            ");
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
            $stmt->bindParam(':review_text', $reviewText, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error adding review: " . $e->getMessage());
            return false;
        }
    }

    // استرجاع جميع المراجعات لحدث معين
    public function getReviewsByEvent($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.name AS user_name 
                FROM reviews r
                JOIN users u ON r.user_id = u.user_id
                WHERE r.event_id = :event_id
                ORDER BY r.created_at DESC
            ");
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reviews: " . $e->getMessage());
            return [];
        }
    }



    // حساب متوسط التقييم لحدث معين
    public function getAverageRating($eventId) {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(rating) AS average_rating
                FROM reviews
                WHERE event_id = :event_id
            ");
            $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['average_rating'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error calculating average rating: " . $e->getMessage());
            return 0;
        }
    }
}
