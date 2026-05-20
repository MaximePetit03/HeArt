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
        $albums = $this->albumManager->findAllPublic();
        $albums = $this->albumManager->findAllPublic();
        $tagManager = new TagManager();

        foreach ($albums as $album) {
            $album->setTags($tagManager->findByAlbumId($album->getId()));
        }
        
        $this->render('albums/index', [
            'title'  => 'Explorer - HeArt',
            'albums' => $albums
        ]);
    }

    public function myAlbums(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $albums = $this->albumManager->findByUserId($userId);

        $this->render('user/userAlbums', [
            'title'  => 'Mes Albums - HeArt',
            'albums' => $albums,
            'extraCss' => 'editAlbum'
        ]);
    }

    public function create(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $errors = [];
        $tagManager = new TagManager();
        $allTags = $tagManager->findAll();

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

                if (!empty($_POST['tags'])) {
                    foreach ($_POST['tags'] as $tagId) {
                        $this->albumManager->addTagToAlbum($albumId, (int)$tagId);
                    }
                }

                $this->redirect('/albums/edit?id=' . $albumId);
                return;
            }
        }

        $this->render('albums/create', [
            'title'    => 'Créer un album - HeArt',
            'errors'   => $errors,
            'allTags'  => $allTags,
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
        $tagManager = new TagManager();
        $allTags = $tagManager->findAll();
        $currentTags = $tagManager->findByAlbumId($albumId);
        $currentTagIds = array_map(fn($t) => $t->getId(), $currentTags);
        
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
            'album'    => $album,
            'extraCss' => 'editAlbum',
            'photos'   => $photos,
            'allTags'  => $allTags,
            'currentTagIds' => $currentTagIds
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

    public function updateVisibility(): void {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $albumId = (int)($data['albumId'] ?? 0);
        $visibility = $data['visibility'] ?? 'public';

        if ($albumId > 0 && $this->isLoggedIn()) {
            $this->albumManager->updateVisibility($albumId, $visibility);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Non autorisé ou ID invalide']);
        }
        var_dump($data);
        exit;
    }

    public function update(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        if (!empty($_POST)) {
            $albumId = (int)($_POST['album_id'] ?? 0);
            $visibility = $_POST['visibility'] ?? 'public';

            $this->albumManager->updateVisibility($albumId, $visibility);
            $this->albumManager->removeTagsFromAlbum($albumId);
            
            if (!empty($_POST['tags'])) {
                foreach ($_POST['tags'] as $tagId) {
                    $this->albumManager->addTagToAlbum($albumId, (int)$tagId);
                }
            }

            $this->redirect('/albums/my-albums');
        }
    }

    public function delete(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/albums/my-albums');
            return;
        }

        $albumId = (int)($_POST['album_id'] ?? 0);
        
        if ($albumId > 0 && $this->albumManager->isOwner($albumId, $_SESSION['user_id'])) {
            $this->albumManager->deleteAlbum($albumId);
        }
        
        $this->redirect('/albums/my-albums');
    }
}