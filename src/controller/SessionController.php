<?php

class SessionController {

    private $connection;

    public function __construct() {
        $this->connection = DatabaseController::connect();
    }

    public static function userSignUp($username, $email, $password) {
        if ((new self)->exist($username, $email)) {
            return "Username or email already exist"; // Retornar mensaje de error
        } else {
            try {
                $sql = "INSERT INTO User (username, email, password) VALUES (:username, :email, :password)";
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $statement = (new self)->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->bindValue(':email', $email);
                $statement->bindValue(':password', $hashed_password);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();

                return "Usuario registrado exitosamente"; // Retornar mensaje de éxito
            } catch(PDOException $error) {
                return "Error: " . $error->getMessage(); // Retornar mensaje de error
            }
        }
    }

    public static function userLogin($username, $password) {
        if (!(new self)->exist($username)) {
            return "Username does not exist"; // Retornar mensaje de error
        } else {
            try {
                $sql = "SELECT id, password FROM User WHERE username = :username";
                $statement = (new self)->connection->prepare($sql);
                $statement->bindValue(':username', $username);
                $statement->setFetchMode(PDO::FETCH_OBJ);
                $statement->execute();

                $user = $statement->fetch();

                if ($user && password_verify($password, $user->password)) {
                    // La autenticación es correcta
                    session_start();
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['username'] = $username;
                    return "success"; // Retornar éxito
                } else {
                    return "Nombre de usuario o contraseña incorrectos."; // Retornar mensaje de error
                }
            } catch(PDOException $error) {
                return "Error: " . $error->getMessage(); // Retornar mensaje de error
            }
        }
    }

    public static function exist($username, $email = null) {
        try {
            $sql = $email === null 
                ? "SELECT * FROM User WHERE username = :username"
                : "SELECT * FROM User WHERE username = :username AND email = :email";

            $statement = (new self)->connection->prepare($sql);
            $statement->bindValue(':username', $username);
            if ($email !== null) {
                $statement->bindValue(':email', $email);
            }
            $statement->setFetchMode(PDO::FETCH_OBJ);
            $statement->execute();

            $result = $statement->fetch();
            return !$result ? false : true;
        } catch(PDOException $error) {
            return false; // Retornar false en caso de error
        }
    }
}