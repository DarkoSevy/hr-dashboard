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
        redirect('dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }
}
