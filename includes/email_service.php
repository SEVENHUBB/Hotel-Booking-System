<?php
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        
        try {
            // SMTP 设置
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = 'tangys-wm24@student.tarc.edu.my';      // 改成你的 Gmail
            $this->mail->Password = 'xxtx ibvz hteu jjxw';        // 改成你的 App Password (16位)
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = 587;
            
            $this->mail->setFrom('your-email@gmail.com', 'Hotel Booking System');
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
        }
    }
    
    public function sendOTP($toEmail, $otp) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Password Reset OTP - Hotel Booking System';
            $this->mail->Body = $this->getEmailTemplate($otp);
            
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $this->mail->ErrorInfo);
            return false;
        }
    }
    
    private function getEmailTemplate($otp) {
        return "
        <html>
        <body>
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password.</p>
            <p>Your OTP code is:</p>
            <h1 style='color: #4CAF50; font-size: 36px; letter-spacing: 5px;'>{$otp}</h1>
            <p>This OTP will expire in 10 minutes.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <br>
            <p>Thank you,<br>Hotel Booking System Team</p>
        </body>
        </html>
        ";
    }
}
?>