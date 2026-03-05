<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;
use App\Helpers\MailHelper;

class AnnouncementController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin         = $this->requireAdmin();
        $announcements = Database::query(
            'SELECT a.*, u.name AS creator_name FROM announcements a
             LEFT JOIN users u ON a.created_by = u.id
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC'
        );
        $this->adminView('admin.announcements.index', compact('admin', 'announcements'));
    }

    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin      = $this->requireAdmin();
        $title      = trim($_POST['title'] ?? '');
        $body       = trim($_POST['body']  ?? '');
        $type       = $_POST['type']       ?? 'update';
        $sendEmail  = isset($_POST['send_email']) ? 1 : 0;

        if (!$title || !$body) {
            $this->flash('error', 'Title and body are required.');
            $this->redirect('/admin/announcements');
        }

        $id = Database::insert(
            'INSERT INTO announcements (title, body, type, send_email, created_by) VALUES (?,?,?,?,?)',
            [$title, $body, $type, $sendEmail, $admin['id']]
        );

        if ($sendEmail) {
            // Queue email to all active students
            $students = Database::query(
                "SELECT name, email FROM users WHERE role='student' AND is_active=1 AND deleted_at IS NULL"
            );
            $html = "<h2>" . htmlspecialchars($title) . "</h2><p>" . nl2br(htmlspecialchars($body)) . "</p>";
            MailHelper::sendBulk($students, $title, $html);
        }

        $this->audit('announcement.create', 'announcement', (int)$id, ['title' => $title]);
        $this->flash('success', 'Announcement published.' . ($sendEmail ? ' Emails sent to all students.' : ''));
        $this->redirect('/admin/announcements');
    }

    public function delete(array $params = []): void
    {
        $this->verifyCsrf();
        $admin = $this->requireAdmin();
        $id    = (int)$params['id'];

        Database::execute('UPDATE announcements SET deleted_at = NOW() WHERE id = ?', [$id]);
        $this->audit('announcement.delete', 'announcement', $id);
        $this->flash('success', 'Announcement deleted.');
        $this->redirect('/admin/announcements');
    }
}
