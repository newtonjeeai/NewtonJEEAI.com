<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

class DashboardController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin = $this->requireAdmin();

        $stats = [
            'total_students'    => Database::queryOne('SELECT COUNT(*) AS n FROM users WHERE role=\'student\' AND is_active=1 AND deleted_at IS NULL')['n'],
            'active_courses'    => Database::queryOne('SELECT COUNT(*) AS n FROM courses WHERE is_published=1 AND deleted_at IS NULL')['n'],
            'certificates'      => Database::queryOne('SELECT COUNT(*) AS n FROM certificates WHERE revoked_at IS NULL')['n'],
            'pending_grades'    => Database::queryOne('SELECT COUNT(*) AS n FROM submissions WHERE score IS NULL')['n'],
            'total_enrollments' => Database::queryOne('SELECT COUNT(*) AS n FROM enrollments')['n'],
            'avg_completion'    => Database::queryOne('SELECT ROUND(AVG(progress_pct),1) AS n FROM enrollments WHERE status=\'active\'')['n'] ?? 0,
        ];

        $recentStudents = Database::query(
            'SELECT u.id, u.name, u.email, u.avatar_url, b.name AS batch, u.created_at
             FROM users u LEFT JOIN batches b ON u.batch_id = b.id
             WHERE u.role=\'student\' AND u.deleted_at IS NULL
             ORDER BY u.created_at DESC LIMIT 8'
        );

        $recentSubmissions = Database::query(
            'SELECT s.id, u.name AS student, a.title AS assignment, c.title AS course, s.submitted_at
             FROM submissions s
             JOIN users u ON s.user_id = u.id
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE s.score IS NULL
             ORDER BY s.submitted_at DESC LIMIT 6'
        );

        $courseEngagement = Database::query(
            'SELECT c.title, c.category,
                    COUNT(e.id) AS enrolled,
                    ROUND(AVG(e.progress_pct),1) AS avg_progress,
                    SUM(e.status=\'completed\') AS completed
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             WHERE c.is_published=1 AND c.deleted_at IS NULL
             GROUP BY c.id ORDER BY enrolled DESC LIMIT 6'
        );

        $this->adminView('admin.dashboard', compact(
            'admin', 'stats', 'recentStudents', 'recentSubmissions', 'courseEngagement'
        ));
    }
}
