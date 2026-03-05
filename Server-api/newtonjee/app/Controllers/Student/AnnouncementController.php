<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class AnnouncementController extends BaseController
{
    public function index(array $params = []): void
    {
        $user = $this->requireStudent();

        $announcements = Database::query(
            'SELECT a.id, a.title, a.body, a.type, a.created_at,
                    (ar.announcement_id IS NULL) AS is_unread
             FROM announcements a
             LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = ?
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC',
            [$user['id']]
        );

        $unreadCount = count(array_filter($announcements, fn($a) => $a['is_unread']));

        $this->view('student.announcements.index', compact('user', 'announcements', 'unreadCount'));
    }

    public function markRead(array $params = []): void
    {
        $user = $this->requireStudent();
        $id   = (int) $params['id'];

        Database::execute(
            'INSERT IGNORE INTO announcement_reads (user_id, announcement_id) VALUES (?,?)',
            [$user['id'], $id]
        );

        $this->json(['ok' => true]);
    }

    public function markAllRead(array $params = []): void
    {
        $this->verifyCsrf();
        $user = $this->requireStudent();

        Database::execute(
            'INSERT IGNORE INTO announcement_reads (user_id, announcement_id)
             SELECT ?, id FROM announcements WHERE deleted_at IS NULL',
            [$user['id']]
        );

        $this->flash('success', 'All announcements marked as read.');
        $this->redirect('/announcements');
    }
}
