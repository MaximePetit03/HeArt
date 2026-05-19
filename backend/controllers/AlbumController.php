<?php

class AlbumController extends AbstractController {
    private AlbumManager $albumManager;
    private PhotoManager $photoManager;

    public function __construct() {
        require BASE_PATH . '/managers/AlbumManager.php';
        require BASE_PATH . '/managers/PhotoManager.php';
        
        $this->albumManager = new AlbumManager();
        $this->photoManager = new PhotoManager();
    }

    public function index(): void {
        try {
            if (!$this->isLoggedIn()) {
                $this->redirect('/login');
                return;
            }

            $albums = $this->albumManager->findAll() ?? [];
            
            $this->render('albums/index', [
                'title'  => 'Albums - HeArt',
                'albums' => $albums,
            ]);
        } catch (Exception $e) {
            die("ERREUR DANS L'INDEX : " . $e->getMessage());
        }
    }

    public function create(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $errors = [];

        if (!empty($_POST)) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $visibility = trim($_POST['visibility'] ?? 'public');
            $allowedVisibility = ['public', 'private', 'restricted'];
            
            if (!in_array($visibility, $allowedVisibility)) {
                $visibility = 'public';
            }
            
            if (empty($title)) {
                $errors[] = "Le titre de l'album est obligatoire.";
            } elseif (mb_strlen($title) > 50) { 
                $errors[] = "Le titre is trop long.";
            }

            if (mb_strlen($description) > 500) {
                $errors[] = "La description est trop longue.";
            }

            if (empty($errors)) {
                $userId = (int)$_SESSION['user_id'];

                $albumId = $this->albumManager->create($title, $description, $visibility, $userId);

                $this->redirect('/albums/edit?id=' . $albumId);
                return;
            }
        }

        $this->render('albums/create', [
            'title'    => 'Créer un album - HeArt',
            'errors'   => $errors,
            'extraCss' => 'createAlbum'
        ]);
    }

    public function edit(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $albumId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $album = $this->albumManager->findById($albumId);

        if (!$album) {
            $this->redirect('/');
            return;
        }

        if ($album->getUserId() !== (int)$_SESSION['user_id']) {
            $this->redirect('/');
            return;
        }

        $photos = $this->photoManager->findByAlbumId($albumId);

        $this->render('albums/edit', [
            'title'    => 'Modifier l\'album - HeArt',
            'extraCss' => 'editAlbum',
            'album'    => $album,
            'photos'   => $photos
        ]);
    }

    public function uploadPhotos(): void {
        header('Content-Type: application/json');

        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
                return;
            }

            $albumId = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
            
            if ($albumId <= 0 || !isset($_FILES['photos'])) {
                echo json_encode(['success' => false, 'error' => 'Données manquantes ou invalides.']);
                return;
            }

            $uploadDir = BASE_PATH . '/public/uploads/albums/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadedPhotos = [];
            $files = $_FILES['photos'];

            for ($i = 0; $i < count($files['name']); $i++) {
                $fileTmpPath = $files['tmp_name'][$i];
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }

                $maxFileSize = 2 * 1024 * 1024;
                if ($files['size'][$i] > $maxFileSize) {
                    continue;
                }

                $fileTmpPath = $files['tmp_name'][$i];
                
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($fileTmpPath);
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedTypes)) {
                    continue;
                }

                $extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($extension, $allowedExtensions)) {
                    continue;
                }

                $newFileName = uniqid('photo_', true) . '.' . $extension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $userId = (int)$_SESSION['user_id'];

                    $photoId = $this->photoManager->create($newFileName, $albumId, $userId);

                    $photoUrl = '/uploads/albums/' . $newFileName;

                    $uploadedPhotos[] = [
                        'id'  => $photoId,
                        'url' => $photoUrl
                    ];
                }
            }

            if (!empty($uploadedPhotos)) {
                echo json_encode([
                    'success' => true,
                    'photos'  => $uploadedPhotos
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error'   => 'Aucune photo n\'a pu être déplacée. Fichier trop lourd (max 2Mo).'
                ]);
            }

        } catch (Throwable $e) {
            echo json_encode([
                'success' => false,
                'error'   => 'Crash PHP : ' . $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine()
            ]);
        }
        exit;
    }
}