<?php 

    namespace App\Controllers;

    use Jubilant\Template;
    use App\Models\Login;

    class LoginController {

        private $model;

        public function __construct() {
            $this->model = new Login();
        }

        public function index() {
            $Template = new Template(__DIR__.'/../views/login.blade.php');
            $Template->var([
                'title' => "login view"
            ]);
            echo $Template->render();
        }

    }