<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class AssignmentController extends BaseController
{
    // ── Assignment List ──────────────────────────────────────────
    public function index(array $params = []): void
    {
        $user = $this->requireStudent();

        $assignments = Database::query(
            'SELECT a.id, a.title, a.deadline, a.max_score, a.submission_type,
                    a.notebook_path IS NOT NULL AS has_notebook,
                    a.colab_url,
                    c.title AS course_title, c.slug AS course_slug,
                    s.id AS submission_id,
                    s.score, s.submitted_at, s.drive_url,
                    CASE
                        WHEN s.id IS NOT NULL AND s.score IS NOT NULL THEN \'graded\'
                        WHEN s.id IS NOT NULL THEN \'submitted\'
                        WHEN a.deadline < NOW() THEN \'overdue\'
                        ELSE \'pending\'
                    END AS status
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
             LEFT JOIN submissions s ON s.assignment_id = a.id AND s.user_id = ?
             WHERE a.is_published = 1
             ORDER BY a.deadline ASC',
            [$user['id'], $user['id']]
        );

        $this->view('student.assignments.index', compact('user', 'assignments'));
    }

    // ── Single Assignment Detail ─────────────────────────────────
    public function show(array $params = []): void
    {
        $user         = $this->requireStudent();
        $assignmentId = (int) $params['id'];

        $assignment = Database::queryOne(
            'SELECT a.*, c.title AS course_title, c.slug AS course_slug,
                    s.id AS submission_id, s.score, s.feedback, s.submitted_at,
                    s.drive_url, s.file_name, s.drive_shared_confirmed,
                    CASE
                        WHEN s.id IS NOT NULL AND s.score IS NOT NULL THEN \'graded\'
                        WHEN s.id IS NOT NULL THEN \'submitted\'
                        WHEN a.deadline < NOW() THEN \'overdue\'
                        ELSE \'pending\'
                    END AS status
             FROM assignments a
             JOIN courses c ON a.course_id = c.id
             JOIN enrollments e ON e.course_id = c.id AND e.user_id = ?
             LEFT JOIN submissions s ON s.assignment_id = a.id AND s.user_id = ?
             WHERE a.id = ? AND a.is_published = 1',
            [$user['id'], $user['id'], $assignmentId]
        );

        if (!$assignment) {
            http_response_code(404);
            $this->view('errors.404'); return;
        }

        $this->view('student.assignments.show', compact('user', 'assignment'));
    }

    // ── Download Starter Notebook (.ipynb) ───────────────────────
    // Auth-gated: served via PHP readfile(), NOT from public web root
    public function downloadNotebook(array $params = []): void
    {
        $user         = $this->requireStudent();
        $assignmentId = (int) $params['id'];

        $assignment = Database::queryOne(
            'SELECT a.notebook_path, a.notebook_filename, a.course_id
             FROM assignments a
             JOIN enrollments e ON e.course_id = a.course_id AND e.user_id = ?
             WHERE a.id = ? AND a.is_published = 1 AND a.notebook_path IS NOT NULL',
            [$user['id'], $assignmentId]
        );

        if (!$assignment) {
            http_response_code(403);
            die('Notebook not available or you are not enrolled in this course.');
        }

        $filePath = $assignment['notebook_path'];

        // Security: ensure path is inside the private notebooks directory
        $realPath    = realpath($filePath);
        $allowedBase = realpath(NOTEBOOK_PATH);

        if (!$realPath || !str_starts_with($realPath, $allowedBase)) {
            http_response_code(403);
            die('Access denied.');
        }

        if (!file_exists($realPath)) {
            http_response_code(404);
            die('Notebook file not found. Please contact admin.');
        }

        $filename = $assignment['notebook_filename'] ?: basename($realPath);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: no-cache, no-store');
        header('Pragma: no-cache');

        readfile($realPath);
        exit;
    }

    // ── Submit Assignment ────────────────────────────────────────
    public function submit(array $params = []): void
    {
        $this->verifyCsrf();
        $user         = $this->requireStudent();
        $assignmentId = (int) $params['id'];

        $assignment = Database::queryOne(
            'SELECT a.* FROM assignments a
             JOIN enrollments e ON e.course_id = a.course_id AND e.user_id = ?
             WHERE a.id = ? AND a.is_published = 1',
            [$user['id'], $assignmentId]
        );

        if (!$assignment) {
            $this->flash('error', 'Assignment not found or you are not enrolled.');
            $this->redirect('/assignments');
        }

        // Check deadline
        if (strtotime($assignment['deadline']) < time()) {
            $this->flash('error', 'The submission deadline has passed.');
            $this->redirect('/assignments/' . $assignmentId);
        }

        // Check existing submission (block resubmit if not allowed)
        $existing = Database::queryOne(
            'SELECT id FROM submissions WHERE assignment_id = ? AND user_id = ?',
            [$assignmentId, $user['id']]
        );

        if ($existing && !$assignment['allow_resubmit']) {
            $this->flash('error', 'You have already submitted this assignment. Resubmission is not allowed.');
            $this->redirect('/assignments/' . $assignmentId);
        }

        $submissionType = $_POST['submission_type'] ?? 'drive_link';

        // ── Option A: Google Drive URL ────────────────────────────
        if ($submissionType === 'drive_link') {
            $driveUrl = trim($_POST['drive_url'] ?? '');
            $sharedConfirmed = isset($_POST['shared_confirmed']) ? 1 : 0;

            if (empty($driveUrl)) {
                $this->flash('error', 'Please provide your Google Drive link.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            if (!$this->isValidDriveUrl($driveUrl)) {
                $this->flash('error', 'Invalid Google Drive URL. Please share from Google Drive or Google Colab and paste the link here.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            if (!$sharedConfirmed) {
                $this->flash('error', 'Please confirm you have shared the notebook with ' . MENTOR_DRIVE_EMAIL . ' (Viewer access).');
                $this->redirect('/assignments/' . $assignmentId);
            }

            if ($existing) {
                Database::execute(
                    'UPDATE submissions SET drive_url=?, drive_shared_confirmed=?, submission_type=\'drive_link\',
                     file_path=NULL, file_name=NULL, submitted_at=NOW(), score=NULL, feedback=NULL, graded_at=NULL
                     WHERE id=?',
                    [$driveUrl, $sharedConfirmed, $existing['id']]
                );
            } else {
                Database::insert(
                    'INSERT INTO submissions (assignment_id, user_id, submission_type, drive_url, drive_shared_confirmed) VALUES (?,?,\'drive_link\',?,?)',
                    [$assignmentId, $user['id'], $driveUrl, $sharedConfirmed]
                );
            }

            $this->flash('success', 'Assignment submitted successfully via Google Drive!');
            $this->redirect('/assignments/' . $assignmentId);
        }

        // ── Option B: File upload (.ipynb or PDF) ─────────────────
        if ($submissionType === 'file_upload') {
            if (!isset($_FILES['notebook_file']) || $_FILES['notebook_file']['error'] !== UPLOAD_ERR_OK) {
                $this->flash('error', 'File upload failed. Please try again.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            $file     = $_FILES['notebook_file'];
            $origName = basename($file['name']);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($ext, ['ipynb', 'pdf', 'zip'], true)) {
                $this->flash('error', 'Only .ipynb, .pdf, or .zip files are accepted.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            if ($file['size'] > NOTEBOOK_MAX_BYTES) {
                $this->flash('error', 'File size exceeds the 25 MB limit.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            // Validate MIME
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            $allowed  = ['application/json', 'application/pdf', 'application/zip', 'application/octet-stream', 'text/plain'];
            if (!in_array($mimeType, $allowed, true)) {
                $this->flash('error', 'Invalid file type detected.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            // Save outside web root
            $uploadDir  = NOTEBOOK_PATH . '/submissions/' . $user['id'];
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0750, true);
            }

            $safeName  = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
            $destPath  = $uploadDir . '/' . $safeName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $this->flash('error', 'Failed to save the uploaded file. Please try again.');
                $this->redirect('/assignments/' . $assignmentId);
            }

            if ($existing) {
                // Delete old file
                if (!empty($existing['file_path']) && file_exists($existing['file_path'])) {
                    unlink($existing['file_path']);
                }
                Database::execute(
                    'UPDATE submissions SET submission_type=\'file_upload\', file_path=?, file_name=?,
                     drive_url=NULL, submitted_at=NOW(), score=NULL, feedback=NULL, graded_at=NULL WHERE id=?',
                    [$destPath, $origName, $existing['id']]
                );
            } else {
                Database::insert(
                    'INSERT INTO submissions (assignment_id, user_id, submission_type, file_path, file_name) VALUES (?,?,\'file_upload\',?,?)',
                    [$assignmentId, $user['id'], $destPath, $origName]
                );
            }

            $this->flash('success', 'Assignment file uploaded successfully!');
            $this->redirect('/assignments/' . $assignmentId);
        }

        $this->flash('error', 'Invalid submission type.');
        $this->redirect('/assignments/' . $assignmentId);
    }

    // ── Validate Google Drive / Colab URL ─────────────────────────
    private function isValidDriveUrl(string $url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) return false;

        $host = strtolower($parsed['host']);
        $allowedHosts = [
            'drive.google.com',
            'docs.google.com',
            'colab.research.google.com',
            'colab.googleapis.com',
        ];

        return in_array($host, $allowedHosts, true);
    }
}
