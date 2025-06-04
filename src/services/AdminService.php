<?php
// src/services/AdminService.php
require_once __DIR__ . '/../repositories/AdminRepository.php';
class AdminService {
    private $adminRepository;

    public function __construct(AdminRepository $adminRepository) {
        $this->adminRepository = $adminRepository;
    }

    public function login(string $username, string $password): string {
        $admin = $this->adminRepository->findAdminByUsername($username);

        if ($admin && password_verify($password, $admin->password)) {
            if(session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_username'] = $username;
            return "success";
        }

        return "Credenciales incorrectas o no tienes privilegios de administrador";
    }

    public function adminExists(string $username): bool {
        return $this->adminRepository->existsAdmin($username);
    }

    public function getGenderDistribution(): array {
        return $this->adminRepository->getGenderDistribution();
    }
}
