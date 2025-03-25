<?php

class AdminController {
    private $connection;

    public function __construct() {
        $this->connection = DatabaseController::connect();
    }

    public static function adminLogin($username, $password) {
        if (!(new self)->existAdmin($username)) {
            return "Admin username does not exist";
        } else {
            try {
                $sql = "SELECT id, password FROM User WHERE username = :username AND role = 'admin'";
                $statement = (new self)->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();

                $admin = $statement->fetch();

                if ($admin && password_verify($password, $admin->password)) {
                    // La autenticación es correcta
                    session_start();
                    $_SESSION['admin_id'] = $admin->id;
                    $_SESSION['admin_username'] = $username;
                    return "success";
                } else {
                    return "Nombre de usuario o contraseña incorrectos.";
                }
            } catch(PDOException $error) {
                return "Error: " . $error->getMessage();
            }
        }
    }

    public static function existAdmin($username) {
        try {
            $sql = "SELECT * FROM User WHERE username = :username AND role = 'admin'";
            $statement = (new self)->connection->prepare($sql);
            $statement->bindValue(':username', $username);
            $statement->setFetchMode(PDO::FETCH_OBJ);
            $statement->execute();

            $result = $statement->fetch();
            return !$result ? false : true;
        } catch(PDOException $error) {
            return false;
        }
    }
}