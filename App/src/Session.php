<?php 
    namespace Jubilant\Superglobals;

    class Session {
        
        public function __construct() {
            if(session_status() == PHP_SESSION_NONE) {
                session_start();
            }
        }

        public function set($key, $value) {
            $_SESSION[$key] = $value;
        }

        public function get($key) {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }

        public function getAll(bool $all = true) {
            if($all == true) {
                return $_SESSION;
            } else {
                return false;
            }
        }

        public function exists($name) {
            return isset($_SESSION[$name]);
        }

        public function remove($key) {
            if(isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
        }

        public function destroy() {
            $_SESSION = [];
            if(session_id() != '' || isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time()-42000,'/');
            }
            session_unset();
            session_destroy();
        }

        public function regenerate() {
            session_regenerate_id(true);
        }

        public function getSessionID() {
            return session_id();
        }

    }
?>