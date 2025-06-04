<?php
class DatabaseController {
  private static $host = "localhost";
  private static $username = "usuario";
  private static $password = "usuario";
  private static $dbname = "ShopList";
  private static $options = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
      PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
  ];
  private static $connection = null;
  public static function connect() {
      if (self::$connection === null) {
          self::$connection = new PDO(
              'mysql:host='.self::$host.';dbname='.self::$dbname, 
              self::$username, 
              self::$password, 
              self::$options
          );
      }
      return self::$connection;
  }
}