<?php
namespace app;

class Router {
    private array $routes = [];
    private Database $db;
    private Cache $cache;

    public function __construct(Database $db, Cache $cache) {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function get(string $path, string $controller, string $method, ?string $middleware = null): void {
        $this->addRoute('GET', $path, $controller, $method, $middleware);
    }

    public function post(string $path, string $controller, string $method, ?string $middleware = null): void {
        $this->addRoute('POST', $path, $controller, $method, $middleware);
    }

    private function addRoute(string $httpMethod, string $path, string $controller, string $method, ?string $middleware): void {
        $this->routes[] = [
            'method' => $httpMethod,
            'path' => $path,
            'controller' => $controller,
            'action' => $method,
            'middleware' => $middleware
        ];
    }

    public function dispatch(string $requestMethod, string $uri): void {
        // Clean URI
        $uri = strtok($uri, '?');

        // Remove base path from URI (for subdirectory installations)
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = rtrim($uri, '/');
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) continue;

            // Convert route pattern to regex
            $pattern = preg_replace('/\{([a-z]+)\}/', '([^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match

                // Middleware check
                if ($route['middleware'] === 'auth') {
                    if (!Auth::check()) {
                        header('Location: ' . BASE_URL . '/login');
                        exit;
                    }
                }

                if ($route['middleware'] === 'admin') {
                    if (!Auth::isAdmin()) {
                        header('Location: ' . BASE_URL . '/');
                        exit;
                    }
                }

                // Instantiate controller
                $controllerClass = "app\\Controllers\\{$route['controller']}";
                $controller = new $controllerClass($this->db, $this->cache);

                // Call method
                call_user_func_array([$controller, $route['action']], $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        require __DIR__ . '/Views/errors/404.php';
    }
}
