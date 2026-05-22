<?php
class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void {
        $this->routes[] = [
            'method'     => 'GET',
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function post(string $path, string $controller, string $action): void {
        $this->routes[] = [
            'method'     => 'POST',
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function dispatch(): void {
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                $ctrl = new $route['controller']();
                $ctrl->{$route['action']}();
                return;
            }
        }

        http_response_code(404);
        echo '<h1>404 - Page introuvable</h1>';
        echo'<h1><a href="/"> Retour à l\'accueil</h1>';
    }
}