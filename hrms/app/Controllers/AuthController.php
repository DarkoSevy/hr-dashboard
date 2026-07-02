<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\View;

class AuthController extends Controller
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        View::render('auth/login', [], bare: true);
    }

    public function attempt(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Database::fetch(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
            [$username, $username]
        );

        if ($user && $user['locked_until'] && strtotime($user['locked_until']) > time()) {
            flash('error', 'Account locked after repeated failures. Try again later.');
            redirect('login');
        }

        if (!$user || !password_verify($password, $user['password_hash'])) {
            if ($user) {
                $attempts = $user['failed_attempts'] + 1;
                $lock = $attempts >= self::MAX_ATTEMPTS
                    ? date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60) : null;
                Database::query(
                    'UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?',
                    [$attempts, $lock, $user['id']]
                );
            }
            Audit::log('login_failed', 'users', $username);
            flash('error', 'Invalid username or password.');
            redirect('login');
        }

        // Two-factor: email a 6-digit OTP and defer the session.
        if ($user['two_factor_enabled']) {
            $code = (string) random_int(100000, 999999);
            Database::query(
                'UPDATE users SET two_factor_code = ?, two_factor_expires = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE id = ?',
                [password_hash($code, PASSWORD_BCRYPT), $user['id']]
            );
            Mailer::send($user['email'], 'PTS HRMS verification code',
                "Your verification code is <strong>{$code}</strong>. It expires in 10 minutes.");
            $_SESSION['pending_2fa_user'] = (int) $user['id'];
            redirect('otp');
        }

        Auth::login($user);
        $this->enforcePasswordExpiry($user);
        redirect('dashboard');
    }

    public function otpForm(): void
    {
        if (empty($_SESSION['pending_2fa_user'])) {
            redirect('login');
        }
        View::render('auth/otp', [], bare: true);
    }

    public function verifyOtp(): void
    {
        $userId = $_SESSION['pending_2fa_user'] ?? null;
        if (!$userId) {
            redirect('login');
        }
        $user = Database::fetch(
            'SELECT * FROM users WHERE id = ? AND two_factor_expires > NOW()', [$userId]
        );
        $code = trim($_POST['code'] ?? '');
        if (!$user || !password_verify($code, (string) $user['two_factor_code'])) {
            flash('error', 'Invalid or expired code.');
            redirect('otp');
        }
        Database::query(
            'UPDATE users SET two_factor_code = NULL, two_factor_expires = NULL WHERE id = ?', [$userId]
        );
        unset($_SESSION['pending_2fa_user']);
        Auth::login($user);
        $this->enforcePasswordExpiry($user);
        redirect('dashboard');
    }

    /** Route users with an expired password straight to the change form. */
    private function enforcePasswordExpiry(array $user): void
    {
        if (Auth::passwordExpired($user)) {
            flash('error', 'Your password has expired under the security policy — please choose a new one now.');
            redirect('account');
        }
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }

    // ------------------------------------------------------------ password reset

    public function forgotForm(): void
    {
        View::render('auth/forgot', [], bare: true);
    }

    /** Email a one-hour reset link. Response is identical whether or not the
     *  address exists, to avoid account enumeration. */
    public function sendReset(): void
    {
        $email = trim($_POST['email'] ?? '');
        $user = Database::fetch('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::insert('password_resets', [
                'user_id' => $user['id'],
                'token_hash' => hash('sha256', $token),
                'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            ]);
            $link = rtrim($GLOBALS['app_config']['app']['url'], '/') . '/reset?token=' . $token;
            Mailer::send($user['email'], 'PTS HRMS password reset',
                "A password reset was requested for your account. "
                . "<a href=\"{$link}\">Click here to choose a new password</a>. "
                . 'The link expires in 1 hour. If this wasn\'t you, ignore this email.');
            Audit::log('password_reset_requested', 'users', (string) $user['id']);
        }
        flash('success', 'If that email is registered, a reset link has been sent.');
        redirect('login');
    }

    public function resetForm(): void
    {
        $reset = $this->validResetRow($_GET['token'] ?? '');
        if (!$reset) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('login');
        }
        View::render('auth/reset', ['token' => $_GET['token']], bare: true);
    }

    public function performReset(): void
    {
        $token = $_POST['token'] ?? '';
        $reset = $this->validResetRow($token);
        if (!$reset) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('login');
        }
        $new = $_POST['password'] ?? '';
        if ($new !== ($_POST['confirm'] ?? '')) {
            flash('error', 'Passwords do not match.');
            redirect('reset?token=' . urlencode($token));
        }
        if (!Auth::validPassword($new)) {
            flash('error', 'Password must be 8+ characters with upper, lower, digit and symbol.');
            redirect('reset?token=' . urlencode($token));
        }
        Database::update('users', [
            'password_hash' => password_hash($new, PASSWORD_BCRYPT),
            'password_changed_at' => date('Y-m-d H:i:s'),
            'failed_attempts' => 0,
            'locked_until' => null,
        ], (int) $reset['user_id']);
        Database::update('password_resets', ['used_at' => date('Y-m-d H:i:s')], (int) $reset['id']);
        Audit::log('password_reset', 'users', (string) $reset['user_id']);
        flash('success', 'Password updated — sign in with your new password.');
        redirect('login');
    }

    private function validResetRow(string $token): ?array
    {
        if ($token === '') {
            return null;
        }
        return Database::fetch(
            'SELECT * FROM password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()',
            [hash('sha256', $token)]
        );
    }
}
