<?php

declare(strict_types=1);

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody = ''
    ): bool {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('SMTP_HOST', 'smtp.sendgrid.net');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('SMTP_USER', 'apikey');
            $mail->Password   = env('SMTP_PASS', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) env('SMTP_PORT', 587);

            $mail->setFrom(env('SMTP_FROM', 'no-reply@newtonjee.com'), env('SMTP_FROM_NAME', 'NewtonJEE'));
            $mail->addAddress($toEmail, $toName);
            $mail->addReplyTo('support@newtonjee.com', 'NewtonJEE Support');

            $mail->isHTML(true);
            $mail->Subject  = $subject;
            $mail->Body     = self::wrap($htmlBody, $subject);
            $mail->AltBody  = $textBody ?: strip_tags($htmlBody);

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('MailHelper error: ' . $mail->ErrorInfo);
            return false;
        }
    }

    /** Send to multiple recipients */
    public static function sendBulk(array $recipients, string $subject, string $htmlBody): void
    {
        foreach ($recipients as $r) {
            self::send($r['email'], $r['name'], $subject, $htmlBody);
        }
    }

    /** Wrap body in branded HTML template */
    private static function wrap(string $body, string $subject): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body { margin:0; padding:0; background:#f0f3fc; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:580px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:#1E3A5F; padding:28px 32px; }
  .header h1 { margin:0; color:#fff; font-size:20px; font-weight:700; letter-spacing:-.3px; }
  .header span { color:#93c5fd; font-size:13px; }
  .body { padding:28px 32px; color:#1e2340; font-size:15px; line-height:1.7; }
  .body p { margin:0 0 16px; }
  .btn { display:inline-block; padding:12px 26px; background:#2563EB; color:#fff; border-radius:8px; text-decoration:none; font-weight:700; font-size:15px; }
  .footer { padding:18px 32px; background:#f8faff; border-top:1px solid #e2e8f0; font-size:12px; color:#8890aa; text-align:center; }
</style></head>
<body>
  <div class="wrap">
    <div class="header">
      <h1>NewtonJEE</h1>
      <span>$subject</span>
    </div>
    <div class="body">$body</div>
    <div class="footer">
      &copy; 2026 NewtonJEE &bull; newtonjee.com &bull; This email was sent for account and course notifications only.
    </div>
  </div>
</body></html>
HTML;
    }
}
