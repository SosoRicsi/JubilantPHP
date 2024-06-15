<?php 

    namespace Jubilant\commands;
    use Jubilant\Superglobals\Dotenv;

    class RunDevServer {

        public function run($port = null) {
            $port = $port ??  Dotenv::get('DEV_SERVER_PORT');
            $command = sprintf("php -S localhost:$port -t App/public/");
            echo "Starting developer server at http://localhost:{$port}\n";
            shell_exec($command);
        }

    }