<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class CourseController extends BaseController
{
    // ── Course Catalog ───────────────────────────────────────────
    public function catalog(array $params = []): void
    {
        $user     = $this->requireStudent();
        $category = $_GET['category'] ?? 'All';
        $level    = $_GET['level']    ?? 'All';
        $search   = trim($_GET['search'] ?? '');

        $sql  = 'SELECT c.*, u.name AS mentor_name,
                        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_count,
                        (SELECT AVG(r.rating) FROM course_ratings r WHERE r.course_id = c.id) AS avg_rating,
                        (en.id IS NOT NULL) AS is_enrolled,
                        (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count
                 FROM courses c
                 LEFT JOIN users u ON c.mentor_id = u.id
                 LEFT JOIN enrollments en ON en.course_id = c.id AND en.user_id = ?
                 WHERE c.is_published = 1 AND c.deleted_at IS NULL';
        $bind = [$user['id']];

        if ($category !== 'All') { $sql .= ' AND c.category = ?'; $bind[] = $category; }
        if ($level    !== 'All') { $sql .= ' AND c.level = ?';    $bind[] = $level;    }
        if ($search !== '')      { $sql .= ' AND c.title LIKE ?'; $bind[] = "%$search%"; }

        $sql .= ' ORDER BY c.sort_order ASC, c.id ASC';

        $courses = Database::query($sql, $bind);

        $this->view('student.courses.catalog', compact('user', 'courses', 'category', 'level', 'search'));
    }

    // ── Course Preview (detail page + enroll button) ─────────────
    public function preview(array $params = []): void
    {
        $user = $this->requireStudent();
        $slug = $params['slug'];

        $course = Database::queryOne(
            'SELECT c.*, u.name AS mentor_name, u.avatar_url AS mentor_avatar,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_count,
                    (en.id IS NOT NULL) AS is_enrolled,
                    en.progress_pct
             FROM courses c
             LEFT JOIN users u ON c.mentor_id = u.id
             LEFT JOIN enrollments en ON en.course_id = c.id AND en.user_id = ?
             WHERE c.slug = ? AND c.is_published = 1 AND c.deleted_at IS NULL',
            [$user['id'], $slug]
        );

        if (!$course) {
            http_response_code(404);
            $this->view('errors.404'); return;
        }

        $modules = Database::query(
            'SELECT m.*, 
                    (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS lesson_count,
                    (SELECT COUNT(*) FROM lessons l
                     JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = ? AND lp.is_completed = 1
                     WHERE l.module_id = m.id) AS completed_count
             FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order ASC',
            [$user['id'], $course['id']]
        );

        $this->view('student.courses.preview', compact('user', 'course', 'modules'));
    }

    // ── Enroll ───────────────────────────────────────────────────
    public function enroll(array $params = []): void
    {
        $this->verifyCsrf();
        $user     = $this->requireStudent();
        $courseId = (int) $params['id'];

        $course = Database::queryOne(
            'SELECT id, title, price FROM courses WHERE id = ? AND is_published = 1 AND deleted_at IS NULL',
            [$courseId]
        );

        if (!$course) {
            $this->flash('error', 'Course not found.');
            $this->redirect('/courses');
        }

        // Paid courses deferred to Phase 2
        if ((float)$course['price'] > 0) {
            $this->flash('info', 'Paid enrollment is coming soon. Please contact admin.');
            $this->redirect('/courses/' . $courseId);
        }

        // Check already enrolled
        $existing = Database::queryOne(
            'SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?',
            [$user['id'], $courseId]
        );

        if ($existing) {
            $this->flash('info', 'You are already enrolled in this course.');
            $this->redirect('/my-courses');
        }

        Database::insert(
            'INSERT INTO enrollments (user_id, course_id, status) VALUES (?,?,\'active\')',
            [$user['id'], $courseId]
        );

        $this->flash('success', 'Enrolled in "' . htmlspecialchars($course['title']) . '" successfully!');
        $this->redirect('/my-courses');
    }

    // ── My Courses ───────────────────────────────────────────────
    public function myCourses(array $params = []): void
    {
        $user = $this->requireStudent();

        $courses = Database::query(
            'SELECT c.id, c.title, c.slug, c.category, c.level, c.thumbnail_url,
                    e.progress_pct, e.status, e.enrolled_at, e.completed_at,
                    u.name AS mentor_name,
                    (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS total_modules,
                    (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) AS total_lessons,
                    (SELECT COUNT(*) FROM lesson_progress lp
                     JOIN lessons l ON lp.lesson_id = l.id
                     JOIN modules m ON l.module_id = m.id
                     WHERE m.course_id = c.id AND lp.user_id = e.user_id AND lp.is_completed = 1) AS completed_lessons
             FROM enrollments e
             JOIN courses c ON e.course_id = c.id AND c.deleted_at IS NULL
             LEFT JOIN users u ON c.mentor_id = u.id
             WHERE e.user_id = ?
             ORDER BY e.enrolled_at DESC',
            [$user['id']]
        );

        $this->view('student.courses.my_courses', compact('user', 'courses'));
    }
}
