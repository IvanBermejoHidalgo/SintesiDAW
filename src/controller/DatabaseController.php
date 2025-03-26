<?php

// General singleton class.

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

    public static function getAllMessages()
    {
        $pdo = self::connect();
        $query = "SELECT m.*, u.username 
                  FROM messages m
                  JOIN User u ON m.user_id = u.id
                  ORDER BY m.created_at DESC
                  LIMIT 50";
        
        $stmt = $pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function postNewMessage($userId, $content)
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())");
        return $stmt->execute([$userId, $content]);
    }

    public static function deleteMessage($userId, $messageId) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ? AND user_id = ?");
      return $stmt->execute([$messageId, $userId]);
    }

  }