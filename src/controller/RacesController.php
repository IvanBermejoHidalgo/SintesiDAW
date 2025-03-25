<?php

class RacesController {
    private static $connection;

    public static function init() {
        self::$connection = DatabaseController::connect();
    }

    // Añadir una carrera
    public static function addRace($grand_prix, $race_date, $winner, $team_id, $laps) {
        self::init();
        $sql = "INSERT INTO races (grand_prix, race_date, winner, team_id, laps) 
                VALUES (:grand_prix, :race_date, :winner, :team_id, :laps)";
        $statement = self::$connection->prepare($sql);
        $statement->bindValue(':grand_prix', $grand_prix);
        $statement->bindValue(':race_date', $race_date);
        $statement->bindValue(':winner', $winner);
        $statement->bindValue(':team_id', $team_id); // Asegúrate de que esto sea el ID del equipo
        $statement->bindValue(':laps', $laps);
        return $statement->execute();
    }

    // Editar una carrera
    public static function editRace($id, $grand_prix, $race_date, $winner, $team_id, $laps) {
        self::init();
        $sql = "UPDATE races 
                SET grand_prix = :grand_prix, race_date = :race_date, winner = :winner, 
                    team_id = :team_id, laps = :laps 
                WHERE id = :id";
        $statement = self::$connection->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':grand_prix', $grand_prix);
        $statement->bindValue(':race_date', $race_date);
        $statement->bindValue(':winner', $winner);
        $statement->bindValue(':team_id', $team_id); // Asegúrate de que esto esté correcto
        $statement->bindValue(':laps', $laps);
        return $statement->execute();
    }

    // Obtener una carrera por ID
    public static function getRaceById($id) {
        self::init();
        $sql = "SELECT * FROM races WHERE id = :id";
        $statement = self::$connection->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public static function deleteRace($id) {
        self::init(); // Asegúrate de inicializar la conexión
        $sql = "DELETE FROM races WHERE id = :id";
        $statement = self::$connection->prepare($sql);
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
}