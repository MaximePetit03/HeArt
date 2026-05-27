<?php

class CommentController extends AbstractController {
    private CommentManager $commentManager;

    public function __construct() {
        require_once BASE_PATH . '/managers/CommentManager.php';
        $this->commentManager = new CommentManager();
    }

    public function add(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'], $_POST['photo_id'])) {
            $content = trim($_POST['content']);
            $photoId = (int)$_POST['photo_id'];
            $userId  = (int)$_SESSION['user_id'];

            if (!empty($content)) {
                $this->commentManager->add($photoId, $userId, $content);
            }

            $this->redirect('/photos/show?id=' . $photoId);
        } else {
            $this->redirect('/');
        }
    }

    public function edit(): void {
        header('Content-Type: application/json');
        
        if (!$this->isLoggedIn()) return;
        
        $data = json_decode(file_get_contents('php://input'), true);
        $commentId = (int)($data['comment_id'] ?? 0);
        $newContent = trim($data['content'] ?? '');

        $comment = $this->commentManager->findById($commentId);

        if ($comment && (int)$_SESSION['user_id'] === $comment->getUserId() && !empty($newContent)) {
            $this->commentManager->update($commentId, $newContent);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function delete(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
            return;
        }

        $commentId = (int)($_POST['comment_id'] ?? 0);
        
        $comment = $this->commentManager->findById($commentId);

        if ($comment && (int)$_SESSION['user_id'] === $comment->getUserId()) {
            $this->commentManager->delete($commentId);
        }
        
        $this->redirect('/photos/show?id=' . $comment->getPhotoId());
    }
}