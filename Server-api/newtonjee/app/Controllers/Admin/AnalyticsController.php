<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

class AnalyticsController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin = $this->requireAdmin();

        // ── Summary KPIs ─────────────────────────────────────────
        $kpi = [
            'students'        => Database::queryOne("SELECT COUNT(*) AS n FROM users WHERE role='student' AND is_active=1")['n'],
            'enrolled'        => Database::queryOne("SELECT COUNT(*) AS n FROM enrollments")['n'],
            'completed'       => Database::queryOne("SELECT COUNT(*) AS n FROM enrollments WHERE status='completed'")['n'],
            'certificates'    => Database::queryOne("SELECT COUNT(*) AS n FROM certificates WHERE revoked_at IS NULL")['n'],
            'submissions'     => Database::queryOne("SELECT COUNT(*) AS n FROM submissions")['n'],
            'avg_score'       => Database::queryOne("SELECT ROUND(AVG(score),1) AS n FROM submissions WHERE score IS NOT NULL")['n'],
            'pending_grades'  => Database::queryOne("SELECT COUNT(*) AS n FROM submissions WHERE score IS NULL")['n'],
            'active_courses'  => Database::queryOne("SELECT COUNT(*) AS n FROM courses WHERE is_published=1 AND deleted_at IS NULL")['n'],
        ];

        // ── Enrollments by course ─────────────────────────────────
        $courseStats = Database::query(
            'SELECT c.title, c.category,
                    COUNT(e.id) AS total_enrolled,
                    SUM(e.status=\'completed\') AS completed,
                    ROUND(AVG(e.progress_pct),1) AS avg_pct,
                    COUNT(DISTINCT s.id) AS total_submissions
             FROM courses c
             LEFT JOIN enrollments e ON e.course_id = c.id
             LEFT JOIN assignments a ON a.course_id = c.id
             LEFT JOIN submissions s ON s.assignment_id = a.id
             WHERE c.is_published=1 AND c.deleted_at IS NULL
             GROUP BY c.id
             ORDER BY total_enrolled DESC'
        );

        // ── Student activity (last 30 days) ───────────────────────
        $recentActivity = Database::query(
            "SELECT DATE(lp.completed_at) AS day, COUNT(*) AS lessons_completed
             FROM lesson_progress lp
             WHERE lp.is_completed=1
               AND lp.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(lp.completed_at)
             ORDER BY day ASC"
        );

        // ── Batch breakdown ───────────────────────────────────────
        $batchStats = Database::query(
            'SELECT b.name AS batch, COUNT(u.id) AS students,
                    ROUND(AVG(e.progress_pct),1) AS avg_progress
             FROM batches b
             LEFT JOIN users u ON u.batch_id = b.id AND u.role=\'student\'
             LEFT JOIN enrollments e ON e.user_id = u.id
             WHERE b.is_active=1
             GROUP BY b.id
             ORDER BY b.year DESC'
        );

        // ── Top students ──────────────────────────────────────────
        $topStudents = Database::query(
            'SELECT u.name, u.email, u.avatar_url, b.name AS batch,
                    COUNT(DISTINCT e.course_id) AS courses,
                    ROUND(AVG(e.progress_pct),1) AS avg_pct,
                    SUM(e.status=\'completed\') AS completed
             FROM users u
             LEFT JOIN enrollments e ON e.user_id = u.id
             LEFT JOIN batches b ON u.batch_id = b.id
             WHERE u.role=\'student\' AND u.is_active=1
             GROUP BY u.id
             HAVING courses > 0
             ORDER BY avg_pct DESC, completed DESC
             LIMIT 10'
        );

        $this->adminView('admin.analytics.index', compact(
            'admin', 'kpi', 'courseStats', 'recentActivity', 'batchStats', 'topStudents'
        ));
    }
}
