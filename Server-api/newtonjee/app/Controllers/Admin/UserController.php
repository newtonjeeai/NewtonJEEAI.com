<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

class UserController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin  = $this->requireAdmin();
        $role   = $_GET['role']   ?? 'student';
        $search = trim($_GET['q'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $sql  = "SELECT u.*, b.name AS batch_name
                 FROM users u
                 LEFT JOIN batches b ON u.batch_id = b.id
                 WHERE u.deleted_at IS NULL AND u.role = ?";
        $bind = [$role];

        if ($search) {
            $sql  .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $bind[] = "%$search%";
            $bind[] = "%$search%";
        }

        $totalRow = Database::queryOne("SELECT COUNT(*) AS n FROM ($sql) t", $bind);
        $total    = (int)($totalRow['n'] ?? 0);

        $sql .= ' ORDER BY u.created_at DESC LIMIT ? OFFSET ?';
        $bind[] = $perPage;
        $bind[] = $offset;

        $users   = Database::query($sql, $bind);
        $batches = Database::query('SELECT * FROM batches WHERE is_active = 1 ORDER BY year DESC');
        $pages   = (int)ceil($total / $perPage);

        $this->adminView('admin.users.index', compact(
            'admin', 'users', 'role', 'search', 'page', 'pages', 'total', 'batches'
        ));
    }

    public function show(array $params = []): void
    {
        $admin  = $this->requireAdmin();
        $userId = (int)$params['id'];

        $user = Database::queryOne(
            'SELECT u.*, b.name AS batch_name FROM users u LEFT JOIN batches b ON u.batch_id = b.id WHERE u.id = ?',
            [$userId]
        );
        if (!$user) $this->redirect('/admin/users');

        $enrollments = Database::query(
            'SELECT c.title, c.category, e.progress_pct, e.status, e.enrolled_at
             FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ?',
            [$userId]
        );

        $certificates = Database::query(
            'SELECT cert.verify_token, cert.issued_at, c.title AS course_title
             FROM certificates cert JOIN courses c ON cert.course_id = c.id
             WHERE cert.user_id = ? AND cert.revoked_at IS NULL',
            [$userId]
        );

        $submissions = Database::query(
            'SELECT s.score, s.submitted_at, a.title AS assignment, s.submission_type
             FROM submissions s JOIN assignments a ON s.assignment_id = a.id
             WHERE s.user_id = ? ORDER BY s.submitted_at DESC LIMIT 10',
            [$userId]
        );

        $this->adminView('admin.users.show', compact('admin', 'user', 'enrollments', 'certificates', 'submissions'));
    }

    public function createForm(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $batches = Database::query('SELECT * FROM batches WHERE is_active = 1 ORDER BY year DESC');
        $this->adminView('admin.users.create', compact('admin', 'batches'));
    }

    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin = $this->requireAdmin();

        $role     = $_POST['role'] ?? ROLE_STUDENT;
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $name     = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$name) {
            $this->flash('error', 'Name and email are required.');
            $this->redirect('/admin/users/create');
        }

        $existing = Database::queryOne('SELECT id FROM users WHERE email = ?', [$email]);
        if ($existing) {
            $this->flash('error', 'A user with this email already exists.');
            $this->redirect('/admin/users/create');
        }

        $passwordHash = null;
        if (in_array($role, [ROLE_ADMIN, ROLE_MENTOR, ROLE_SUPER_ADMIN], true)) {
            if (strlen($password) < 8) {
                $this->flash('error', 'Admin/Mentor accounts require a password of at least 8 characters.');
                $this->redirect('/admin/users/create');
            }
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $id = Database::insert(
            'INSERT INTO users (name, email, password, role, batch_id, profile_complete) VALUES (?,?,?,?,?,?)',
            [
                $name,
                $email,
                $passwordHash,
                $role,
                ($_POST['batch_id'] ?? '') ?: null,
                $role === ROLE_STUDENT ? 0 : 1,
            ]
        );

        $this->audit('user.create', 'user', (int)$id, ['email' => $email, 'role' => $role]);
        $this->flash('success', "User {$name} created successfully.");
        $this->redirect('/admin/users');
    }

    public function editForm(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $userId  = (int)$params['id'];
        $user    = Database::queryOne('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$userId]);
        $batches = Database::query('SELECT * FROM batches WHERE is_active = 1 ORDER BY year DESC');
        if (!$user) $this->redirect('/admin/users');
        $this->adminView('admin.users.edit', compact('admin', 'user', 'batches'));
    }

    public function edit(array $params = []): void
    {
        $this->verifyCsrf();
        $admin  = $this->requireAdmin();
        $userId = (int)$params['id'];

        $user = Database::queryOne('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL', [$userId]);
        if (!$user) $this->redirect('/admin/users');

        $updates  = [
            'name'     => trim($_POST['name'] ?? $user['name']),
            'batch_id' => ($_POST['batch_id'] ?? '') ?: null,
            'is_active'=> isset($_POST['is_active']) ? 1 : 0,
        ];

        // Update password only for admin/mentor if provided
        if (in_array($user['role'], [ROLE_ADMIN, ROLE_MENTOR, ROLE_SUPER_ADMIN], true)) {
            $newPass = $_POST['new_password'] ?? '';
            if ($newPass !== '') {
                if (strlen($newPass) < 8) {
                    $this->flash('error', 'Password must be at least 8 characters.');
                    $this->redirect('/admin/users/' . $userId . '/edit');
                }
                $updates['password'] = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            }
        }

        $setClauses = implode(', ', array_map(fn($k) => "$k = ?", array_keys($updates)));
        $values     = array_values($updates);
        $values[]   = $userId;

        Database::execute("UPDATE users SET $setClauses, updated_at = NOW() WHERE id = ?", $values);

        $this->audit('user.edit', 'user', $userId);
        $this->flash('success', 'User updated.');
        $this->redirect('/admin/users');
    }

    public function deactivate(array $params = []): void
    {
        $this->verifyCsrf();
        $admin  = $this->requireAdmin();
        $userId = (int)$params['id'];

        // Can't deactivate yourself
        if ($userId === (int)$_SESSION['admin_id']) {
            $this->flash('error', 'You cannot deactivate your own account.');
            $this->redirect('/admin/users');
        }

        Database::execute(
            'UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?',
            [$userId]
        );

        $this->audit('user.deactivate', 'user', $userId);
        $this->flash('success', 'User account deactivated.');
        $this->redirect('/admin/users');
    }
}
