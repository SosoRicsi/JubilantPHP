<?php 

    namespace Jubilant\commands;
    use Jubilant\Superglobals\Dotenv;

    class RunDevServer {

        public function run($port = null) {
            $port = $port ??  Dotenv::get('DEV_SERVER_PORT');
            $command = sprintf("php -S localhost:$port -t App/public/");
            echo "[".date("Y-m-d H:i:s")."] | Jubilant: Starting developer server at http://localhost:{$port}\n";
            shell_exec($command);
        }

    }