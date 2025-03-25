<?php

class DriversController {
    private $connection;

    public function __construct() {
        $this->connection = DatabaseController::connect();
    }

    public static function addDriver($first_name, $last_name, $team_id, $country, $flag_url, $piloto_img) {
        $sql = "INSERT INTO drivers (first_name, last_name, team_id, country, flag_url, piloto_img) 
                VALUES (:first_name, :last_name, :team_id, :country, :flag_url, :piloto_img)";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':first_name', $first_name);
        $statement->bindValue(':last_name', $last_name);
        $statement->bindValue(':team_id', $team_id); // Asegúrate de que esto esté correcto
        $statement->bindValue(':country', $country);
        $statement->bindValue(':flag_url', $flag_url);
        $statement->bindValue(':piloto_img', $piloto_img);
    
        if ($statement->execute()) {
            return true;
        } else {
            $errorInfo = $statement->errorInfo();
            echo "Error al ejecutar la consulta: " . $errorInfo[2];
            return false;
        }
    }
    
    public static function editDriver($id, $first_name, $last_name, $team_id, $country, $flag_url, $piloto_img) {
        $sql = "UPDATE drivers 
                SET first_name = :first_name, last_name = :last_name, team_id = :team_id, 
                    country = :country, flag_url = :flag_url, piloto_img = :piloto_img 
                WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':first_name', $first_name);
        $statement->bindValue(':last_name', $last_name);
        $statement->bindValue(':team_id', $team_id); // Asegúrate de que esto esté correcto
        $statement->bindValue(':country', $country);
        $statement->bindValue(':flag_url', $flag_url);
        $statement->bindValue(':piloto_img', $piloto_img);
    
        if ($statement->execute()) {
            return true;
        } else {
            $errorInfo = $statement->errorInfo();
            echo "Error al ejecutar la consulta: " . $errorInfo[2];
            return false;
        }
    }

    public static function deleteDriver($id) {
        $sql = "DELETE FROM drivers WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);

        if ($statement->execute()) {
            return true;
        } else {
            // Mostrar errores de la consulta
            $errorInfo = $statement->errorInfo();
            echo "Error al ejecutar la consulta: " . $errorInfo[2];
            return false;
        }
    }

    public static function getDriverById($id) {
        $sql = "SELECT * FROM drivers WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);

        if ($statement->execute()) {
            return $statement->fetch(PDO::FETCH_ASSOC);
        } else {
            // Mostrar errores de la consulta
            $errorInfo = $statement->errorInfo();
            echo "Error al ejecutar la consulta: " . $errorInfo[2];
            return false;
        }
    }
}