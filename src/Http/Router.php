<?php

declare(strict_types=1);

namespace App\Http;

use Closure;

class Router
{
    private array $routes = [];

    public function get(string $path, Closure $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    public function post(string $path, Closure $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    public function patch(string $path, Closure $callback): void
    {
        $this->addRoute('PATCH', $path, $callback);
    }

    private function addRoute(
        string $method,
        string $path,
        Closure $callback
    ): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }

    public function dispatch(Request $request): void
    {
        $requestMethod = $request->method();
        $requestPath = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            $pattern = preg_replace(
                '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
                '(?P<$1>[^/]+)',
                $route['path']
            );

            $pattern = '#^' . rtrim($pattern, '/') . '/?$#';

            if (preg_match($pattern, $requestPath, $matches)) {
                $params = [];

                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                $callback = $route['callback'];

                $callback($request, $params);

                return;
            }
        }

        Response::json([
            'success' => false,
            'message' => 'Route not found'
        ], 404);
    }
}