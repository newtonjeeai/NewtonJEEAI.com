<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        // Convert {param} to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        $this->routes[] = compact('method', 'path', 'pattern', 'handler');
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string
        $uri = strtok($uri, '?') ?: '/';

        // Method override for forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            // Extract named params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            [$controllerName, $action] = explode('@', $route['handler']);
            $class = 'App\\Controllers\\' . str_replace('/', '\\', $controllerName);

            if (!class_exists($class)) {
                throw new RuntimeException("Controller not found: $class");
            }

            $controller = new $class();

            if (!method_exists($controller, $action)) {
                throw new RuntimeException("Action not found: $class::$action");
            }

            $controller->$action($params);
            return;
        }

        // 404
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo '<h1>404 Not Found</h1>';
        }
    }
}
