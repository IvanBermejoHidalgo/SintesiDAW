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
            ob_start();
            $this->messageRepo->createMessageWithOptionalImageAndList();
            $messages = $this->getAllMessagesWithUsers();
            $lastMessage = $messages[0] ?? null;

            if ($lastMessage) {
                global $twig;
                echo $twig->render('components/message_card.html', [
                    'message' => $lastMessage,
                    'current_user_id' => $_SESSION['user_id'],
                    'userData' => SessionController::getUserData($_SESSION['user_id']),
                    'language' => $_SESSION['language'] ?? 'es'
                ]);
            }

            $html = ob_get_clean();
            echo $html;
            exit();
        } elseif (isset($_POST['delete_message'])) {
            $this->messageRepo->deleteMessage($_SESSION['user_id'], (int)$_POST['delete_message']);
        } elseif (isset($_POST['like_message'])) {
            $success = $this->messageRepo->addLike($_SESSION['user_id'], (int)$_POST['like_message']);
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }
        } elseif (isset($_POST['unlike_message'])) {
            $success = $this->messageRepo->removeLike($_SESSION['user_id'], (int)$_POST['unlike_message']);
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }
        } elseif (isset($_POST['comment_content'])) {
            $comment = $this->messageRepo->addComment($_SESSION['user_id'], (int)$_POST['message_id'], $_POST['comment_content']);
            if ($comment) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'comment' => $comment
                ]);
                exit();
            }
        } elseif (isset($_POST['delete_comment'])) {
            $result = $this->messageRepo->deleteComment($_SESSION['user_id'], (int)$_POST['delete_comment']);
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }
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
