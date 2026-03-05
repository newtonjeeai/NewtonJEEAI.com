<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Database;
use App\Helpers\CertHelper;

class CertificateController extends BaseController
{
    public function index(array $params = []): void
    {
        $admin = $this->requireAdmin();

        $certificates = Database::query(
            'SELECT cert.id, cert.verify_token, cert.issued_at, cert.revoked_at,
                    u.name AS student_name, u.email,
                    c.title AS course_title, c.category
             FROM certificates cert
             JOIN users u ON cert.user_id = u.id
             JOIN courses c ON cert.course_id = c.id
             ORDER BY cert.issued_at DESC'
        );

        $this->adminView('admin.certificates.index', compact('admin', 'certificates'));
    }

    public function issue(array $params = []): void
    {
        $this->verifyCsrf();
        $admin    = $this->requireAdmin();
        $userId   = (int)($_POST['user_id']   ?? 0);
        $courseId = (int)($_POST['course_id'] ?? 0);

        if (!$userId || !$courseId) {
            $this->flash('error', 'Student and course are required.');
            $this->redirect('/admin/certificates');
        }

        // Check not already issued
        $existing = Database::queryOne(
            'SELECT id FROM certificates WHERE user_id = ? AND course_id = ? AND revoked_at IS NULL',
            [$userId, $courseId]
        );
        if ($existing) {
            $this->flash('error', 'A certificate has already been issued for this student and course.');
            $this->redirect('/admin/certificates');
        }

        $verifyToken = bin2hex(random_bytes(20));
        $filePath    = CertHelper::generate($userId, $courseId) ?: null;

        Database::insert(
            'INSERT INTO certificates (user_id, course_id, verify_token, file_path) VALUES (?,?,?,?)',
            [$userId, $courseId, $verifyToken, $filePath]
        );

        $this->audit('certificate.issue', 'certificate', 0, ['user_id' => $userId, 'course_id' => $courseId]);
        $this->flash('success', 'Certificate issued successfully.');
        $this->redirect('/admin/certificates');
    }

    public function revoke(array $params = []): void
    {
        $this->verifyCsrf();
        $admin = $this->requireAdmin();
        $certId = (int)$params['id'];

        Database::execute(
            'UPDATE certificates SET revoked_at = NOW() WHERE id = ?',
            [$certId]
        );

        $this->audit('certificate.revoke', 'certificate', $certId);
        $this->flash('success', 'Certificate revoked.');
        $this->redirect('/admin/certificates');
    }
}
