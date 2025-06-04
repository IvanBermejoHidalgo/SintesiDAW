<?php
require_once __DIR__ . '/../repositories/HomeRepository.php';
require_once __DIR__ . '/../repositories/MisListasRepository.php';

class HomeService {
    private HomeRepository $messageRepo;
    private $misListasRepository;

    public function __construct() {
        $db = new DatabaseController();
        $userId = $_SESSION['user_id'] ?? null;

        $this->messageRepo = new HomeRepository($db, $userId);
        $this->misListasRepository = new MisListasRepository($db, $userId);
    }

    public function handlePostActions(): void {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") return;

        if (isset($_POST['content'])) {
            $this->messageRepo->createMessageWithOptionalImageAndList();
        } elseif (isset($_POST['delete_message'])) {
            $this->messageRepo->deleteMessage($_SESSION['user_id'], (int)$_POST['delete_message']);
        } elseif (isset($_POST['like_message'])) {
            $this->messageRepo->addLike($_SESSION['user_id'], (int)$_POST['like_message']);
        } elseif (isset($_POST['unlike_message'])) {
            $this->messageRepo->unlikeMessage($_SESSION['user_id'], (int)$_POST['unlike_message']);
        } elseif (isset($_POST['comment_content'])) {
            $this->messageRepo->addComment($_SESSION['user_id'], (int)$_POST['message_id'], $_POST['comment_content']);
        } elseif (isset($_POST['delete_comment'])) {
            $this->messageRepo->deleteComment($_SESSION['user_id'], (int)$_POST['delete_comment']);
        }

        header("Location: /home");
        exit();
    }

    public function getAllMessagesWithUsers(): array {
        return $this->messageRepo->getAllMessagesWithUsers($_SESSION['user_id']);
    }

    public function getUserById(int $id): ?array {
        $stmt = $this->messageRepo->getUserById($id);
        return $stmt;
    }
}
