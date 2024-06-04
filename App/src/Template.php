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
        $this->directory[$route] = __DIR__.'/../public/templates/views/'.$directory;
    }

    public function getDirectory(string $route) {
        return $this->directory[$route] ?? null;
    }

    public function render() {
        $content = file_get_contents($this->templatePath);

        foreach ($this->variables as $key => $value) {
            $content = str_replace("{{".$key."}}", htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $content);
        }

        $content = $this->processTemplates($content);

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

    private function processTemplates($content) {
        $templateCounter = 0;

        $content = preg_replace_callback('/@template\(\s*[\'"](.+?)[\'"]\s*(?:,\s*array\((.*?)\)\s*)?\)/', function($matches) use (&$templateCounter) {
            $templateCounter++;
            $templateVar = '$Template' . $templateCounter;

            $variables = isset($matches[2]) ? 'array(' . $matches[2] . ')' : 'array()';

            return '<?php ' . $templateVar . ' = new \Jubilant\Template("' . __DIR__ . '/../public/templates/views/' . $matches[1] . '"); ' . $templateVar . '->setVariables(' . $variables . '); echo ' . $templateVar . '->render(); ?>';
        }, $content);
        return $content;
    }
}
