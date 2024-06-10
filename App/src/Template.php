<?php
namespace Jubilant;

class Template {
    private $templatePath;
    private $cssPath;
    private $variables = [];

    public function __construct($templatePath = '') {
        $this->templatePath = $templatePath;
    }

    public function css($cssPath = '') {
        $this->cssPath = $cssPath;
    }

    public function var(array $variables) {
        $this->variables = $variables;
        return true;
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

        $content = str_replace('@hw','Hello, World!',$content);
        $content = str_replace('@css', '<?php echo "<style>".file_get_contents($this->cssPath)."</style>" ?>', $content);
        if(file_exists(__DIR__.'/../styles/css/main.css')) {
            $mainCss = file_get_contents(__DIR__.'/../styles/css/main.css');
            $content = str_replace('@mainCss', '<?php echo "<style>".$mainCss."</style>" ?>', $content);
        } else {
            $content = str_replace('@mainCss', 'Nem hívható meg a css fájl, mert nem létezik!', $content);
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

            $variables = isset($matches[2]) ? 'array('.$matches[2].')' : 'array()';

            return '<?php '.$templateVar.' = new \Jubilant\Template("'.__DIR__.'/../public/templates/views/'.$matches[1].'.blade.php");'.$templateVar.'->var('.$variables.'); echo '.$templateVar.'->render(); ?>';
        }, $content);
        return $content;
    }
}