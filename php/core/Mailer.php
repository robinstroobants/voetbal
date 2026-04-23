<?php
// We load PHPMailer via Composer autoload
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    /**
     * Stuur een e-mail via de gecentraliseerde SMTP instellingen.
     * 
     * @param string $to E-mailadres van de ontvanger
     * @param string $subject Onderwerp van de mail
     * @param string $body Inhoud van de mail (HTML of Plain text)
     * @return bool True bij succes, False bij mislukken
     */
    public static function send($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // Gmail App Password
            $mail->Username   = getenv('SMTP_USER') ?: 'robin@webbit.be';
            $mail->Password   = getenv('SMTP_PASS') ?: 'zswkgsukapbwntmf'; 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = getenv('SMTP_PORT') ?: 587;

            // Recipients
            $fromEmail = getenv('SMTP_FROM_EMAIL') ?: 'lineup@webbit.be';
            $fromName  = getenv('SMTP_FROM_NAME') ?: 'LineUp Automail';
            
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(false); // Standaard sturen we plain-text mails in de huidige setup
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
