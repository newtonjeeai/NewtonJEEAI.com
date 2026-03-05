<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

// ── Module Controller ──────────────────────────────────────────
class ModuleController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin    = $this->requireAdmin();
        $courseId = (int)$params['id'];

        $course = Database::queryOne(
            'SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL',
            [$courseId]
        );
        if (!$course) $this->redirect('/admin/courses');

        $modules = Database::query(
            'SELECT m.*,
                    (SELECT COUNT(*) FROM lessons l WHERE l.module_id = m.id) AS lesson_count
             FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order ASC',
            [$courseId]
        );

        // Fetch lessons for each module
        foreach ($modules as &$m) {
            $m['lessons'] = Database::query(
                'SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order ASC',
                [$m['id']]
            );
        }

        $this->adminView('admin.courses.modules', compact('admin', 'course', 'modules'));
    }

    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $courseId = (int)($_POST['course_id'] ?? 0);

        if (!$courseId) {
            $this->flash('error', 'Invalid course.');
            $this->redirect('/admin/courses');
        }

        Database::insert(
            'INSERT INTO modules (course_id, title, description, sort_order) VALUES (?,?,?,?)',
            [
                $courseId,
                trim($_POST['title']       ?? ''),
                trim($_POST['description'] ?? ''),
                (int)($_POST['sort_order'] ?? 0),
            ]
        );

        $this->flash('success', 'Module added.');
        $this->redirect('/admin/courses/' . $courseId . '/modules');
    }

    public function edit(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $moduleId = (int)$params['id'];

        $module = Database::queryOne('SELECT * FROM modules WHERE id = ?', [$moduleId]);
        if (!$module) $this->redirect('/admin/courses');

        Database::execute(
            'UPDATE modules SET title=?, description=?, sort_order=? WHERE id=?',
            [
                trim($_POST['title']       ?? $module['title']),
                trim($_POST['description'] ?? ''),
                (int)($_POST['sort_order'] ?? $module['sort_order']),
                $moduleId,
            ]
        );

        $this->flash('success', 'Module updated.');
        $this->redirect('/admin/courses/' . $module['course_id'] . '/modules');
    }
}

// ── Lesson Controller ──────────────────────────────────────────
class LessonController extends BaseController
{
    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $moduleId = (int)$params['id'];

        $module = Database::queryOne('SELECT * FROM modules WHERE id = ?', [$moduleId]);
        if (!$module) $this->redirect('/admin/courses');

        $type       = $_POST['type']        ?? 'video';
        $contentUrl = trim($_POST['content_url']  ?? '');

        // Normalise YouTube URL to embed format
        if ($type === 'video' && $contentUrl) {
            $contentUrl = $this->normaliseYoutubeUrl($contentUrl);
        }

        Database::insert(
            'INSERT INTO lessons (module_id, title, type, content_url, content_text, duration_min, sort_order, is_free_preview)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $moduleId,
                trim($_POST['title'] ?? ''),
                $type,
                $contentUrl ?: null,
                trim($_POST['content_text'] ?? '') ?: null,
                ($_POST['duration_min'] ?? '') !== '' ? (int)$_POST['duration_min'] : null,
                (int)($_POST['sort_order'] ?? 0),
                isset($_POST['is_free_preview']) ? 1 : 0,
            ]
        );

        $this->flash('success', 'Lesson added.');
        $this->redirect('/admin/courses/' . $module['course_id'] . '/modules');
    }

    public function edit(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $lessonId = (int)$params['id'];

        $lesson = Database::queryOne('SELECT * FROM lessons WHERE id = ?', [$lessonId]);
        if (!$lesson) $this->redirect('/admin/courses');

        $contentUrl = trim($_POST['content_url'] ?? $lesson['content_url'] ?? '');
        if (($lesson['type'] === 'video') && $contentUrl) {
            $contentUrl = $this->normaliseYoutubeUrl($contentUrl);
        }

        Database::execute(
            'UPDATE lessons SET title=?, content_url=?, content_text=?, duration_min=?, sort_order=?, is_free_preview=?
             WHERE id=?',
            [
                trim($_POST['title']       ?? $lesson['title']),
                $contentUrl ?: null,
                trim($_POST['content_text'] ?? '') ?: null,
                ($_POST['duration_min'] ?? '') !== '' ? (int)$_POST['duration_min'] : null,
                (int)($_POST['sort_order'] ?? $lesson['sort_order']),
                isset($_POST['is_free_preview']) ? 1 : 0,
                $lessonId,
            ]
        );

        $module = Database::queryOne('SELECT course_id FROM modules WHERE id = ?', [$lesson['module_id']]);
        $this->flash('success', 'Lesson updated.');
        $this->redirect('/admin/courses/' . ($module['course_id'] ?? '') . '/modules');
    }

    /**
     * Convert any YouTube URL format to the embed URL.
     * Supports: watch?v=, youtu.be/, shorts/, embed/
     */
    private function normaliseYoutubeUrl(string $url): string
    {
        // Already an embed URL
        if (str_contains($url, 'youtube.com/embed/')) return $url;

        // Extract video ID
        $videoId = null;

        if (preg_match('/(?:v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $videoId = $m[1];
        }

        if ($videoId) {
            return 'https://www.youtube.com/embed/' . $videoId . '?rel=0&modestbranding=1';
        }

        // Return as-is if can't parse
        return $url;
    }
}
