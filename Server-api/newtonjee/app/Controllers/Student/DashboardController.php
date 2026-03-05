<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class DashboardController extends BaseController
{
    public function index(array $params = []): void
    {
        $user = $this->requireStudent();

        // ── Active courses with progress ─────────────────────────
        $activeCourses = Database::query(
            'SELECT c.id, c.title, c.slug, c.category, c.thumbnail_url,
                    e.progress_pct, e.enrolled_at,
                    (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS total_modules,
                    (SELECT COUNT(*) FROM modules m
                     JOIN lessons l ON l.module_id = m.id
                     WHERE m.course_id = c.id) AS total_lessons
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             WHERE e.user_id = ? AND e.status = \'active\' AND c.deleted_at IS NULL
             ORDER BY e.enrolled_at DESC
             LIMIT 4',
            [$user['id']]
        );

        // ── Overall progress % ───────────────────────────────────
        $totalPct = 0;
        if (count($activeCourses) > 0) {
            $totalPct = (int) round(array_sum(array_column($activeCourses, 'progress_pct')) / count($activeCourses));
        }

        // ── Unread announcements ──────────────────────────────────
        $unreadCount = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM announcements a
             LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = ?
             WHERE a.deleted_at IS NULL AND ar.announcement_id IS NULL',
            [$user['id']]
        )['cnt'] ?? 0;

        $latestAnnouncements = Database::query(
            'SELECT a.id, a.title, a.type, a.created_at,
                    (ar.announcement_id IS NULL) AS is_unread
             FROM announcements a
             LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = ?
             WHERE a.deleted_at IS NULL
             ORDER BY a.created_at DESC
             LIMIT 3',
            [$user['id']]
        );

        // ── Today's calendar events ──────────────────────────────
        $todayEvents = Database::query(
            'SELECT ce.title, ce.tag, DATE_FORMAT(ce.starts_at, \'%h:%i %p\') AS time_fmt
             FROM calendar_events ce
             WHERE DATE(ce.starts_at) = CURDATE()
             ORDER BY ce.starts_at ASC
             LIMIT 5',
            []
        );

        // ── Upcoming exams ───────────────────────────────────────
        $upcomingExams = Database::query(
            'SELECT e.id, e.title, e.duration_min, DATE_FORMAT(e.scheduled_at, \'%b %d, %Y\') AS exam_date,
                    c.title AS course_title
             FROM exams e
             JOIN courses c ON e.course_id = c.id
             JOIN enrollments en ON en.course_id = c.id AND en.user_id = ?
             WHERE e.is_published = 1
               AND e.scheduled_at > NOW()
               AND (e.closes_at IS NULL OR e.closes_at > NOW())
             ORDER BY e.scheduled_at ASC
             LIMIT 3',
            [$user['id']]
        );

        // ── Badges (static config for Phase 1) ──────────────────
        $badges = $this->computeBadges($user['id']);

        // ── Hours learned (estimated: lessons completed × avg duration) ──
        $hoursRow = Database::queryOne(
            'SELECT COALESCE(SUM(COALESCE(l.duration_min, 10)), 0) AS total_min
             FROM lesson_progress lp
             JOIN lessons l ON lp.lesson_id = l.id
             WHERE lp.user_id = ? AND lp.is_completed = 1',
            [$user['id']]
        );
        $hoursLearned = round(($hoursRow['total_min'] ?? 0) / 60, 1);

        $this->view('student.dashboard', compact(
            'user', 'activeCourses', 'totalPct', 'unreadCount',
            'latestAnnouncements', 'todayEvents', 'upcomingExams',
            'badges', 'hoursLearned'
        ));
    }

    private function computeBadges(int $userId): array
    {
        $completedCourses = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM enrollments WHERE user_id = ? AND status = \'completed\'',
            [$userId]
        )['cnt'] ?? 0;

        $completedLessons = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM lesson_progress WHERE user_id = ? AND is_completed = 1',
            [$userId]
        )['cnt'] ?? 0;

        $aiCompleted = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             WHERE e.user_id = ? AND e.status = \'completed\' AND c.category = \'AI\'',
            [$userId]
        )['cnt'] ?? 0;

        $roboticsCompleted = Database::queryOne(
            'SELECT COUNT(*) AS cnt FROM enrollments e
             JOIN courses c ON e.course_id = c.id
             WHERE e.user_id = ? AND e.status = \'completed\' AND c.category = \'Robotics\'',
            [$userId]
        )['cnt'] ?? 0;

        return [
            ['label' => 'AI Novice',     'icon' => '🌱', 'earned' => $completedLessons >= 5],
            ['label' => 'Code Sprinter', 'icon' => '⚡', 'earned' => $completedLessons >= 20],
            ['label' => 'Bot Builder',   'icon' => '🤖', 'earned' => $roboticsCompleted >= 1],
            ['label' => 'AI Developer',  'icon' => '🧠', 'earned' => $aiCompleted >= 2],
            ['label' => 'ML Engineer',   'icon' => '🔬', 'earned' => $completedCourses >= 4],
        ];
    }
}
