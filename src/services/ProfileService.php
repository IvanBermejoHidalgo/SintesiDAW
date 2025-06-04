<?php
require_once __DIR__ . '/../repositories/ProfileRepository.php';

class ProfileService {
    public function getProfileData($userId, $viewerId) {
        $user = ProfileRepository::getUserById($userId);
        if (!$user) return null;

        $messages = ProfileRepository::getMessagesByUser($userId, $viewerId);
        return [
            'profileUser' => $user,
            'userMessages' => $messages
        ];
    }

    public function updateProfile($userId, $formData, $fileData) {
        if ($userId != $_SESSION['user_id']) {
            throw new Exception("No puedes editar este perfil");
        }

        $changes = false;

        if (!empty($formData['username']) && $formData['username'] !== $_SESSION['username']) {
            $username = trim($formData['username']);
            if (strlen($username) < 3 || strlen($username) > 20) {
                throw new Exception("El nombre de usuario debe tener entre 3 y 20 caracteres");
            }

            if (ProfileRepository::updateUsername($userId, $username)) {
                $_SESSION['username'] = $username;
                $changes = true;
            }
        }

        if (isset($fileData['profile_image']) && $fileData['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '/public/images/profiles/';
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
            if (!file_exists($uploadPath)) mkdir($uploadPath, 0755, true);

            $ext = pathinfo($fileData['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array(strtolower($ext), $allowed)) {
                if (move_uploaded_file($fileData['profile_image']['tmp_name'], $uploadPath . $filename)) {
                    $newPath = $uploadDir . $filename;

                    $oldImage = ProfileRepository::getUserById($userId)['profile_image'];
                    if ($oldImage && $oldImage !== '/public/images/default-profile.png') {
                        @unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
                    }

                    ProfileRepository::updateProfileImage($userId, $newPath);
                    $changes = true;
                }
            }
        }

        return $changes;
    }

    public function deleteAccount($userId, $password) {
        if ($userId != $_SESSION['user_id']) throw new Exception("No puedes eliminar esta cuenta");
        if (empty($password)) throw new Exception("Debes ingresar tu contraseña");

        $user = ProfileRepository::getUserById($userId);
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception("Contraseña incorrecta");
        }

        return ProfileRepository::deleteUserAccount($userId);
    }
}
