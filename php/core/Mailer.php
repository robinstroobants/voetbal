<?php
// We load PHPMailer via Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

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
    public static function send($to, $subject, $body, $isHTML = false) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_SERVER['SMTP_HOST'] ?? (getenv('SMTP_HOST') ?: 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            
            // SMTP Auth
            $mail->Username   = $_SERVER['SMTP_USER'] ?? getenv('SMTP_USER');
            $mail->Password   = $_SERVER['SMTP_PASS'] ?? getenv('SMTP_PASS'); 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_SERVER['SMTP_PORT'] ?? (getenv('SMTP_PORT') ?: 587);

            // Recipients
            $fromEmail = $_SERVER['SMTP_FROM_EMAIL'] ?? (getenv('SMTP_FROM_EMAIL') ?: 'no-reply@notifications.webbit.be');
            $fromName  = $_SERVER['SMTP_FROM_NAME'] ?? (getenv('SMTP_FROM_NAME') ?: 'LineUp');
            
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to);

            // Anti-Spam optimalisatie: Stuur als multipart (HTML + AltBody)
            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            if ($isHTML) {
                $mail->Body    = $body;
                // Verwijder tags en zet breaks om naar newlines voor de AltBody
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            } else {
                // Als de originele string plain-text was, zet het mooi om naar HTML en gebruik de originele als AltBody
                $mail->Body    = nl2br(htmlspecialchars($body));
                $mail->AltBody = $body;
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
