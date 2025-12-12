<?php

namespace App\Core;

use App\Helpers\JsonResponse;

class Router
{
    protected array $routes = [];
    public Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }


    public function get($path, $className, $method): void
    {
        $this->routes[$path] = [
            'get' => [
                'className' => $className,
                'method' => $method,
            ],
        ];
    }

    public function post($path, $className, $method): void
    {
        $this->routes[$path] = [
            'post' => [
                'className' => $className,
                'method' => $method,
            ],
        ];
    }

    public function resolve(): mixed
    {
        $path = $this->request->getPath();
        $httpMethod = $this->request->getMethod();

        if (
            array_key_exists($path, $this->routes) === false
            ||
            array_key_exists($httpMethod, $this->routes[$path]) === false
        ) {
            return JsonResponse::error('URL не найден', 404);
        }

        $object = new $this->routes[$path][$httpMethod]['className'];
        $objectMethod = $this->routes[$path][$httpMethod]['method'];

        return call_user_func([$object, $objectMethod]);
    }
}
