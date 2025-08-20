<?php

namespace App;

class Router
{
    private $routes = [];

    public function add($method, $pattern, $callback)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => "#^" . $pattern . "$#",
            'callback' => $callback
        ];
    }

    public function dispatch($uri = null, $method = null)
    {
        $uri = $uri ?? $_SERVER['REQUEST_URI'];
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $method = $method ?? $_SERVER['REQUEST_METHOD'];

        // Elimina el prefijo del path del proyecto (ajusta según tu estructura)
        $basePath = '/zona_gris/public';
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
            if ($uri === '') $uri = '/';
        }

        error_log("PATH LIMPIO: $uri METHOD: $method"); // Para depurar

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $params)) {
                array_shift($params);
                return call_user_func_array($route['callback'], $params);
            }
        }
        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}
