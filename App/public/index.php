<?php 
    require __DIR__.'../../../vendor/autoload.php';

    use Jubilant\Router;
    use Jubilant\Template;
    use Jubilant\UserAuth;
    use Jubilant\FileUpload;
    use Jubilant\RandomString;
    use Jubilant\Superglobals\Session;

    $Router = new Router();
    $Template = new Template();
    $Session = new Session();

    $Template->setDirectory('/','index.blade.php');
    $Template->setDirectory('/register', 'register.blade.php');
    $Template->setDirectory('/login', 'login.blade.php');
    $Template->setDirectory('/dashboard/account', 'dashboard/account.blade.php');

    $Router->get('/', $Template->getDirectory('/'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Főoldal"
        ]);
    });

    $Router->post('/uploadImage', function () {
        echo "<pre>";
        $file = $_FILES["fileToUpload"];
        print_r($file);
        print_r($_FILES);
        $UploadFiles = new FileUpload(__DIR__.'/images/');
        $RandomString = new RandomString();
        $fileID = $RandomString->generateCustomString('file',2,3);
        $UploadFiles->upload($file,true,true,$fileID);
    });

    $Router->get('/register', $Template->getDirectory('/register'), function ($params, $Template) {
        $Template->setVariables([
            'title'=>"Regisztráció",
            'session_id'=>$_COOKIE['PHPSESSID']
        ]);
    });
    $Router->post('/execute/register', function () {
        $UserAuth = new UserAuth();

        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $sessionid = $_POST['session_id'];

        $UserAuth->registerUser($username, $email, $password, $sessionid);
    });
    $Router->get('/execute/register/authenticate/{customid}', null, function ($params) {
        $UserAuth = new UserAuth();
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
        $UserAuth = new UserAuth();

        $email = $_POST['email'];
        $password = $_POST['password'];
        $session_id = $_POST['session_id'];

        if($UserAuth->loginUser($email, $password, $session_id)) {
            echo "<script>window.location.href='/dashboard/account'</script>";
        }
    });

    $Router->get('/dashboard/account', $Template->getDirectory('/dashboard/account'), function ($params, $Template) {
        $Session = new Session();
        $Template->setVariables([
            'username'=>$Session->get('username')
        ]);
    });
    $Router->get('/dashboard/account/logout', null, function() {
        $Session = new Session();
        $Session->destroy();
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
