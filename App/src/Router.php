<?php
    declare(strict_types=1);

    namespace Jubilant;

    class Router {
        private array $routes = [];
        private $notFoundHandler;
        private string $currentGroupPrefix = '';
        private array $currentGroupMiddleware = [];
        private const METHOD_GET = 'GET';
        private const METHOD_POST = 'POST';
        private const METHOD_PUT = 'PUT';
        private const METHOD_DELETE = 'DELETE';

        public function put(string $path, mixed $handler, ?array $middleware = null): void {
            $this->addRoute(self::METHOD_PUT, $path, $handler, $middleware ?? []);
        }

        public function delete(string $path, mixed $handler, ?array $middleware = null): void {
            $this->addRoute(self::METHOD_DELETE, $path, $handler, $middleware ?? []);
        }

        public function get(string $path, mixed $handler, ?array $middleware = null): void {
            if($middleware === null) {
                $this->addRoute(self::METHOD_GET, $path, $handler);
            } else {
                $this->addRoute(self::METHOD_GET, $path, $handler, $middleware);
            }
        }

        public function redirect(string $path, string $redirectTo): void {
            $handler = function() use ($redirectTo) {
                header("Location: " . $redirectTo);
                exit();
            };
            $this->addRoute(self::METHOD_GET, $path, $handler);
        }

        public function post(string $path, mixed $handler, ?array $middleware = null): void {
            if($middleware === null) {
                $this->addRoute(self::METHOD_POST, $path, $handler);
            } else {
                $this->addRoute(self::METHOD_POST, $path, $handler, $middleware);
            }
        }

        public function add404Handler($handler): void {
            $this->notFoundHandler = $handler;
        }

        public function group(string $prefix, array $middleware, callable $callback): void {
            $parentGroupPrefix = $this->currentGroupPrefix;
            $parentGroupMiddleware = $this->currentGroupMiddleware;

            $this->currentGroupPrefix = $parentGroupPrefix . $prefix;
            $this->currentGroupMiddleware = array_merge($parentGroupMiddleware, $middleware);

            $callback($this);

            $this->currentGroupPrefix = $parentGroupPrefix;
            $this->currentGroupMiddleware = $parentGroupMiddleware;
        }

        private function addRoute(string $method, string $path, mixed $handler, array $middleware = []): void {
            $path = isset($this->currentGroupPrefix) ? $this->currentGroupPrefix . $path : $path;
            $middleware = isset($this->currentGroupMiddleware) ? array_merge($this->currentGroupMiddleware, $middleware) : $middleware;
            
            $this->routes[] = [
                'method' => $method,
                'path' => $path,
                'handler' => $handler,
                'middleware' => $middleware
            ];
        }        

        private function match(string $requestPath, string $path, array &$params): bool {
            $pathParts = explode('/', trim($path, '/'));
            $requestParts = explode('/', trim($requestPath, '/'));
        
            if (count($pathParts) !== count($requestParts)) {
                return false;
            }
        
            foreach ($pathParts as $index => $part) {
                if (preg_match('/\{(\w+):(.+)\}/', $part, $matches)) {
                    $paramName = $matches[1];
                    $pattern = $matches[2];
                    if (!preg_match('/^' . $pattern . '$/', $requestParts[$index])) {
                        return false;
                    }
                    $params[$paramName] = $requestParts[$index];
                } elseif ($part !== $requestParts[$index]) {
                    return false;
                }
            }
            return true;
        }
        

        public function run(string $uri = null, string $method = null) {
            $requestUri = parse_url($uri ?? $_SERVER['REQUEST_URI']);
            $requestPath = $requestUri['path'];
            $method = $method ?? $_SERVER['REQUEST_METHOD'];

            $callback = null;
            $params = [];

            foreach ($this->routes as $route) {
                if ($route['method'] === $method && $this->match($requestPath, $route['path'], $params)) {
                    foreach ($route['middleware'] as $middleware) {
                        $middlewareInstance = new $middleware;
                        if(!$middlewareInstance->handle($route['path'], $route['method'])) {
                            return $this->notFoundHandler;
                        }
                    }
                    $callback = $route['handler'];
                    break;
                }
            }

            if (!$callback) {
                header("HTTP/1.0 404 Not Found");
                if ($this->notFoundHandler) {
                    call_user_func($this->notFoundHandler);
                } else {
                    echo '404 Not Found';
                }
                return;
            }

            if (is_array($callback)) {
                [$controller, $method] = $callback;
                if (class_exists($controller)) {
                    $controller = new $controller();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    }
                }
            } else {
                call_user_func_array($callback, $params);
            }
        }
    }
