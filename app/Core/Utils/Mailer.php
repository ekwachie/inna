<?php
namespace app\Core\Utils;

use app\Core\Application;
use app\Core\DbModel;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends DbModel
{
    public function sendMail($mailTo, $fname, $subject, $msg)
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        //Set SMTP host name                          
        $mail->Host = $_ENV['SMTP_HOST'] ?? '';
        if ($mail->Host === '') throw new \RuntimeException('Configure SMTP_HOST before sending mail.');
        //Set this to true if SMTP host requires authentication to send email
        $mail->SMTPAuth = ($_ENV['SMTP_USERNAME'] ?? '') !== '';
        //Provide username and password     
        $mail->Username = $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password = $_ENV['SMTP_PASSWORD'] ?? '';
        //If SMTP requires TLS encryption then set it
        $mail->SMTPSecure = "tls";
        //Set TCP port to connect to
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 587);

        $mail->From = $_ENV['SMTP_FROM_ADDRESS'] ?? '';
        if (!filter_var($mail->From, FILTER_VALIDATE_EMAIL)) throw new \RuntimeException('Configure SMTP_FROM_ADDRESS.');
        $mail->FromName = $_ENV['SMTP_FROM_NAME'] ?? ($_ENV['APP_NAME'] ?? 'Inna');

        $mail->addAddress($mailTo, ""); //Recipient name is optional 
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $this->getEmailTemplate($fname, $msg, $mailTo);

        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Generate modern email template with Tailwind-inspired design
     * Uses inline CSS for email client compatibility
     * Loads template from separate HTML file
     */
    private function getEmailTemplate($fname, $msg, $mailTo)
    {
        // Get app name from env or use default
        $appName = $_ENV['APP_NAME'] ?? 'Inna Framework';
        $appUrl = defined('BASE_URL') ? BASE_URL : 'https://payperlez.org';
        $currentYear = date('Y');
        
        // Get template file path
        $templatePath = Application::$ROOT_DIR . '/app/templates/email/default.html';
        
        // Check if template file exists
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template not found: {$templatePath}");
        }
        
        // Load template content
        $template = file_get_contents($templatePath);
        
        // Replace placeholders with actual values
        $replacements = [
            '{{APP_NAME}}' => htmlspecialchars($appName),
            '{{FNAME}}' => htmlspecialchars($fname),
            '{{MESSAGE}}' => nl2br(htmlspecialchars($msg)),
            '{{MAIL_TO}}' => htmlspecialchars($mailTo),
            '{{CURRENT_YEAR}}' => $currentYear,
            '{{APP_URL}}' => htmlspecialchars($appUrl),
        ];
        
        // Replace all placeholders
        foreach ($replacements as $placeholder => $value) {
            $template = str_replace($placeholder, $value, $template);
        }
        
        return $template;
    }
}
