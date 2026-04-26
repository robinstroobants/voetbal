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
            // Check of we lokaal draaien
            $is_localhost = isset($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

            $mail->isSMTP();
            
            if ($is_localhost) {
                // Lokale Mailpit configuratie
                $mail->Host       = 'mailpit';
                $mail->SMTPAuth   = false;
                $mail->Port       = 1025;
            } else {
                // Productie configuratie (Gmail)
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'robin@webbit.be';
                $mail->Password   = $_SERVER['SMTP_PASS'] ?? getenv('SMTP_PASS'); 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
            }

            // Recipients
            $fromEmail = 'no-reply@notifications.webbit.be';
            $fromName  = 'LineUp';
            
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
