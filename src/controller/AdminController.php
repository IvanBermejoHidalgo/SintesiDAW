<?php
// src/controllers/AdminController.php

require_once __DIR__ . '/../repositories/AdminRepository.php';
require_once __DIR__ . '/../services/AdminService.php';

class AdminController {
    private $adminService;

    public function __construct(AdminService $adminService) {
        $this->adminService = $adminService;
    }

    public function loginAction(string $username, string $password) {
        $result = $this->adminService->login($username, $password);
        if ($result === "success") {
            header("Location: /admin/dashboard");
            exit;
        } else {
            echo $result;
        }
    }

    public function genderDistributionAction() {
        $distribution = $this->adminService->getGenderDistribution();
        header('Content-Type: application/json');
        echo json_encode($distribution);
    }
}
