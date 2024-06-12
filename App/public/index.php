<?php 
    require_once __DIR__.'/../settings.php';

    use Jubilant\Router;
    use Jubilant\Template;
    use Jubilant\UserAuth;
    use Jubilant\FileUpload;
    use Jubilant\RandomString;
    use Jubilant\Superglobals\Session;
    use Jubilant\Superglobals\Dotenv;

    $Router = new Router();
    $Template = new Template();

    $Router->set('/', 'index', 'index');
    $Router->set('/login', 'home', 'inde');

    $Router->get('/', function ($params, $Template) use ($Appname, $language) {
        $Template->var([
            'title'=>"Főoldal",
            'appName'=>$Appname,
            'lang'=>$language
        ]);
    });

    $Router->add404Handler(function () use ($language) {
        $Template = new Template(__DIR__.'/templates/views/404.blade.php');
        $Template->var([
            'error'=>"404",
            'lang'=>$language
        ]);
        echo $Template->render();
    });

    $Router->run();