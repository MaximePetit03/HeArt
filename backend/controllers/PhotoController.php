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
}