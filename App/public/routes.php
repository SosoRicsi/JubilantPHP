<?php 

    /* 
    *
    *   @JubilantPHP -- Jarkó Richárd production
    *
    */

    use Jubilant\Router;
    use Jubilant\Template;

    use App\Controllers\IndexController;

    $Router = new Router();

    $Router->get('/', [IndexController::class, 'index']);

    $Router->add404Handler(function () {
        $Template = new Template(__DIR__.'/MVC/views/404.blade.php');
        $Template->var([
            'error'=>"404"
        ]);
        echo $Template->render();
    });
