<?php

class AuthMiddleware {
    private static array $public = [
        '/',
        '/login',
        '/register',
    ];

    public static function handle(string $uri, string $method):void {
        if (in_array($uri, self::$public)) {
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = $uri;

            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }
}