<?php 

    /* 
    *
    *   @JubilantPHP -- Jarkó Richárd production
    *
    */

    use Jubilant\Router;
    use Jubilant\Template;

    use Jubilant\Superglobals\Session;

    use App\Controllers\IndexController;
    use App\Controllers\LoginController;
    use App\Controllers\RegisterController;

    $Router = new Router();

    $Router->get('/', [IndexController::class, 'index'], function () {
        $loggedin = Session::get('loggedin');
        if (!isset($loggedin)) {
            header('HTTP/1.1 401 Unauthorized');
            http_response_code(401);
            header('Location: /login');
            exit;
        }
    });
    $Router->get('/login', [LoginController::class, 'index']);
    $Router->get('/register', [RegisterController::class, 'index']);

    $Router->add404Handler(function () {
        $Template = new Template(__DIR__.'/MVC/views/404.blade.php');
        $Template->var([
            'error'=>"404"
        ]);
        echo $Template->render();
    });
