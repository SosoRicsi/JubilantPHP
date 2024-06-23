<?php 

    namespace App\Controllers;

    use Jubilant\Template;
    use App\Models\Register;

    class RegisterController {

        private $model;

        public function __construct() {
            $this->model = new Register();
        }

        public function index() {
            $Template = new Template(__DIR__.'/../views/register.blade.php');
            $Template->var([
                'title' => 'sb'
            ]);
            echo $Template->render();
        }

    }