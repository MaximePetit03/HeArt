<?php
class PhotoController extends AbstractController {
    private PhotoManager $photoManager;

    public function __construct() {
        $this->photoManager = new PhotoManager();
    }

    public function toggleVisibility(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $photoId = (int)($data['photoId'] ?? 0);

        if ($photoId > 0 && $this->isLoggedIn()) {
            $success = $this->photoManager->toggleVisibility($photoId);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function delete(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $photoId = (int)($data['photoId'] ?? 0);

        if ($this->photoManager->isOwnerOfPhoto($photoId, $_SESSION['user_id'])) {
            $this->photoManager->delete($photoId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    public function show(?int $id = null): void {

        if ($id === null) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }

        if ($id === 0) {
            $this->redirect('/');
            return;
        }

        $photo = $this->photoManager->findById($id);

        if (!$photo) {
            $this->redirect('/');
            return;
        }

        $comments = [];

        // When comments is defined
        // $commentManager = new CommentManager();
        // $comments = $commentManager->findByPhotoId($id);

        $this->render('photos/photoDetails', [
            'title'    => 'Détail',
            'photo'    => $photo,
            'comments' => $comments,
            'extraCss' => 'detailPhoto'
        ]);
    }

    public function toggleTag(): void {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['photoId'], $data['tagId'])) {
            echo json_encode(['success' => false, 'message' => 'Données manquantes']);
            return;
        }

        if (!$this->photoManager->isOwnerOfPhoto($data['photoId'], $_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            return;
        }

        $success = $this->photoManager->toggleTag((int)$data['photoId'], (int)$data['tagId']);
        
        echo json_encode(['success' => $success]);
        exit;
    }
}