<?php
namespace Controller;

require_once __DIR__ . '/DatabaseController.php';

class MessageController
{
    private $twig;
    private $db;

    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
        $this->db = DatabaseController::getConnection();
    }

    public function postMessage($userId, $content)
    {
        // Sanitizar el contenido
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        
        // Insertar en la base de datos
        $stmt = $this->db->prepare("INSERT INTO messages (user_id, content, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $content]);
        
        return true;
    }

    public function getAllMessages()
    {
        // Obtener mensajes con información de usuario
        $query = "SELECT m.*, u.username, u.profile_image 
                  FROM messages m
                  JOIN User u ON m.user_id = u.id
                  ORDER BY m.created_at DESC
                  LIMIT 50";
        
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}