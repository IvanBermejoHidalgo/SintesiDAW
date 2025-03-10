<?php

// General singleton class.

class DatabaseController {

    private static $host = "localhost";
    private static $username = "usuario";
    private static $password = "usuario";
    private static $dbname = "ShopList";
    //private $dsn = 'mysql:host='.$host.';dbname='.$dbname;
    private static $options = array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                  );

    // Hold the class instance.
    private static $instance = null;

    // The constructor is private
    // to prevent initiation with outer code.
    private function __construct()
    {
      // The expensive process (e.g.,db connection) goes here.
    }

    // The object is created from within the class itself
    // only if the class has no instance.
    public static function getInstance()
    {
      if (self::$instance == null)
      {
        self::$instance = new DatabaseController();
      }

      return self::$instance;
    }

    public static function connect () {
        try  {
            $connection = new PDO('mysql:host='.self::$host.';dbname='.self::$dbname, self::$username, self::$password, self::$options);
            return $connection;

          } catch(PDOException $error) {
              echo $sql . "<br>" . $error->getMessage();
              return null;
          }
    }

    public static function getTeams() {
      $sql = "SELECT * FROM teams";
      $statement = self::connect()->prepare($sql);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function addTeam($team_name, $driver1, $driver2, $img_team, $img_car) {
      $sql = "INSERT INTO teams (team_name, driver1, driver2, img_team, img_car) VALUES (:team_name, :driver1, :driver2, :img_team, :img_car)";
      $statement = self::connect()->prepare($sql);
      $statement->bindValue(':team_name', $team_name);
      $statement->bindValue(':driver1', $driver1);
      $statement->bindValue(':driver2', $driver2);
      $statement->bindValue(':img_team', $img_team);
      $statement->bindValue(':img_car', $img_car);
      return $statement->execute();
    }
    
    public static function deleteTeam($id) {
        $sql = "DELETE FROM teams WHERE id = :id";
        $statement = self::connect()->prepare($sql);
        $statement->bindValue(':id', $id);
        return $statement->execute();
    }
    
    public static function getDrivers() {
        $sql = "SELECT * FROM drivers";
        $statement = self::connect()->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRaces() {
        $sql = "SELECT * FROM races";
        $statement = self::connect()->prepare($sql);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTeamsWithDrivers() {
      $pdo = self::connect();
      $query = "
          SELECT t.team_name, t.img_team, t.img_car, 
                 d.id AS driver_id, d.first_name, d.last_name, d.country, d.flag_url, d.piloto_img
          FROM teams t
          LEFT JOIN drivers d ON t.team_name = d.team_name
          ORDER BY t.team_name, d.last_name
      ";
      $stmt = $pdo->query($query);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getWinners() {
      $sql = "SELECT winner, COUNT(*) as count FROM races GROUP BY winner";
      $statement = self::connect()->prepare($sql);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getTeamWinners() {
      $sql = "SELECT car_team_name, COUNT(*) as count FROM races GROUP BY car_team_name";
      $statement = self::connect()->prepare($sql);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }
  }