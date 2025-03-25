<?php

class TeamsController {
    private $connection;

    public function __construct() {
        $this->connection = DatabaseController::connect();
    }

    public static function addTeam($team_name, $img_team, $img_car) {
        $sql = "INSERT INTO teams (team_name, img_team, img_car) 
                VALUES (:team_name, :img_team, :img_car)";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':team_name', $team_name);
        $statement->bindValue(':img_team', $img_team);
        $statement->bindValue(':img_car', $img_car);
        return $statement->execute();
    }
    
    public static function editTeam($id, $team_name, $img_team, $img_car) {
        $sql = "UPDATE teams 
                SET team_name = :team_name, img_team = :img_team, img_car = :img_car 
                WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':team_name', $team_name);
        $statement->bindValue(':img_team', $img_team);
        $statement->bindValue(':img_car', $img_car);
        return $statement->execute();
    }

    // Obtener un equipo por ID
    public static function getTeamById($id) {
        $sql = "SELECT * FROM teams WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_OBJ);
    }

    // Método para obtener los pilotos de un equipo
    public static function getDriversByTeam($team_id) {
        $sql = "SELECT * FROM drivers WHERE team_id = :team_id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':team_id', $team_id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteTeam($id) {
        $sql = "DELETE FROM teams WHERE id = :id";
        $statement = (new self)->connection->prepare($sql);
        $statement->bindValue(':id', $id);
        return $statement->execute();
    }

}