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


        public function __construct($db_host, $db_user, $db_pass, $db_name) {
            $this->con = new Database($db_host, $db_user, $db_pass, $db_name);
            $this->con->connect();
        }

        private function createUsersTable() {
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
                echo "Nem sikerült létrehozni a <u><i>felhasználók</i></u> táblát! ".$this->con->error."<br>";
                return false;
            }
        }

        private function createLastLoginsTable() {
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
                echo "Nem sikerült létrehozni az <u><i>új bejelentkezések</i></u> táblát! ".$this->con->error."<br>";
                return false;
            }
        }

        public function registerUser(string $username, string $email, string $password, string $sessionid, array $EmailServer, string $redirectURL, ?string $emailSender = null) {
            $Session = new Session();
            if($sessionid == $_COOKIE['PHPSESSID']) {
                if($this->createUsersTable()) {
                    if(empty($username)) {
                        echo "Hiba! A felhasználónév mező nem lehet üres! <br>";
                    } else if(empty($email)) {
                        echo "Hiba! Az email cím mező nem lehet üres! <br>";
                    } else if(empty($password)) {
                        echo "Hiba! A jelszó mező nem lehet üres! <br>";
                    } else {
                        if($this->con->select("users", "*","`email`='$email'")) {
                            echo "Ez az email cím ($email) már használatban van! <br>";
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
                                $Template = new Template(__DIR__.'/../public/templates/emailTemplates/welcomeNewUser.html');
                                $date = date("Y-m-d H:i:s");
                                $Template->setVariables(['username'=>$username,'email'=>$email,'registerDate'=>$date,'customID'=>$customID]);
                                if($EmailSender->sendMail($email,'Regisztráció megerősítés',$Template->render())) {
                                    echo "Sikeres regisztráció! <br>";
                                } else {
                                    $this->con->delete("users","`email`='$email' AND `customID`='$customID'");
                                    echo "Sikertelen regisztráció!";
                                }
    
                                return true;
                            }
                        }
                    }
                }
            } else {
                echo "A küldött <i>php session id</i> nem egyezik meg a valós értékkel!";
            }

            $Session->regenerate();

        }

        public function loginUser(string $email, string $password, string $sessionID, array $EmailServer = null, ?string $emailSender = null) {
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
                            echo "Helytelen jelszó! <br>";
                            return false;
                        }
                    } else {
                        echo "Nem található ilyen fiók, vagy több van ezekkel az adatokkal, így nem lehet beazonosítani. <br>";
                        return false;
                    }
                } else {
                    echo "Biztonsági rés: a küldött php session_id nem egyezik meg a valós értékkel! <br>";
                    return false;
                }
    
                return true;
            }

            $Session->regenerate();
    
        }
        
        public function changePassword(string $email, string $newPassword, string $sessionID,array $EmailServer, ?string $emailSender = null) {
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
                    $Template = new Template(__DIR__.'/../public/templates/emailTemplates/passwordChange.html');
                    $Template->setVariables([
                        'email' => $email,
                        'date'  => date("Y-m-d H:i:s")
                    ]);

                    $EmailSender->sendMail($email, 'Jelszó megváltoztatás', $Template->render());
                    echo "A jelszó sikeresen megváltoztatva! <br>";
                    return true;
                } else {
                    echo "A jelszót nem sikerült megváltoztatni! Kérjük próbálja újra később! <br>";
                    return false;
                }
            } else {
                echo "Biztonsági rés: a küldött php session_id nem egyezik meg a valós értékkel! <br>";
                return false;
            }

            $Session->regenerate();
            return true;
        }

        public function confAuth(string $customID) {
            if($this->con->select("users","*","`customID`='$customID' AND `status`='pending'")) {
                if($this->con->update("users",array('status'=>"confirmed"),array('customID'=>$customID))) {
                    echo "Felhasználó megerősítve! <br>";
                    return true;
                } else {
                    throw new Exceception("Something went wrong while updating the user's status: ".$this->con->error);
                }
            } else {
                echo "Nem található felhasználó! <br>";
            }
        }

    }
?>