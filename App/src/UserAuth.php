<?php 
    namespace Jubilant;

    use Jubilant\Database;
    use Jubilant\RandomString;
    use Jubilant\PasswordHash;
    use Jubilant\EmailSender;
    use Jubilant\Superglobals\Session;
    use Jubilant\Superglobals\Cookie;

    class UserAuth {
        private $con;


        public function __construct() {
            require __DIR__.'/../settings.php';
            $this->con = new Database($DatabaseConnection[0], $DatabaseConnection[1], $DatabaseConnection[2], $DatabaseConnection[3]);
            $this->con->connect();
        }

        private function createUsersTable() {
            require __DIR__.'/../settings.php';
            $query = "CREATE TABLE IF NOT EXISTS users(
                ID INT AUTO_INCREMENT PRIMARY KEY,
                customID VARCHAR(255) NOT NULL,
                username VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                registered TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(255) NOT NULL
            )";

            if($this->con->getConnection()->query($query)) {
                return true;
            } else {
                echo $cantCreateUsersTable.$this->con->error."<br>";
                return false;
            }
        }

        private function createLastLoginsTable() {
            require __DIR__.'/../settings.php';
            $query  = "CREATE TABLE IF NOT EXISTS lastLogins(
                ID INT AUTO_INCREMENT PRIMARY KEY,
                userCustomID VARCHAR(255) NOT NULL,
                sessionID VARCHAR(255) NOT NULL,
                sessionIP VARCHAR(255) NOT NULL,
                sessionBrowser VARCHAR(255) NOT NULL,
                loginDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

            if($this->con->getConnection()->query($query)) {
                return true;
            } else {
                echo $cantCreateLastloginsTable.$this->con->error."<br>";
                return false;
            }
        }

        public function registerUser(string $username, string $email, string $password, string $sessionid, array $EmailServer, string $redirectURL, ?string $emailSender = null) {
            require __DIR__.'/../settings.php';
            $Session = new Session();
            if($sessionid == $_COOKIE['PHPSESSID']) {
                if($this->createUsersTable()) {
                    if(empty($username)) {
                        echo $emptyUsernameInput;
                    } else if(empty($email)) {
                        echo $emptyEmailInput;
                    } else if(empty($password)) {
                        echo $emptyPasswordInput;
                    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        echo $notValidEmail;
                    } else {
                        if($this->con->select("users", "*","`email`='$email'")) {
                            echo $emailAlredyUsed;
                        } else {
                            $PasswordHash = new PasswordHash();
                            $RandomString = new RandomString();
            
                            $hashedPassword = $PasswordHash->passwordHash($password);
                            $customID = $RandomString->generateCustomString('ucid',3,5);
            
                            if($this->con->insert("users",array("$customID","$username","$email","$hashedPassword","pending"),array("customID","username","email","password","status"))) {
                                
                                if($emailSender == null) {
                                    $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4]);
                                } else {
                                    $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4],$emailSender);
                                }
                                $Template = new Template(__DIR__.'/../public/templates/emailTemplates/welcomeNewUser.php');
                                $date = date("Y-m-d H:i:s");
                                $Template->setVariables(['username'=>$username,'email'=>$email,'registerDate'=>$date,'customID'=>$customID]);
                                if($EmailSender->sendMail($email,'Regisztráció megerősítés',$Template->render())) {
                                    echo $registeredSuccessfully;
                                } else {
                                    $this->con->delete("users","`email`='$email' AND `customID`='$customID'");
                                    echo $registeredUnsuccessfully;
                                }
    
                                return true;
                            }
                        }
                    }
                }
            } else {
                echo $invalidPHPsessionID;
            }

            $Session->regenerate();

        }

        public function loginUser(string $email, string $password, string $sessionID, array $EmailServer = null, ?string $emailSender = null) {
            require __DIR__.'/../settings.php';
            $Session = new Session();
            if($this->createLastLoginsTable()) {
                if($sessionID === $_COOKIE['PHPSESSID']) {
                    $PasswordHash = new PasswordHash();
                    
                    $selectUserDatas = $this->con->select("users","*","`email`='$email' AND `status`='confirmed'");
                    
                    if($selectUserDatas) {
                        if($PasswordHash->passwordVerify($password, $selectUserDatas[0]['password'])) {
        
                            $Session->set('customID', $selectUserDatas[0]['customID']);
                            $Session->set('username', $selectUserDatas[0]['username']);
                            $Session->set('email', $email);
                            $Session->set('registered', $selectUserDatas[0]['registered']);
                            $Session->set('accountStatus', $selectUserDatas[0]['status']);
                            $Session->set('loggedin', true);
    
                            $customID = $Session->get('customID');
                            $userBrowser = $_SERVER['HTTP_USER_AGENT'];
    
                            if($this->con->select("lastLogins","*","`userCustomID`='$customID' AND `sessionBrowser`='$userBrowser'")) {
                                echo "Sikeres bejelentkezés! <br>";
                                return true;
                            } else {
                                $userIP = $_SERVER['REMOTE_ADDR'];
                                $sessionID = $Session->getSessionID();
    
                                if($this->con->insert("lastLogins",array($customID,$sessionID,$userIP,$userBrowser),array("userCustomID","sessionID","sessionIP","sessionBrowser"))) {
                                    if($EmailServer != null) {
                                        if($emailSender == null) {
                                            $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4]);
                                        } else {
                                            $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4],$emailSender);
                                        }
    
                                        $to = $email;
                                        $subject = 'Új eszközről való bejelentkezés';
                                        $date = date("Y-m-d H:i:s");
                                        $body = "Tisztelt ".$Session->get('username')."! Új eszközről való bejelentkezést észleltünk! <br>Eszköz adatai: <br> <ul><li><b>Webböngésző:</b> $userBrowser</li><li><b>IP cím:</b> $userIP</li><li><b>Időpont:</b> $date</li></ul><br>Nem én voltam, <a href='/dashboard/account/changePassword'>megváltoztatom a jelszavamat!</a>";
                                        if($EmailSender->sendMail($to, $subject, $body)) {
                                            echo "Sikeres bejelentkezés! <br>";
                                            $Session->regenerate();
                                            return true;
                                        }
                                    }
                                }
                            }
                        } else {
                            echo $invalidPassword;
                            return false;
                        }
                    } else {
                        echo $userNotFound;
                        return false;
                    }
                } else {
                    echo $invalidPHPsessionID;
                    return false;
                }
    
                return true;
            }

            $Session->regenerate();
    
        }
        
        public function changePassword(string $email, string $newPassword, string $sessionID,array $EmailServer, ?string $emailSender = null) {
            require __DIR__.'/../settings.php';
            $Session = new Session();
            if ($sessionID == $_COOKIE['PHPSESSID']) {
                $PasswordHash = new PasswordHash();
                $hashedPassword = $PasswordHash->passwordHash($newPassword);
                if($this->con->update("users",array("password"=>$hashedPassword),array("email"=>$email))) {
                    if($emailSender == null) {
                        $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4]);
                    } else {
                        $EmailSender = new EmailSender($EmailServer[0],$EmailServer[1],$EmailServer[2],$EmailServer[3],$EmailServer[4], $emailSender);
                    }
                    $Template = new Template(__DIR__.'/../public/templates/emailTemplates/passwordChange.php');
                    $Template->setVariables([
                        'email' => $email,
                        'date'  => date("Y-m-d H:i:s")
                    ]);

                    $EmailSender->sendMail($email, 'Jelszó megváltoztatás', $Template->render());
                    echo $passwordSuccessfullyChanged;
                    return true;
                } else {
                    echo $passwordUnsuccessfullyChanged;
                    return false;
                }
            } else {
                echo $invalidPHPsessionID;
                return false;
            }

            $Session->regenerate();
            return true;
        }

        public function confAuth(string $customID) {
            require __DIR__.'/../settings.php';
            if($this->con->select("users","*","`customID`='$customID' AND `status`='pending'")) {
                if($this->con->update("users",array('status'=>"confirmed"),array('customID'=>$customID))) {
                    echo $userConfirmedSuccessfully;
                } else {
                    echo $userConfirmedUnsuccessfully;
                    return false;
                }
            } else {
                echo $userNotFound;
                return false;
            }

            return true;
        }

    }
?>