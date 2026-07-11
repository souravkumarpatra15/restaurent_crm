<?php

namespace App\Libraries;
use PHPMailer\PHPMailer\PHPMailer;
class Mail
{
    /**
     * Send password reset email
     */
    public static function sendPasswordReset(string $toEmail, string $toName, string $resetLink): bool
    {
        $subject = 'Reset Your DinoviX CRM Password';

        $body = self::wrapTemplate('Password Reset Request', "
            <p>Hi <strong>" . esc($toName) . "</strong>,</p>
            <p>We received a request to reset your password. Click the button below to set a new one:</p>
            <div style='text-align:center;margin:2rem 0'>
                <a href='" . $resetLink . "' style='background:#FF6B35;color:#fff;padding:.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:800;font-size:1rem;display:inline-block'>
                    Reset My Password
                </a>
            </div>
            <p style='color:#94A3B8;font-size:.85rem'>This link expires in <strong>1 hour</strong>. If you didn't request a password reset, you can safely ignore this email.</p>
            <p style='color:#94A3B8;font-size:.8rem;word-break:break-all'>Or paste this link in your browser:<br>" . $resetLink . "</p>
        ");

        return self::send($toEmail, $toName, $subject, $body);
    }

    /**
     * Send subscription payment reminder
     */
    public static function sendPaymentReminder(string $toEmail, string $restName, string $expiresAt, string $payLink): bool
    {
        $subject = 'Your DinoviX CRM Subscription is Expiring Soon';

        $body = self::wrapTemplate('Subscription Expiring Soon ⚠️', "
            <p>Hi <strong>" . esc($restName) . "</strong>,</p>
            <p>Your DinoviX CRM subscription expires on <strong>" . date('d M Y', strtotime($expiresAt)) . "</strong>.</p>
            <p>Renew now to keep your POS, kitchen display, and QR ordering running without interruption.</p>
            <div style='text-align:center;margin:2rem 0'>
                <a href='" . $payLink . "' style='background:#22C55E;color:#fff;padding:.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:800;font-size:1rem;display:inline-block'>
                    Renew Subscription
                </a>
            </div>
            <p style='color:#94A3B8;font-size:.85rem'>Contact your admin if you need any help.</p>
        ");

        return self::send($toEmail, $restName, $subject, $body);
    }

    /**
     * Send welcome email after restaurant creation
     */
    public static function sendWelcome(string $toEmail, string $restName, string $loginUrl, string $password): bool
    {
        $subject = 'Welcome to DinoviX CRM — Your Account is Ready';

        $body = self::wrapTemplate('Welcome to DinoviX CRM 🎉', "
            <p>Hi <strong>" . esc($restName) . "</strong>,</p>
            <p>Your restaurant management account has been created successfully. Here are your login details:</p>
            <div style='background:#F8FAFC;border-radius:10px;padding:1rem 1.25rem;margin:1.5rem 0;border-left:4px solid #FF6B35'>
                <p style='margin:0 0 .5rem'><strong>Login URL:</strong> <a href='" . $loginUrl . "'>" . $loginUrl . "</a></p>
                <p style='margin:0 0 .5rem'><strong>Email:</strong> " . esc($toEmail) . "</p>
                <p style='margin:0'><strong>Password:</strong> <code style='background:#E2E8F0;padding:.15rem .4rem;border-radius:4px'>" . esc($password) . "</code></p>
            </div>
            <p style='color:#EF4444;font-size:.85rem'>⚠️ Please change your password after first login.</p>
            <div style='text-align:center;margin:2rem 0'>
                <a href='" . $loginUrl . "' style='background:#FF6B35;color:#fff;padding:.875rem 2rem;border-radius:10px;text-decoration:none;font-weight:800;font-size:1rem;display:inline-block'>
                    Login Now
                </a>
            </div>
        ");

        return self::send($toEmail, $restName, $subject, $body);
    }

    public static function send(string $to, string $toName, string $subject, string $message): bool
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
        $mail->addAddress($to, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        return $mail->send();
    }

    // ── HTML email wrapper template ───────────────────────────
    private static function wrapTemplate(string $heading, string $content): string
    {
        $year    = date('Y');
        $appName = config('App')->appName ?? 'DinoviX CRM';

        return "<!DOCTYPE html>
            <html lang='en'>
            <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width,initial-scale=1'>
            <title>{$heading}</title>
            </head>
            <body style='margin:0;padding:0;background:#F1F5F9;font-family:\"Segoe UI\",system-ui,sans-serif;-webkit-font-smoothing:antialiased'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background:#F1F5F9;padding:2rem 1rem'>
                <tr>
                <td align='center'>
                    <table width='100%' style='max-width:520px;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08)'>

                    <!-- Header -->
                    <tr>
                        <td style='background:linear-gradient(135deg,#0F172A,#1E293B);padding:1.5rem;text-align:center'>
                        <div style='font-size:1.5rem;font-weight:900;color:#fff;letter-spacing:-.01em'><img src='https://www.dinovix.ngwebd.com/images/favicon.png' alt='{$appName}' style='vertical-align:middle;margin-right:1rem'> {$appName}</div>
                        </td>
                    </tr>

                    <!-- Heading -->
                    <tr>
                        <td style='padding:1.5rem 2rem .5rem'>
                        <h1 style='margin:0;font-size:1.25rem;font-weight:900;color:#0F172A'>{$heading}</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style='padding:.5rem 2rem 1.5rem;font-size:.9rem;color:#334155;line-height:1.7'>
                        {$content}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='background:#F8FAFC;padding:1rem 2rem;text-align:center;border-top:1px solid #E2E8F0'>
                        <p style='margin:0;font-size:.75rem;color:#94A3B8'>© {$year} {$appName} · All rights reserved</p>
                        <p style='margin:.25rem 0 0;font-size:.72rem;color:#CBD5E1'>This is an automated email — please do not reply.</p>
                        </td>
                    </tr>

                    </table>
                </td>
                </tr>
            </table>
            </body>
            </html>";
    }
}
