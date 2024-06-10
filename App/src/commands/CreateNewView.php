<?php 

    namespace Jubilant\commands;

    use Jubilant\Superglobals\Dotenv;

    
    class CreateNewView {
        
        public function run($filename) {

            echo "Creating view...\n";

            $css = __DIR__.'/../../styles/'.$filename.'.style.less';
            if(file_exists($css)) {
                echo "The less file alredy exists.\n";
            } else {
                $file = __DIR__.'/../../public/templates/views/'.$filename.'.blade.php';
                if(file_exists($file)) {
                    echo "View alredy exists.\n";
                    return;
                }
                $handle = fopen($file, 'w');
                $csshandle = fopen($css, 'w');
                if($handle && $csshandle) {
                    echo "View '$filename' created successfully.\n";
                    fclose($handle);
                } else {
                    echo "Failed to create view '$filename'.\n";
                }
            }

        }

    }