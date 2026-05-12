<?php
class AlbumController extends Controller {
    private Album $album;

    public function __construct() {
        require_once BASE_PATH . '/models/Album.php';
        $this->album = new Album();
    }

    public function index(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        $albums = $this->album->findAll();

        $this->render('albums/index', [
            'title'  => 'Albums - HeArt',
            'albums' => $albums,
        ]);
    }
}