<?php
    declare(strict_types=1);
    namespace Jubilant;

    use Jubilant\Template;

    class Router {
        private array $handlers = [];
        private array $directory = [];
        private array $styles = [];
        private $notFoundHandler;
        private $dinamicErrorHandler;
        private const METHOD_POST = 'POST';
        private const METHOD_GET = 'GET';

        public function set($route, $template, $css) {
            if($template === null) {
                $this->directory[$route] = null;
            } else {
                $this->directory[$route] = __DIR__.'/../public/templates/views/'.$template.'.blade.php';
                $this->styles[$route] = __DIR__.'/../styles/css/'.$css.'.style.css';
            }
        }

        private function getDirectory($route) {
            return $this->directory[$route] ?? null;
        }

        public function get(string $path, $handler, ?bool $css = true): void {
            $template = $this->getDirectory($path);
            if($css != false) {
                $css = $this->styles[$path];
            }
            $css == true ? $css = $this->styles[$path] : $css = null;
            $this->addHandler(self::METHOD_GET, $path, $template, $handler, $css);
        }

        public function post(string $path, $handler): void {
            $this->addHandler(self::METHOD_POST, $path, null, $handler);
        }

        public function add404Handler($handler): void {
            $this->notFoundHandler = $handler;
        }

        private function addHandler(string $method, string $path, ?string $template, $handler, $css = ''): void {
            $this->handlers[] = [
                'path' => $path,
                'method' => $method,
                'template' => $template,
                'handler' => $handler,
                'css' => $css
            ];
        }

        private function match(string $requestPath, string $path, array &$params): bool {
            $pathParts = explode('/', trim($path, '/'));
            $requestParts = explode('/', trim($requestPath, '/'));

            if (strpos($path, '{') === false && count($pathParts) !== count($requestParts)) {
                $this->dinamicErrorHandler = 'disabled';
                return false;
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
                    $css = $handler['css'];
                    break;
                }
            }

            if (!$callback) {
                header("HTTP/1.0 404 Not Found");
                call_user_func($this->notFoundHandler);
                return;
            }

            if ($template && $handler['template'] != null) {
                $Template = new Template($template);
                $css != null ? $Template->css($css) : null;
                call_user_func_array($callback, [$params, $Template]);
                echo $Template->render();
            } else {
                call_user_func_array($callback, [$params]);
            }
        }
    }
?>