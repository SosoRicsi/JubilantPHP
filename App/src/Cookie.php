<?php 
    namespace Jubilant\Superglobals;

    class Cookie {

        public function set($name, $value, $expire = 3600, $path = '/', $domain = "", $secure = false, $httponly = false) {
            $expireTime = time() + $expire;

            secookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }

        public function exists($name) {
            return isset($_COOKIE[$name]);
        }

        public function get($name) {
            if(!empty($this->exists($name))) {
                return $_COOKIE[$name];
            } else {
                return false;
            }
        }

        public function getAll() {
            return $_COOKIE;
        }

        public function remove($name, $path = '/', $domain = "") {
            if($this->exists($name)) {
                setcookie($name, '', time()-42000, $path, $domain);
                unset($_COOKIE[$name]);
            }
        }

        public function destroy() {
            foreach ($_COOKIE as $name => $value) {
                setcookie($name, '', time()-42000, '/');
                unset($_COOKIE[$name]);
            }
        }

    }
?>