<?php
namespace Classes;

require_once 'User.php';

class Admin extends User {
    public function addEvent(Event $event) {}
    public function updateEvent(Event $event) {}
    public function deleteEvent(int $eventId) {}
    public function manageUsers() {}
    public function generateReports() {}
}
//ahmed ssss
