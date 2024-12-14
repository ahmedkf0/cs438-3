<?php

require_once 'Admin.php';

class UserFacade {
    private $admin;

    public function __construct($conn) {
        $this->admin = new Admin(0, '', '', '', '', null, $conn);
    }

    public function loginUser($name, $password) {
        return $this->admin->login($name, $password);
    }

    public function updateUser($userId, $data) {
        return $this->admin->updateUser($userId, $data);
    }

    public function deleteUser($userId) {
        return $this->admin->deleteUserWithReferences($userId);
    }

    public function getUserByName($name) {
        return $this->admin->getUserByName($name);
    }

    public function getUserById($userId) {
        return $this->admin->getUserById($userId);
    }
}

?>