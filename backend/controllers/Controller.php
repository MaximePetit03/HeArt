<?php
abstract class Controller {
    protected function render(string $view, array $data = []): void {
        extract($data); // ['album' => $album] devient $album dans la vue
        require_once BASE_PATH . '/views/layout/header.php';
        require_once BASE_PATH . '/views/' . $view . '.php';
        require_once BASE_PATH . '/views/layout/footer.php';
    }

    protected function redirect(string $url): void {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
}