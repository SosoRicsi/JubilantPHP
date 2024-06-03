<?php 
    require __DIR__.'../../../vendor/autoload.php';

    use Jubilant\Router;
    use Jubilant\Template;
    use Jubilant\UserAuth;
    use Jubilant\Superglobals\Session;

    $Router = new Router();
    $Template = new Template();
    $Session = new Session();

    $Template->setDirectory('/',__DIR__.'/templates/views/index.blade.php');
    $Template->setDirectory('/register',__DIR__.'/templates/views/register.blade.php');
    $Template->setDirectory('/login', __DIR__.'/templates/views/login.blade.php');
    $Template->setDirectory('/dashboard/account', __DIR__.'/templates/views/dashboard/account.blade.php');

    $Router->get('/',$Template->getDirectory('/'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Főoldal"
        ]);
    });

    $Router->get('/register', $Template->getDirectory('/register'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Regisztráció",
            'session_id'=>$_COOKIE['PHPSESSID']
        ]);
    });
    $Router->post('/execute/register', function () {
        $UserAuth = new UserAuth("host","username","password","database_name");

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $sessionid = $_POST['session_id'];

        $EmailServer = ['smtp.example.com','sb@example.com','email password','port(587)','sender email'];
        $UserAuth->registerUser($username, $email, $password, $sessionid, $EmailServer,'/');
    });
    $Router->get('/execute/register/authenticate/{customid}', null, function ($params) {
        $UserAuth = new UserAuth("host","username","password","database_name");
        $userID = $params['customid'];

        if($UserAuth->confAuth($userID)) {
            echo "<script>window.location.href='/login'</script>";
        }
    });

    $Router->get('/login', $Template->getDirectory('/login'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Bejelentkezés",
            'session_id'=>$_COOKIE['PHPSESSID']
        ]);
    });
    $Router->post('/execute/login', function () {
        $UserAuth = new UserAuth("host","username","password","database_name");

        $email = $_POST['email'];
        $password = $_POST['password'];
        $session_id = $_POST['session_id'];

        $EmailServer = ['smtp.example.com','sb@example.com','email password','port(587)','sender email'];

        if($UserAuth->loginUser($email, $password, $session_id,$EmailServer)) {
            echo "<script>window.location.href='/dashboard/account'</script>";
        }
    });

    $Router->get('/dashboard/account', $Template->getDirectory('/dashboard/account'), function ($params, $Template) {
        $Session = new Session();
        $Template->setVariables([
            'username'=>$Session->get('username')
        ]);
    });

    $Router->add404Handler(function () {
        $Template = new Template(__DIR__.'/templates/views/404.view.html');
        $Template->setVariables([
            'error'=>"404"
        ]);
        echo $Template->render();
    });

    $Router->run();
?>
