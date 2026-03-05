<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class CertificateController extends BaseController
{
    public function index(array $params = []): void
    {
        $user = $this->requireStudent();

        $certificates = Database::query(
            'SELECT cert.verify_token, cert.issued_at, cert.file_path,
                    c.title AS course_title, c.category
             FROM certificates cert
             JOIN courses c ON cert.course_id = c.id
             WHERE cert.user_id = ? AND cert.revoked_at IS NULL
             ORDER BY cert.issued_at DESC',
            [$user['id']]
        );

        $this->view('student.certificates.index', compact('user', 'certificates'));
    }

    public function download(array $params = []): void
    {
        $user  = $this->requireStudent();
        $token = $params['token'];

        $cert = Database::queryOne(
            'SELECT cert.file_path, c.title
             FROM certificates cert
             JOIN courses c ON cert.course_id = c.id
             WHERE cert.verify_token = ? AND cert.user_id = ? AND cert.revoked_at IS NULL',
            [$token, $user['id']]
        );

        if (!$cert || !$cert['file_path']) {
            $this->flash('error', 'Certificate not found or file not generated yet.');
            $this->redirect('/certificates');
        }

        $realPath    = realpath($cert['file_path']);
        $allowedBase = realpath(PRIVATE_PATH . '/certificates');

        if (!$realPath || !str_starts_with($realPath, $allowedBase) || !file_exists($realPath)) {
            $this->flash('error', 'Certificate file not available. Please contact support.');
            $this->redirect('/certificates');
        }

        $filename = 'NewtonJEE_Certificate_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $cert['title']) . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($realPath));
        header('Cache-Control: private, no-cache');
        readfile($realPath);
        exit;
    }

    /** Public certificate verification — no auth required */
    public function verify(array $params = []): void
    {
        $token = $params['token'];

        $cert = Database::queryOne(
            'SELECT cert.verify_token, cert.issued_at,
                    u.name AS student_name,
                    c.title AS course_title, c.category,
                    cert.revoked_at
             FROM certificates cert
             JOIN users u ON cert.user_id = u.id
             JOIN courses c ON cert.course_id = c.id
             WHERE cert.verify_token = ?',
            [$token]
        );

        // This is a public page — render without full portal layout
        include VIEWS_PATH . '/student/certificates/verify.php';
    }
}
