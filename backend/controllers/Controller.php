<?php
abstract class Controller {
    protected function render(string $view, array $data = []): void {
        extract($data);
        require BASE_PATH . '/views/layout/header.phtml';
        require BASE_PATH . '/views/' . $view . '.phtml';
        require BASE_PATH . '/views/layout/footer.phtml';
    }

    protected function redirect(string $url): void {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    protected function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }
}