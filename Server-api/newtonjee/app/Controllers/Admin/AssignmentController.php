<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;

class AssignmentController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin = $this->requireAdmin();
        $assignments = Database::query(
            'SELECT a.*, c.title AS course_title,
                    COUNT(s.id) AS total_submissions,
                    SUM(s.score IS NULL AND s.id IS NOT NULL) AS pending_grades
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             LEFT JOIN submissions s ON s.assignment_id = a.id
             WHERE c.deleted_at IS NULL
             GROUP BY a.id ORDER BY a.deadline DESC'
        );
        $this->adminView('admin.assignments.index', compact('admin', 'assignments'));
    }

    public function createForm(array $params = []): void
    {
        $admin   = $this->requireAdmin();
        $courses = Database::query('SELECT id, title FROM courses WHERE is_published=1 AND deleted_at IS NULL ORDER BY title');
        $this->adminView('admin.assignments.create', compact('admin', 'courses'));
    }

    public function create(array $params = []): void
    {
        $this->verifyCsrf();
        $admin = $this->requireAdmin();

        $courseId       = (int) ($_POST['course_id'] ?? 0);
        $title          = trim($_POST['title'] ?? '');
        $description    = trim($_POST['description'] ?? '');
        $deadline       = $_POST['deadline'] ?? '';
        $maxScore       = (int) ($_POST['max_score'] ?? 100);
        $allowResubmit  = isset($_POST['allow_resubmit']) ? 1 : 0;
        $colabUrl       = trim($_POST['colab_url'] ?? '');
        $submissionType = $_POST['submission_type'] ?? 'drive_link';
        $isPublished    = isset($_POST['is_published']) ? 1 : 0;

        if (!$courseId || !$title || !$deadline) {
            $this->flash('error', 'Course, title, and deadline are required.');
            $this->redirect('/admin/assignments/create');
        }

        // Handle notebook upload
        $notebookPath     = null;
        $notebookFilename = null;

        if (isset($_FILES['notebook_file']) && $_FILES['notebook_file']['error'] === UPLOAD_ERR_OK) {
            $result = $this->saveNotebook($_FILES['notebook_file'], $courseId);
            if ($result === false) {
                $this->flash('error', 'Notebook upload failed. Only .ipynb files are accepted (max 25MB).');
                $this->redirect('/admin/assignments/create');
            }
            [$notebookPath, $notebookFilename] = $result;
        }

        $id = Database::insert(
            'INSERT INTO assignments (course_id, title, description, deadline, max_score, allow_resubmit,
             notebook_path, notebook_filename, colab_url, submission_type, is_published, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [$courseId, $title, $description, $deadline, $maxScore, $allowResubmit,
             $notebookPath, $notebookFilename, $colabUrl ?: null, $submissionType, $isPublished, $admin['id']]
        );

        $this->audit('assignment.create', 'assignment', (int)$id, ['title' => $title]);
        $this->flash('success', 'Assignment created successfully.');
        $this->redirect('/admin/assignments');
    }

    public function editForm(array $params = []): void
    {
        $admin      = $this->requireAdmin();
        $assignment = Database::queryOne('SELECT * FROM assignments WHERE id = ?', [(int)$params['id']]);
        $courses    = Database::query('SELECT id, title FROM courses WHERE is_published=1 AND deleted_at IS NULL ORDER BY title');
        if (!$assignment) { $this->redirect('/admin/assignments'); }
        $this->adminView('admin.assignments.edit', compact('admin', 'assignment', 'courses'));
    }

    public function edit(array $params = []): void
    {
        $this->verifyCsrf();
        $admin        = $this->requireAdmin();
        $assignmentId = (int) $params['id'];

        $assignment = Database::queryOne('SELECT * FROM assignments WHERE id = ?', [$assignmentId]);
        if (!$assignment) { $this->redirect('/admin/assignments'); }

        $notebookPath     = $assignment['notebook_path'];
        $notebookFilename = $assignment['notebook_filename'];

        if (isset($_FILES['notebook_file']) && $_FILES['notebook_file']['error'] === UPLOAD_ERR_OK) {
            $result = $this->saveNotebook($_FILES['notebook_file'], $assignment['course_id']);
            if ($result !== false) {
                // Delete old notebook
                if ($notebookPath && file_exists($notebookPath)) unlink($notebookPath);
                [$notebookPath, $notebookFilename] = $result;
            }
        }

        Database::execute(
            'UPDATE assignments SET title=?, description=?, deadline=?, max_score=?, allow_resubmit=?,
             notebook_path=?, notebook_filename=?, colab_url=?, submission_type=?, is_published=?
             WHERE id=?',
            [
                trim($_POST['title'] ?? $assignment['title']),
                trim($_POST['description'] ?? ''),
                $_POST['deadline'] ?? $assignment['deadline'],
                (int)($_POST['max_score'] ?? $assignment['max_score']),
                isset($_POST['allow_resubmit']) ? 1 : 0,
                $notebookPath,
                $notebookFilename,
                trim($_POST['colab_url'] ?? '') ?: null,
                $_POST['submission_type'] ?? $assignment['submission_type'],
                isset($_POST['is_published']) ? 1 : 0,
                $assignmentId,
            ]
        );

        $this->audit('assignment.edit', 'assignment', $assignmentId);
        $this->flash('success', 'Assignment updated.');
        $this->redirect('/admin/assignments');
    }

    // ── All Submissions ──────────────────────────────────────────
    public function submissions(array $params = []): void
    {
        $admin  = $this->requireAdmin();
        $filter = $_GET['filter'] ?? 'pending';

        $sql  = 'SELECT s.id, s.submission_type, s.drive_url, s.file_name, s.score,
                        s.submitted_at, s.graded_at, s.drive_shared_confirmed,
                        u.name AS student_name, u.email AS student_email,
                        a.title AS assignment_title, a.max_score,
                        c.title AS course_title
                 FROM submissions s
                 JOIN users u ON s.user_id = u.id
                 JOIN assignments a ON s.assignment_id = a.id
                 JOIN courses c ON a.course_id = c.id
                 WHERE 1=1';
        $bind = [];

        if ($filter === 'pending') { $sql .= ' AND s.score IS NULL'; }
        if ($filter === 'graded')  { $sql .= ' AND s.score IS NOT NULL'; }

        $sql .= ' ORDER BY s.submitted_at DESC';

        $submissions = Database::query($sql, $bind);
        $this->adminView('admin.assignments.submissions', compact('admin', 'submissions', 'filter'));
    }

    public function submissionDetail(array $params = []): void
    {
        $admin      = $this->requireAdmin();
        $submission = Database::queryOne(
            'SELECT s.*, u.name AS student_name, u.email,
                    a.title AS assignment_title, a.max_score, a.description AS assignment_desc,
                    c.title AS course_title,
                    gu.name AS graded_by_name
             FROM submissions s
             JOIN users u ON s.user_id = u.id
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             LEFT JOIN users gu ON s.graded_by = gu.id
             WHERE s.id = ?',
            [(int)$params['id']]
        );
        if (!$submission) { $this->redirect('/admin/submissions'); }
        $this->adminView('admin.assignments.submission_detail', compact('admin', 'submission'));
    }

    // ── Grade a Submission ───────────────────────────────────────
    public function grade(array $params = []): void
    {
        $this->verifyCsrf();
        $admin        = $this->requireAdmin();
        $submissionId = (int) $params['id'];

        $submission = Database::queryOne(
            'SELECT s.*, a.max_score FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE s.id = ?',
            [$submissionId]
        );

        if (!$submission) {
            $this->flash('error', 'Submission not found.');
            $this->redirect('/admin/submissions');
        }

        $score    = (int) ($_POST['score'] ?? 0);
        $feedback = trim($_POST['feedback'] ?? '');

        if ($score < 0 || $score > $submission['max_score']) {
            $this->flash('error', "Score must be between 0 and {$submission['max_score']}.");
            $this->redirect('/admin/submissions/' . $submissionId);
        }

        Database::execute(
            'UPDATE submissions SET score=?, feedback=?, graded_by=?, graded_at=NOW() WHERE id=?',
            [$score, $feedback, $admin['id'], $submissionId]
        );

        $this->audit('submission.grade', 'submission', $submissionId, ['score' => $score]);
        $this->flash('success', 'Submission graded successfully.');
        $this->redirect('/admin/submissions');
    }

    // ── Private: save notebook file securely ─────────────────────
    private function saveNotebook(array $file, int $courseId): array|false
    {
        $origName = basename($file['name']);
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if ($ext !== 'ipynb') return false;
        if ($file['size'] > NOTEBOOK_MAX_BYTES) return false;

        // Validate JSON structure of notebook
        $content = file_get_contents($file['tmp_name']);
        $json    = json_decode($content, true);
        if (!$json || !isset($json['nbformat'])) return false;

        $dir = NOTEBOOK_PATH . '/starters/' . $courseId;
        if (!is_dir($dir)) mkdir($dir, 0750, true);

        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
        $destPath = $dir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) return false;

        return [$destPath, $origName];
    }
}
