<?php

namespace App;

class Router
{
    private array $routes = [];
    private string $basePath = '/zona_gris/public'; // puedes cambiarlo desde fuera si es necesario

    public function add(string $method, string $pattern, callable $callback): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => "#^" . $pattern . "$#",
            'callback' => $callback
        ];
    }

    public function dispatch(?string $uri = null, ?string $method = null): void
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        $method = $method ?? $_SERVER['REQUEST_METHOD'];

        // Eliminar query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Quitar base path si aplica
        if (!empty($this->basePath) && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
            if ($uri === '') $uri = '/';
        }

        error_log("➡ PATH LIMPIO: $uri | METHOD: $method");

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $params)) {
                array_shift($params);
                call_user_func_array($route['callback'], $params);
                return;
            }
        }

        // No encontrada
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Ruta no encontrada']);
    }

    public function setBasePath(string $path): void
    {
        $this->basePath = rtrim($path, '/');
    }
}
