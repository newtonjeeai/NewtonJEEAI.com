<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;
use App\Helpers\MailHelper;

class AuthController extends BaseController
{
    public function loginForm(array $params = []): void
    {
        if (!empty($_SESSION['admin_id'])) {
            $this->redirect('/admin');
        }
        include VIEWS_PATH . '/admin/auth/login.php';
    }

    public function login(array $params = []): void
    {
        $this->verifyCsrf();

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->flash('error', 'Email and password are required.');
            $this->redirect('/admin/login');
        }

        // Rate limiting: max 5 attempts per 15 min per IP
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = 'admin_login_' . md5($ip);
        $attempts = (int)($_SESSION[$key] ?? 0);
        $lockTime  = (int)($_SESSION[$key . '_lock'] ?? 0);

        if ($lockTime > time()) {
            $wait = ceil(($lockTime - time()) / 60);
            $this->flash('error', "Too many failed attempts. Please wait {$wait} minute(s).");
            $this->redirect('/admin/login');
        }

        $user = Database::queryOne(
            'SELECT * FROM users WHERE email = ? AND role IN (\'admin\',\'super_admin\') AND is_active = 1 AND deleted_at IS NULL',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            $_SESSION[$key] = $attempts + 1;
            if ($attempts + 1 >= 5) {
                $_SESSION[$key . '_lock'] = time() + 900; // 15 min
                $_SESSION[$key] = 0;
            }
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/admin/login');
        }

        // Rehash if needed
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            Database::execute('UPDATE users SET password = ? WHERE id = ?', [$hash, $user['id']]);
        }

        // Clear attempts
        unset($_SESSION[$key], $_SESSION[$key . '_lock']);

        session_regenerate_id(true);
        $_SESSION['admin_id']   = $user['id'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['admin_name'] = $user['name'];

        Database::execute('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        $this->audit('admin.login');

        $this->redirect('/admin');
    }

    public function logout(array $params = []): void
    {
        $this->audit('admin.logout');
        unset($_SESSION['admin_id'], $_SESSION['admin_role'], $_SESSION['admin_name']);
        $this->redirect('/admin/login');
    }

    public function forgotForm(array $params = []): void
    {
        include VIEWS_PATH . '/admin/auth/forgot.php';
    }

    public function forgotSend(array $params = []): void
    {
        $this->verifyCsrf();
        $email = strtolower(trim($_POST['email'] ?? ''));

        // Always show same message to prevent email enumeration
        $successMsg = 'If that email belongs to an admin account, a reset link has been sent.';

        $user = Database::queryOne(
            'SELECT id, name FROM users WHERE email = ? AND role IN (\'admin\',\'super_admin\',\'mentor\') AND is_active = 1',
            [$email]
        );

        if ($user) {
            // Clean up old tokens
            Database::execute('DELETE FROM admin_password_resets WHERE email = ?', [$email]);

            $rawToken  = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);

            Database::execute(
                'INSERT INTO admin_password_resets (email, token_hash, expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))',
                [$email, $tokenHash]
            );

            $resetUrl = APP_URL . '/admin/reset-password?token=' . $rawToken . '&email=' . urlencode($email);
            MailHelper::send(
                $email,
                $user['name'],
                'NewtonJEE Admin — Password Reset',
                "<p>Hi {$user['name']},</p><p>Click the link below to reset your admin password. It expires in 1 hour.</p><p><a href='{$resetUrl}'>{$resetUrl}</a></p><p>If you did not request this, ignore this email.</p>"
            );
        }

        $this->flash('success', $successMsg);
        $this->redirect('/admin/login');
    }

    public function resetForm(array $params = []): void
    {
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';
        include VIEWS_PATH . '/admin/auth/reset.php';
    }

    public function resetSave(array $params = []): void
    {
        $this->verifyCsrf();
        $token    = $_POST['token'] ?? '';
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if ($password !== $confirm) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        if (strlen($password) < 8) {
            $this->flash('error', 'Password must be at least 8 characters.');
            $this->redirect('/admin/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        $tokenHash = hash('sha256', $token);
        $row = Database::queryOne(
            'SELECT id FROM admin_password_resets WHERE email = ? AND token_hash = ? AND expires_at > NOW()',
            [$email, $tokenHash]
        );

        if (!$row) {
            $this->flash('error', 'Reset link is invalid or has expired. Please request a new one.');
            $this->redirect('/admin/forgot-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        Database::execute('UPDATE users SET password = ? WHERE email = ?', [$hash, $email]);
        Database::execute('DELETE FROM admin_password_resets WHERE email = ?', [$email]);

        $this->flash('success', 'Password updated successfully. Please log in.');
        $this->redirect('/admin/login');
    }
}
