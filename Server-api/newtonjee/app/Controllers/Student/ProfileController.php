<?php

declare(strict_types=1);

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Database;

class ProfileController extends BaseController
{
    public function setupForm(array $params = []): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/google');
        }

        $user = Database::queryOne(
            'SELECT * FROM users WHERE id = ? AND role = \'student\'',
            [$_SESSION['user_id']]
        );

        if (!$user) { $this->redirect('/auth/google'); }
        if ($user['profile_complete']) { $this->redirect('/dashboard'); }

        $batches = Database::query('SELECT * FROM batches WHERE is_active = 1 ORDER BY year DESC');

        // Render without main layout (standalone page)
        extract(compact('user', 'batches'));
        include VIEWS_PATH . '/student/profile_setup.php';
    }

    public function setupSave(array $params = []): void
    {
        $this->verifyCsrf();

        if (!$this->isLoggedIn()) {
            $this->redirect('/auth/google');
        }

        $userId  = $_SESSION['user_id'];
        $batchId = (int)($_POST['batch_id'] ?? 0);
        $sid     = trim($_POST['student_id_custom'] ?? '');

        if (!$batchId) {
            $this->flash('error', 'Please select your batch.');
            $this->redirect('/setup-profile');
        }

        Database::execute(
            'UPDATE users SET batch_id = ?, student_id_custom = ?, profile_complete = 1 WHERE id = ?',
            [$batchId, $sid ?: null, $userId]
        );

        $this->redirect('/dashboard');
    }
}
