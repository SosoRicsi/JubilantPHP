<?php 
    namespace Jubilant;

    class Template {
        private $templatePath;
        private $variables = [];
        private $directory = [];

        public function __construct($templatePath = '') {
            $this->templatePath = $templatePath;
        }

        public function setVariables(array $variables) {
            $this->variables = $variables;
        }

        public function setDirectory(string $route, string $directory) {
            $this->directory[$route] = $directory;
        }

        public function getDirectory(string $route) {
            return $this->directory[$route] ?? null;
        }

        public function render() {
            $content = file_get_contents($this->templatePath);

            foreach ($this->variables as $key => $value) {
                $content = str_replace("{{".$key."}}", htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $content);
            }

            return $content;
        }
    }
?>