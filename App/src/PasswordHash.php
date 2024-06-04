<?php 
    namespace Jubilant;

    class PasswordHash {
        private $hashedPassword;
        
        public function passwordHash($password, $hashType = PASSWORD_DEFAULT) {
            $this->hashedPassword = password_hash($password, $hashType);

            return $this->hashedPassword;
        }

        public function passwordVerify($password, $hashedPassword) {
            return password_verify($password, $hashedPassword);
        }

        public function isPasswordStrong($password, $minLenght = 8) {
            require __DIR__.'/../settings.php';
            
            if(strlen($password) < $minLenght) {
                return $passwordMinimumCharError.$minLenght;
                return false;
            }

            if(!preg_match('/[A-Z]/', $password)) {
                return $passwordLeastOneUppercase;
                return false;
            }

            if(!preg_match('/[a-z]/', $password)) {
                return $passwordLeastOneLowercase;
                return false;
            }

            if(!preg_match('/[0-9]/', $password)) {
               return $passwordLeastOneNumber;
                return false;
            }

            if(!preg_match('/[!@$&_]/', $password)) {
                return $passwordLeastOneSpecialChar.' (!@$&_)';
                return false;
            }

            return true;
        }

    }
?>