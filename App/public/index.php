<?php
    /* 
    *
    *   @JubilantPHP -- Jarkó Richárd production
    *
    */

    require __DIR__.'/../settings.php';
    use Jubilant\Application;

    /* Set the application */
    $Application = new Application();

    /* Set the paths */
    require __DIR__.'/routes.php';

    /* Run the application */
    $Application->start();