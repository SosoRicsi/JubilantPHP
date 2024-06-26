<?php 

    /* 
    *
    *   @JubilantPHP -- Jarkó Richárd production
    *
    */

    use Jubilant\Template;

    use App\Controllers\IndexController;
    use App\Middlewares\Auth;

    //with auth middleware:
    //$Application->router->get('/', [IndexController::class, 'index'], [Auth::class]);
    $Application->router->get('/', [IndexController::class, 'index']);

    $Application->router->add404Handler(function () {
        $Template = new Template(__DIR__.'/MVC/views/404.blade.php');
        $Template->var([
            'error'=>"404"
        ]);
        echo $Template->render();
    });
