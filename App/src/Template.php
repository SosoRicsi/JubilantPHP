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
            $if = $content = preg_replace('/@if\(\s*(.+?)\s*\)/', '<?php if($1): ?>', $content);
            $if = $content = preg_replace('/@if \(\s*(.+?)\s*\)/', '<?php if($1): ?>', $content);
            if($if != '') {
                $content = preg_replace('/@elseif\(\s*(.+?)\s*\)/', '<?php elseif($1): ?>', $content);
                $content = preg_replace('/@elseif \(\s*(.+?)\s*\)/', '<?php elseif($1): ?>', $content);
                $content = str_replace('@else', '<?php else: ?>', $content);
                $content = str_replace('@endif', '<?php endif; ?>', $content);
            }

            ob_start();
            eval('?>'.$content);
            $final = ob_get_clean();

            return $final;
        }
    }
?>