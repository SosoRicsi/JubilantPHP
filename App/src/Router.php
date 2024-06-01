<?php 
    declare(strict_types=1);
    namespace Jubilant;

    use Jubilant\Template;

    class Router {
        private array $handlers = [];
        private $notFoundHandler;
        private $dinamicErrorHandler;
        private const METHOD_POST = 'POST';
        private const METHOD_GET = 'GET';

        public function get(string $path, ?string $template, $handler): void {
            $this->addHandler(self::METHOD_GET, $path, $template, $handler);
        }

        public function post(string $path, $handler): void {
            $this->addHandler(self::METHOD_POST, $path, null, $handler);
        }

        public function add404Handler($handler): void {
            $this->notFoundHandler = $handler;
        }

        public function addDinamicErrorHandler($handler): void {
            $this->dinamicErrorHandler = $handler;
        }

        private function addHandler(string $method, string $path, ?string $template, $handler): void {
            $this->handlers[] = [
                'path' => $path,
                'method' => $method,
                'template' => $template,
                'handler' => $handler
            ];
        }

        private function match(string $requestPath, string $path, array &$params): bool {
            $pathParts = explode('/', trim($path, '/'));
            $requestParts = explode('/', trim($requestPath, '/'));

            if (strpos($path, '{') === false && count($pathParts) !== count($requestParts)) {
                $this->dinamicErrorHandler = 'disabled';
                return false;
            } else {

            }

            foreach ($pathParts as $index => $part) {
                if (!isset($requestParts[$index])) {
                    return false;
                }

                if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                    $paramName = substr($part, 1, -1);
                    $params[$paramName] = $requestParts[$index];
                } elseif ($part !== $requestParts[$index]) {
                    return false;
                }
            }
            return true;
        }

        public function run() {
            $requestUri = parse_url($_SERVER['REQUEST_URI']);
            $requestPath = $requestUri['path'];
            $method = $_SERVER['REQUEST_METHOD'];

            $callback = null;
            $params = [];
            $template = null;

            foreach ($this->handlers as $handler) {
                if ($handler['method'] === $method && $this->match($requestPath, $handler['path'], $params)) {
                    $callback = $handler['handler'];
                    $template = $handler['template'];
                    break;
                }
            }

            if (!$callback) {
                if ($this->dinamicErrorHandler != 'disabled' && $this->notFoundHandler == 'disabled') {
                    header("HTTP/1.0 400 Error in dinamic url");
                    call_user_func($this->dinamicErrorHandler);
                } else if ($this->dinamicErrorHandler == 'disabled' && $this->notFoundHandler != 'disabled') {
                    header("HTTP/1.0 404 Not Found");
                    call_user_func($this->notFoundHandler);
                } else {
                    header("HTTP/1.0 404 Not Found");
                    call_user_func($this->notFoundHandler);
                }
                return;
            }

            if ($template) {
                $Template = new Template($template);
                call_user_func_array($callback, [$params, $Template]);
                echo $Template->render();
            } else {
                call_user_func_array($callback, [$params]);
            }
        }
    }
?>