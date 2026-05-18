<?php

abstract class AbstractController {
    protected function render(string $view, array $data = []): void {
        extract($data, EXTR_SKIP);
        ob_start();
        
        require BASE_PATH . '/views/' . $view . '.phtml';
        $content = ob_get_clean();
        require BASE_PATH . '/views/layout/header.phtml';
        echo $content;
        require BASE_PATH . '/views/layout/footer.phtml';
    }

    protected function redirect(string $url): void {
        $baseUrl = rtrim(BASE_URL, '/');
        $targetUrl = '/' . ltrim($url, '/');

        header('Location: ' . $baseUrl . $targetUrl);
        exit;
    }

    protected function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}