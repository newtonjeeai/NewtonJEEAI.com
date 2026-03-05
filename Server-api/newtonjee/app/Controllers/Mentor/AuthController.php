<?php

declare(strict_types=1);

namespace App\Controllers\Mentor;

use App\Controllers\BaseController;
use App\Database;
use App\Helpers\MailHelper;

// ── Mentor Auth ──────────────────────────────────────────────
class AuthController extends BaseController
{
    public function loginForm(array $params = []): void
    {
        if (!empty($_SESSION['mentor_id'])) { $this->redirect('/mentor'); }
        include VIEWS_PATH . '/mentor/auth/login.php';
    }

    public function login(array $params = []): void
    {
        $this->verifyCsrf();
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        // Rate limiting
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0';
        $key = 'mentor_login_' . md5($ip);
        $attempts = (int)($_SESSION[$key] ?? 0);
        if ($attempts >= 5) {
            $this->flash('error', 'Too many failed attempts. Please wait 15 minutes.');
            $this->redirect('/mentor/login');
        }

        $user = Database::queryOne(
            'SELECT * FROM users WHERE email = ? AND role = \'mentor\' AND is_active = 1 AND deleted_at IS NULL',
            [$email]
        );

        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            $_SESSION[$key] = $attempts + 1;
            $this->flash('error', 'Invalid email or password.');
            $this->redirect('/mentor/login');
        }

        unset($_SESSION[$key]);
        session_regenerate_id(true);
        $_SESSION['mentor_id']   = $user['id'];
        $_SESSION['mentor_name'] = $user['name'];
        Database::execute('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$user['id']]);
        $this->redirect('/mentor');
    }

    public function logout(array $params = []): void
    {
        unset($_SESSION['mentor_id'], $_SESSION['mentor_name']);
        $this->redirect('/mentor/login');
    }
}
