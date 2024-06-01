<?php 
    namespace Jubilant;

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class EmailSender {

        private $mailer;

        public function __construct($host, $username, $password, $port, $senderEmail, $senderName = 'JubilantPHP EmailSender system') {
            $this->mailer = new PHPMailer(true);

            $this->mailer->isSMTP();
            $this->mailer->Host = $host;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $username;
            $this->mailer->Password = $password;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $port;
            $this->mailer->SetLanguage("hu","php_mailer/language");
            $this->mailer->CharSet = "utf-8";

            $this->mailer->setFrom($senderEmail, $senderName);
        }

        public function sendMail(string $to, string $subject, string $body) {
            try {
                $this->mailer->addAddress($to);
    
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $subject;
                $this->mailer->Body    = $body;
                $this->mailer->AltBody = strip_tags($body);
                $this->mailer->Subject = "=?UTF-8?B?".base64_encode($subject)."?=";
    
                $this->mailer->send();
                echo 'Message has been sent! <br>';
            } catch (Exception $e) {
                throw new Exception("An error occurred while sending the email: ".$this->mailer->ErrorInfo."<br>");
            }
            return true;
        }

    }
?>