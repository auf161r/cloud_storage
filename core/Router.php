<?php

namespace Core;

class Router
{
    private array $routes = [];

    /**
     * Добавить маршрут
     * 
     * @param string $method HTTP-метод (GET, POST, PUT, DELETE)
     * @param string $uri URI маршрута (может содержать параметры {id})
     * @param string $controller Название контроллера
     * @param string $action Название метода контроллера
     * @return self
     */
    public function add(string $method, string $uri, string $controller, string $action): self
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'controller' => $controller,
            'action' => $action
        ];

        return $this;
    }

    /**
     * Обработать входящий запрос и вызвать соответствующий контроллер
     * 
     * @param Request $request Объект запроса
     * @return Response Объект ответа
     */
    public function dispatch(Request $request): Response
    {

        $uri = $request->getUri();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);

                $controllerName = 'app\\Controllers\\' . $route['controller'];
                $action = $route['action'];

                if (class_exists($controllerName)) {
                    $controller = new $controllerName();
                    $controller->setRequest($request);
                    if (method_exists($controller, $action)) {
                        return $controller->$action(...$matches);
                    }
                }
            }
        }

        $response = new Response();
        return $response->setStatusCode(404)->json(['error' => 'Not Found']);
    }
}
