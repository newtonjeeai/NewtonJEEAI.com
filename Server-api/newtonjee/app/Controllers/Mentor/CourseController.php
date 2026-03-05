<?php

declare(strict_types=1);

namespace App\Controllers\Mentor;

use App\Controllers\BaseController;
use App\Database;

class CourseController extends BaseController
{
    public function index(array $params = []): void
    {
        $mentor  = $this->requireMentor();
        $courses = Database::query(
            'SELECT c.*, COUNT(DISTINCT e.id) AS enrolled, ROUND(AVG(e.progress_pct),1) AS avg_pct
             FROM courses c LEFT JOIN enrollments e ON e.course_id = c.id
             WHERE c.mentor_id = ? AND c.deleted_at IS NULL GROUP BY c.id ORDER BY c.id DESC',
            [$mentor['id']]
        );
        $this->mentorView('mentor.courses.index', compact('mentor', 'courses'));
    }

    public function students(array $params = []): void
    {
        $mentor   = $this->requireMentor();
        $courseId = (int)$params['id'];

        $course = Database::queryOne(
            'SELECT id, title FROM courses WHERE id = ? AND mentor_id = ?',
            [$courseId, $mentor['id']]
        );
        if (!$course) $this->redirect('/mentor/courses');

        $students = Database::query(
            'SELECT u.name, u.email, u.avatar_url, b.name AS batch,
                    e.progress_pct, e.status, e.enrolled_at,
                    (SELECT COUNT(*) FROM submissions s JOIN assignments a ON s.assignment_id=a.id
                     WHERE a.course_id=? AND s.user_id=u.id) AS submissions
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             LEFT JOIN batches b ON u.batch_id = b.id
             WHERE e.course_id = ?
             ORDER BY e.progress_pct DESC',
            [$courseId, $courseId]
        );

        $this->mentorView('mentor.courses.students', compact('mentor', 'course', 'students'));
    }
}
