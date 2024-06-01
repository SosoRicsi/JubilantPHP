<?php 
    require __DIR__.'../../../vendor/autoload.php';

    use Jubilant\Router;
    use Jubilant\Template;

    $Router = new Router();
    $Template = new Template();

    $Template->setDirectory('/',__DIR__.'/views/index.view.php');

    $Router->get('/',$Template->getDirectory('/'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Főoldal"
        ]);
    });

    $Router->run();
?>