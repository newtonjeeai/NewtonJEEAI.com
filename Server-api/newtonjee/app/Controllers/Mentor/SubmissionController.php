<?php

declare(strict_types=1);

namespace App\Controllers\Mentor;

use App\Controllers\BaseController;
use App\Database;

class SubmissionController extends BaseController
{
    public function index(array $params = []): void
    {
        $mentor = $this->requireMentor();
        $filter = $_GET['filter'] ?? 'pending';

        $sql  = 'SELECT s.id, s.submission_type, s.drive_url, s.file_name, s.score,
                        s.submitted_at, s.drive_shared_confirmed,
                        u.name AS student_name, u.email,
                        a.title AS assignment_title, a.max_score,
                        c.title AS course_title
                 FROM submissions s
                 JOIN users u ON s.user_id = u.id
                 JOIN assignments a ON s.assignment_id = a.id
                 JOIN courses c ON a.course_id = c.id
                 WHERE c.mentor_id = ?';
        $bind = [$mentor['id']];

        if ($filter === 'pending') { $sql .= ' AND s.score IS NULL'; }
        if ($filter === 'graded')  { $sql .= ' AND s.score IS NOT NULL'; }

        $sql .= ' ORDER BY s.submitted_at DESC';
        $submissions = Database::query($sql, $bind);

        $this->mentorView('mentor.submissions.index', compact('mentor', 'submissions', 'filter'));
    }

    public function show(array $params = []): void
    {
        $mentor     = $this->requireMentor();
        $submission = Database::queryOne(
            'SELECT s.*, u.name AS student_name, u.email,
                    a.title AS assignment_title, a.max_score, a.description AS assignment_desc,
                    c.title AS course_title
             FROM submissions s
             JOIN users u ON s.user_id = u.id
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE s.id = ? AND c.mentor_id = ?',
            [(int)$params['id'], $mentor['id']]
        );
        if (!$submission) {
            $this->flash('error', 'Submission not found.');
            $this->redirect('/mentor/submissions');
        }
        $this->mentorView('mentor.submissions.show', compact('mentor', 'submission'));
    }

    public function grade(array $params = []): void
    {
        $this->verifyCsrf();
        $mentor       = $this->requireMentor();
        $submissionId = (int) $params['id'];

        $submission = Database::queryOne(
            'SELECT s.*, a.max_score FROM submissions s
             JOIN assignments a ON s.assignment_id = a.id
             JOIN courses c ON a.course_id = c.id
             WHERE s.id = ? AND c.mentor_id = ?',
            [$submissionId, $mentor['id']]
        );

        if (!$submission) {
            $this->flash('error', 'Submission not found.');
            $this->redirect('/mentor/submissions');
        }

        $score    = (int)($_POST['score'] ?? 0);
        $feedback = trim($_POST['feedback'] ?? '');

        if ($score < 0 || $score > $submission['max_score']) {
            $this->flash('error', "Score must be 0–{$submission['max_score']}.");
            $this->redirect('/mentor/submissions/' . $submissionId);
        }

        Database::execute(
            'UPDATE submissions SET score=?, feedback=?, graded_by=?, graded_at=NOW() WHERE id=?',
            [$score, $feedback, $mentor['id'], $submissionId]
        );

        $this->flash('success', 'Submission graded.');
        $this->redirect('/mentor/submissions');
    }
}
