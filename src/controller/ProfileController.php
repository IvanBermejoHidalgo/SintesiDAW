<?php
class ProfileController {
    public function handleRequest() {
        $userId = $_GET['id'] ?? $_SESSION['user_id'];

        // Manejar la eliminación de cuenta
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
            $this->handleAccountDeletion($userId);
            return; // Salir después de manejar la eliminación
        }

        // Manejar la actualización del perfil si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['update_profile'])) {
                $this->handleProfileUpdate($userId);
            } elseif (isset($_POST['delete_account'])) {
                $this->handleAccountDeletion($userId);
            }
        }
        
        // Manejar la actualización del perfil si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
            $this->handleProfileUpdate($userId);
        }
        
        // Obtener datos del usuario del perfil
        $profileUser = DatabaseController::getUserById($userId);
        
        if (!$profileUser) {
            header("Location: /home");
            exit();
        }
        
        // Obtener mensajes del usuario
        $userMessages = DatabaseController::getMessagesByUser($userId, $_SESSION['user_id']);
        
        return [
            'profileUser' => $profileUser,
            'userMessages' => $userMessages
        ];
    }
    
    private function handleProfileUpdate($userId) {
        // Verificar que el usuario está editando su propio perfil
        if ($userId != $_SESSION['user_id']) {
            $_SESSION['error'] = "No puedes editar este perfil";
            header("Location: /profile");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
            $this->handleAccountDeletion($userId);
            return; // Salir después de manejar la eliminación
        }
        
        $changesMade = false;
        
        // Procesar cambio de nombre de usuario
        if (!empty($_POST['username']) && $_POST['username'] !== $_SESSION['username']) {
            $newUsername = trim($_POST['username']);
            
            // Validar el nombre de usuario
            if (strlen($newUsername) < 3 || strlen($newUsername) > 20) {
                $_SESSION['error'] = "El nombre de usuario debe tener entre 3 y 20 caracteres";
                header("Location: /profile");
                exit();
            }
            
            // Actualizar en base de datos
            if (DatabaseController::updateUsername($userId, $newUsername)) {
                $_SESSION['username'] = $newUsername;
                $changesMade = true;
            }
        }
        
        // Procesar la imagen de perfil
        $profileImage = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '/images/profiles/';
            $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
            
            // Crear directorio si no existe
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Validar y mover el archivo
            $fileExtension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath . $fileName)) {
                    $profileImage = $uploadDir . $fileName;
                    
                    // Eliminar la imagen anterior si existe y no es la predeterminada
                    $oldImage = DatabaseController::getUserById($userId)['profile_image'];
                    if ($oldImage && $oldImage !== '/images/default-profile.png' && file_exists($_SERVER['DOCUMENT_ROOT'] . $oldImage)) {
                        unlink($_SERVER['DOCUMENT_ROOT'] . $oldImage);
                    }
                    
                    // Actualizar en base de datos
                    DatabaseController::updateProfileImage($userId, $profileImage);
                    $changesMade = true;
                }
            }
        }
        
        if ($changesMade) {
            $_SESSION['success'] = "Perfil actualizado correctamente";
        } else {
            $_SESSION['info'] = "No se realizaron cambios";
        }
        
        header("Location: /profile");
        exit();
    }

    public function handlePublicProfileRequest($userId) {
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        // Obtener datos del usuario del perfil
        $profileUser = DatabaseController::getUserById($userId);
        
        if (!$profileUser) {
            header("Location: /home");
            exit();
        }
        
        // Obtener mensajes del usuario
        $userMessages = DatabaseController::getMessagesByUser($userId, $currentUserId);
        
        return [
            'profileUser' => $profileUser,
            'userMessages' => $userMessages
        ];
    }

    private function handleAccountDeletion($userId) {
        // Verificar que el usuario está intentando eliminar su propia cuenta
        if ($userId != $_SESSION['user_id']) {
            $_SESSION['error'] = "No puedes eliminar esta cuenta";
            header("Location: /profile");
            exit();
        }

        $password = $_POST['password'] ?? null;
        if (is_null($password) || $password === '') {
            $_SESSION['error'] = "Debes ingresar tu contraseña";
            header("Location: /profile");
            exit();
        }
        
        if (empty($password)) {
            $_SESSION['error'] = "Debes ingresar tu contraseña";
            header("Location: /profile");
            exit();
        }

        // Obtener usuario y verificar contraseña
        $user = DatabaseController::getUserById($userId);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Contraseña incorrecta";
            header("Location: /profile");
            exit();
        }
        
        // Eliminar la cuenta
        if (DatabaseController::deleteUserAccount($userId)) {
            // Cerrar sesión y redirigir
            session_destroy();
            header("Location: /");
            exit();
        } else {
            $_SESSION['error'] = "Error al eliminar la cuenta. Por favor, inténtalo de nuevo.";
            header("Location: /profile");
            exit();
        }
    }
}