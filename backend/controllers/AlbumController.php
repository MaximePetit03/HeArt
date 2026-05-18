<?php

class AlbumController extends AbstractController {
    private AlbumManager $albumManager;

    public function __construct() {
        require_once BASE_PATH . '/managers/AlbumManager.php';
        $this->albumManager = new AlbumManager();
    }

    public function index(): void {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }

        $albums = $this->albumManager->findAll();

        $this->render('albums/index', [
            'title'  => 'Albums - HeArt',
            'albums' => $albums,
        ]);
    }
}