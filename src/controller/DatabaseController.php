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
      $query = "SELECT m.*, u.username, u.profile_image 
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

    public static function addLike($userId, $messageId) {
      $pdo = self::connect();
      try {
          $stmt = $pdo->prepare("INSERT INTO likes (user_id, message_id) VALUES (?, ?)");
          return $stmt->execute([$userId, $messageId]);
      } catch (PDOException $e) {
          // Evita errores si ya dio like
          return false;
      }
    }

    public static function removeLike($userId, $messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND message_id = ?");
        return $stmt->execute([$userId, $messageId]);
    }

    public static function getLikesCount($messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE message_id = ?");
        $stmt->execute([$messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public static function hasLiked($userId, $messageId) {
        $pdo = self::connect();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM likes WHERE user_id = ? AND message_id = ?");
        $stmt->execute([$userId, $messageId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    }

    public static function addComment($userId, $messageId, $content) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("INSERT INTO comments (user_id, message_id, content) VALUES (?, ?, ?)");
      return $stmt->execute([$userId, $messageId, $content]);
  }

  public static function getComments($messageId) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("
          SELECT c.*, u.username, u.profile_image 
          FROM comments c
          JOIN User u ON c.user_id = u.id
          WHERE c.message_id = ?
          ORDER BY c.created_at ASC
      ");
      $stmt->execute([$messageId]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function deleteComment($userId, $commentId) {
      $pdo = self::connect();
      $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
      return $stmt->execute([$commentId, $userId]);
  }

  }