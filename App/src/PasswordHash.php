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
            if(strlen($password) < $minLenght) {
                return 'A jelszó nem elég hosszú! Minimum '.$minLenght.' karakter szükséges!';
                return false;
            }

            if(!preg_match('/[A-Z]/', $password)) {
                return 'A jelszónak legalább egy nagy betűt tartalmaznia kell!';
                return false;
            }

            if(!preg_match('/[a-z]/', $password)) {
                return 'A jelszónak legalább egy kisbetűt tartalmaznia kell!';
                return false;
            }

            if(!preg_match('/[0-9]/', $password)) {
               return 'A jelszónak legalább egy számot tartalmaznia kell!';
                return false;
            }

            if(!preg_match('/[!@$&_]/', $password)) {
                return 'A jelszónak legalább egy speciális karaktert tartalmazni kell! (!@$&_)';
                return false;
            }

            return true;
        }

    }
?>