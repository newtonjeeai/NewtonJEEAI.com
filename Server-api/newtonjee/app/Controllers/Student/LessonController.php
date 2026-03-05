<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class LessonController extends BaseController
{
    public function show(array $params = []): void
    {
        $user       = $this->requireStudent();
        $courseSlug = $params['courseSlug'];
        $lessonId   = (int)$params['lessonId'];

        $course = Database::queryOne(
            'SELECT c.id, c.title, c.slug, c.category
             FROM courses c
             JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
             WHERE c.slug = ? AND c.is_published = 1 AND c.deleted_at IS NULL',
            [$user['id'], $courseSlug]
        );

        if (!$course) {
            $this->flash('error', 'You are not enrolled in this course.');
            $this->redirect('/my-courses');
        }

        $lesson = Database::queryOne(
            'SELECT l.*, m.title AS module_title, m.course_id
             FROM lessons l
             JOIN modules m ON l.module_id = m.id
             WHERE l.id = ? AND m.course_id = ?',
            [$lessonId, $course['id']]
        );

        if (!$lesson) {
            $this->flash('error', 'Lesson not found.');
            $this->redirect('/my-courses');
        }

        // Lesson progress
        $progress = Database::queryOne(
            'SELECT is_completed, last_position FROM lesson_progress WHERE user_id = ? AND lesson_id = ?',
            [$user['id'], $lessonId]
        );

        // All lessons in course for sidebar navigation
        $allLessons = Database::query(
            'SELECT l.id, l.title, l.type, l.duration_min, l.sort_order,
                    m.id AS module_id, m.title AS module_title, m.sort_order AS m_order,
                    COALESCE(lp.is_completed, 0) AS is_completed
             FROM lessons l
             JOIN modules m ON l.module_id = m.id
             LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ?
             WHERE m.course_id = ?
             ORDER BY m.sort_order ASC, l.sort_order ASC',
            [$user['id'], $course['id']]
        );

        // Prev/next lesson
        $flat = array_values($allLessons);
        $currentIndex = array_search($lessonId, array_column($flat, 'id'));
        $prevLesson   = $currentIndex > 0              ? $flat[$currentIndex - 1] : null;
        $nextLesson   = $currentIndex < count($flat)-1 ? $flat[$currentIndex + 1] : null;

        $this->view('student.learn.lesson', compact(
            'user', 'course', 'lesson', 'progress', 'allLessons', 'prevLesson', 'nextLesson'
        ));
    }

    public function markComplete(array $params = []): void
    {
        $user     = $this->requireStudent();
        $lessonId = (int)$params['lessonId'];
        $position = (int)($_POST['position'] ?? 0);

        // Verify the student is enrolled in the course containing this lesson
        $lesson = Database::queryOne(
            'SELECT l.id, m.course_id FROM lessons l JOIN modules m ON l.module_id = m.id WHERE l.id = ?',
            [$lessonId]
        );

        if (!$lesson) {
            $this->json(['ok' => false, 'error' => 'Lesson not found'], 404);
        }

        $enrollment = Database::queryOne(
            'SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?',
            [$user['id'], $lesson['course_id']]
        );

        if (!$enrollment) {
            $this->json(['ok' => false, 'error' => 'Not enrolled'], 403);
        }

        // Upsert lesson progress
        Database::execute(
            'INSERT INTO lesson_progress (user_id, lesson_id, is_completed, last_position, completed_at)
             VALUES (?,?,1,?,NOW())
             ON DUPLICATE KEY UPDATE is_completed=1, last_position=?, completed_at=IF(is_completed=0,NOW(),completed_at)',
            [$user['id'], $lessonId, $position, $position]
        );

        // Recalculate course progress
        $this->updateCourseProgress($user['id'], $lesson['course_id']);

        $this->json(['ok' => true]);
    }

    private function updateCourseProgress(int $userId, int $courseId): void
    {
        $totals = Database::queryOne(
            'SELECT COUNT(*) AS total,
                    SUM(lp.is_completed = 1) AS completed
             FROM lessons l
             JOIN modules m ON l.module_id = m.id
             LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ?
             WHERE m.course_id = ?',
            [$userId, $courseId]
        );

        $total     = (int)($totals['total']     ?? 0);
        $completed = (int)($totals['completed'] ?? 0);
        $pct       = $total > 0 ? (int)round(($completed / $total) * 100) : 0;
        $status    = $pct >= 100 ? 'completed' : 'active';
        $completedAt = $pct >= 100 ? 'NOW()' : 'NULL';

        Database::execute(
            "UPDATE enrollments SET progress_pct = ?, status = ?, completed_at = IF(status='completed' AND ? >= 100, NOW(), completed_at)
             WHERE user_id = ? AND course_id = ?",
            [$pct, $status, $pct, $userId, $courseId]
        );
    }
}
