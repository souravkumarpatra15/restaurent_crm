<?php
namespace App\Libraries;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    public static function send(string $to, string $subject, string $message): bool
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com.';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info.dinovix@gmail.com';
        $mail->Password   = 'oeqgtnnxntjhhnxy';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('info.dinovix@gmail.com', 'DinoviX - Run Your Restaurant Smarter');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        return $mail->send();
    }
}
