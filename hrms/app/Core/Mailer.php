<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Email notifications. Uses PHPMailer when installed via Composer
 * (composer require phpmailer/phpmailer); otherwise falls back to mail().
 * All sends are best-effort: HR actions must never fail because SMTP is down.
 */
class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody): bool
    {
        $cfg = $GLOBALS['app_config']['mail'];
        if (!$cfg['enabled']) {
            return false;
        }
        try {
            if (class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $cfg['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $cfg['username'];
                $mail->Password   = $cfg['password'];
                $mail->SMTPSecure = $cfg['encryption'];
                $mail->Port       = $cfg['port'];
                $mail->setFrom($cfg['from_email'], $cfg['from_name']);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = self::template($subject, $htmlBody);
                $mail->send();
                return true;
            }
            $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\n"
                     . "From: {$cfg['from_name']} <{$cfg['from_email']}>\r\n";
            return mail($to, $subject, self::template($subject, $htmlBody), $headers);
        } catch (\Throwable $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    /** Queue an in-app notification and optionally email the user. */
    public static function notify(int $userId, string $type, string $title, string $body, ?string $link = null, bool $email = true): void
    {
        Database::insert('notifications', [
            'user_id' => $userId, 'type' => $type, 'title' => $title,
            'body' => $body, 'link' => $link, 'channel' => 'system',
        ]);
        if ($email) {
            $address = Database::scalar('SELECT email FROM users WHERE id = ?', [$userId]);
            if ($address) {
                self::send((string) $address, $title, $body);
            }
        }
    }

    private static function template(string $title, string $body): string
    {
        $company = e($GLOBALS['app_config']['app']['name']);
        return <<<HTML
<div style="font-family:Segoe UI,Arial,sans-serif;max-width:600px;margin:auto;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden">
  <div style="background:#0b3d66;color:#fff;padding:18px 24px;font-size:18px;font-weight:600">{$company}</div>
  <div style="padding:24px;color:#1e293b;font-size:14px;line-height:1.6">
    <h3 style="margin-top:0;color:#0b3d66">{$title}</h3>
    <p>{$body}</p>
  </div>
  <div style="background:#f1f5f9;padding:12px 24px;font-size:12px;color:#64748b">
    Premier Transport &amp; Tour Services Ltd — automated HR notification. Do not reply.
  </div>
</div>
HTML;
    }
}
