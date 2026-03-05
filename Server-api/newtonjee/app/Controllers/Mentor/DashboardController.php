<?php

declare(strict_types=1);

namespace App\Controllers\Mentor;

use App\Controllers\BaseController;
use App\Database;

class DashboardController extends BaseController
{
    public function index(array $params = []): void
    {
        $mentor = $this->requireMentor();

        $courses = Database::query(
            'SELECT c.id, c.title, c.category,
                    COUNT(DISTINCT e.id) AS enrolled,
                    ROUND(AVG(e.progress_pct),1) AS avg_progress
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             WHERE c.mentor_id = ? AND c.deleted_at IS NULL
             GROUP BY c.id',
            [$mentor['id']]
        );

        $pendingSubmissions = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM submissions s
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE c.mentor_id = ? AND s.score IS NULL',
            [$mentor['id']]
        )['cnt'] ?? 0;

        $recentSubmissions = Database::query(
            'SELECT s.id, u.name AS student, a.title AS assignment, s.submitted_at, s.drive_url
             FROM submissions s
             JOIN users u ON s.user_id = u.id
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE c.mentor_id = ? AND s.score IS NULL
             ORDER BY s.submitted_at DESC LIMIT 5',
            [$mentor['id']]
        );

        $this->mentorView('mentor.dashboard', compact('mentor', 'courses', 'pendingSubmissions', 'recentSubmissions'));
    }
}
