<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class NotificationController extends BaseController
{
    public function poll(array $params = []): void
    {
        if (!$this->isLoggedIn()) {
            $this->json(['unread_count' => 0]);
        }

        $userId = $_SESSION['user_id'];

        $unreadCount = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM announcements a
             LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = ?
             WHERE a.deleted_at IS NULL AND ar.announcement_id IS NULL',
            [$userId]
        )['cnt'] ?? 0;

        $this->json(['unread_count' => (int)$unreadCount]);
    }
}
