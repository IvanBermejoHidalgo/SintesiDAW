<?php

class AdminController {
    private static $connection;

    private static function init() {
        if (self::$connection === null) {
            self::$connection = DatabaseController::connect();
        }
    }

    public static function adminLogin($username, $password) {
        self::init();
        
        try {
            $sql = "SELECT id, password FROM User WHERE username = :username AND role = 'admin'";
            $statement = self::$connection->prepare($sql);
            $statement->bindValue(':username', $username);
            $statement->execute();

            $admin = $statement->fetch(PDO::FETCH_OBJ);

            if ($admin && password_verify($password, $admin->password)) {
                $_SESSION['admin_id'] = $admin->id;
                $_SESSION['admin_username'] = $username;
                return "success";
            }
            return "Credenciales incorrectas o no tienes privilegios de administrador";
        } catch(PDOException $error) {
            error_log("Error en login de admin: " . $error->getMessage());
            return "Error en el sistema";
        }
    }

    public static function existAdmin($username) {
        self::init();
        try {
            $sql = "SELECT * FROM User WHERE username = :username AND role = 'admin'";
            $statement = self::$connection->prepare($sql);
            $statement->bindValue(':username', $username);
            $statement->execute();

            return $statement->fetch() !== false;
        } catch(PDOException $error) {
            error_log("Error verificando admin: " . $error->getMessage());
            return false;
        }
    }
}