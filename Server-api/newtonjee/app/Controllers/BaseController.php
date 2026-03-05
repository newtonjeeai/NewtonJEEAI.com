<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Database;

abstract class BaseController
{
    // ── View rendering ──────────────────────────────────────────
    protected function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $file");
        }
        include VIEWS_PATH . '/layouts/app.php';
    }

    protected function adminView(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $file");
        }
        include VIEWS_PATH . '/layouts/admin.php';
    }

    protected function mentorView(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $file");
        }
        include VIEWS_PATH . '/layouts/mentor.php';
    }

    /** Render a view without a layout (for partials / AJAX) */
    protected function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        include $file;
    }

    // ── Redirects ───────────────────────────────────────────────
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function back(): void
    {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
    }

    // ── Flash messages ──────────────────────────────────────────
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    // ── JSON responses (for AJAX) ───────────────────────────────
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ── CSRF ────────────────────────────────────────────────────
    protected function generateCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('CSRF token mismatch. Please go back and try again.');
        }
    }

    // ── Auth helpers ────────────────────────────────────────────
    protected function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    protected function currentUser(): ?array
    {
        if (!$this->isLoggedIn()) return null;
        return Database::queryOne(
            'SELECT id, name, email, role, avatar_url, batch_id, profile_complete FROM users WHERE id = ? AND is_active = 1 AND deleted_at IS NULL',
            [$_SESSION['user_id']]
        );
    }

    protected function requireStudent(): array
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/google');
        }
        $user = $this->currentUser();
        if (!$user || $user['role'] !== ROLE_STUDENT) {
            $this->redirect('/auth/google');
        }
        // Force profile setup
        if (!$user['profile_complete']) {
            $this->redirect('/setup-profile');
        }
        return $user;
    }

    protected function requireAdmin(): array
    {
        if (empty($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
        }
        $user = Database::queryOne(
            'SELECT id, name, email, role FROM users WHERE id = ? AND role IN (\'admin\',\'super_admin\') AND is_active = 1',
            [$_SESSION['admin_id']]
        );
        if (!$user) {
            session_destroy();
            $this->redirect('/admin/login');
        }
        return $user;
    }

    protected function requireMentor(): array
    {
        if (empty($_SESSION['mentor_id'])) {
            $this->redirect('/mentor/login');
        }
        $user = Database::queryOne(
            'SELECT id, name, email, role FROM users WHERE id = ? AND role = \'mentor\' AND is_active = 1',
            [$_SESSION['mentor_id']]
        );
        if (!$user) {
            session_destroy();
            $this->redirect('/mentor/login');
        }
        return $user;
    }

    // ── Audit logging ────────────────────────────────────────────
    protected function audit(string $action, string $targetType = '', int $targetId = 0, array $detail = []): void
    {
        $userId = $_SESSION['admin_id'] ?? $_SESSION['mentor_id'] ?? $_SESSION['user_id'] ?? null;
        Database::execute(
            'INSERT INTO audit_logs (user_id, action, target_type, target_id, detail, ip) VALUES (?,?,?,?,?,?)',
            [$userId, $action, $targetType ?: null, $targetId ?: null, $detail ? json_encode($detail) : null, $_SERVER['REMOTE_ADDR'] ?? null]
        );
    }

    // ── Input helpers ────────────────────────────────────────────
    protected function input(string $key, mixed $default = ''): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
