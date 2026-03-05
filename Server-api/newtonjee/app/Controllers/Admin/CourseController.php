<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

class CourseController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $courses = Database::query(
            'SELECT c.*, u.name AS mentor_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled,
                    (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id) AS module_count
             FROM courses c
             LEFT JOIN users u ON c.mentor_id = u.id
             WHERE c.deleted_at IS NULL
             ORDER BY c.sort_order ASC, c.id DESC'
        );
        $this->adminView('admin.courses.index', compact('admin', 'courses'));
    }

    public function createForm(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $mentors = Database::query("SELECT id, name FROM users WHERE role = 'mentor' AND is_active = 1 ORDER BY name");
        $this->adminView('admin.courses.create', compact('admin', 'mentors'));
    }

    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin = $this->requireAdmin();

        $slug = $this->generateSlug(trim($_POST['title'] ?? ''));

        // Check slug uniqueness
        $existing = Database::queryOne('SELECT id FROM courses WHERE slug = ?', [$slug]);
        if ($existing) $slug .= '-' . time();

        $id = Database::insert(
            'INSERT INTO courses (title, slug, description, category, level, price, mentor_id, is_published, sort_order, prerequisites)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [
                trim($_POST['title']       ?? ''),
                $slug,
                trim($_POST['description'] ?? ''),
                $_POST['category']         ?? 'AI',
                $_POST['level']            ?? 'Beginner',
                (float)($_POST['price']    ?? 0),
                ($_POST['mentor_id']       ?? '') ?: null,
                isset($_POST['is_published']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0),
                $_POST['prerequisites']    ?? null,
            ]
        );

        $this->audit('course.create', 'course', (int)$id, ['title' => $_POST['title'] ?? '']);
        $this->flash('success', 'Course created successfully.');
        $this->redirect('/admin/courses');
    }

    public function editForm(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $course  = Database::queryOne('SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL', [(int)$params['id']]);
        $mentors = Database::query("SELECT id, name FROM users WHERE role = 'mentor' AND is_active = 1 ORDER BY name");
        if (!$course) $this->redirect('/admin/courses');
        $this->adminView('admin.courses.edit', compact('admin', 'course', 'mentors'));
    }

    public function edit(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $courseId = (int)$params['id'];

        Database::execute(
            'UPDATE courses SET title=?, description=?, category=?, level=?, price=?,
             mentor_id=?, is_published=?, sort_order=?, updated_at=NOW()
             WHERE id=? AND deleted_at IS NULL',
            [
                trim($_POST['title']       ?? ''),
                trim($_POST['description'] ?? ''),
                $_POST['category']         ?? 'AI',
                $_POST['level']            ?? 'Beginner',
                (float)($_POST['price']    ?? 0),
                ($_POST['mentor_id']       ?? '') ?: null,
                isset($_POST['is_published']) ? 1 : 0,
                (int)($_POST['sort_order'] ?? 0),
                $courseId,
            ]
        );

        $this->audit('course.edit', 'course', $courseId);
        $this->flash('success', 'Course updated.');
        $this->redirect('/admin/courses');
    }

    public function archive(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $courseId = (int)$params['id'];

        Database::execute(
            'UPDATE courses SET deleted_at = NOW() WHERE id = ?',
            [$courseId]
        );

        $this->audit('course.archive', 'course', $courseId);
        $this->flash('success', 'Course archived.');
        $this->redirect('/admin/courses');
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
