<?php

declare(strict_types=1);

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Database;

class CertHelper
{
    /**
     * Generate a certificate PDF and save it to disk.
     * Returns the file path on success, false on failure.
     */
    public static function generate(int $userId, int $courseId): string|false
    {
        $user   = Database::queryOne('SELECT name FROM users WHERE id = ?', [$userId]);
        $course = Database::queryOne('SELECT title, category FROM courses WHERE id = ?', [$courseId]);

        if (!$user || !course) return false;

        $token  = bin2hex(random_bytes(20));
        $date   = date('F j, Y');
        $catColor = $course['category'] === 'Robotics' ? '#34d399' : '#818cf8';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 0; size: A4 landscape; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: DejaVu Sans, sans-serif;
    background: #080b14;
    color: #e2e8f8;
    width: 297mm;
    height: 210mm;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }
  .cert {
    width: 297mm;
    height: 210mm;
    background: linear-gradient(135deg, #0f1220 0%, #080b14 100%);
    border: 2px solid rgba(129,140,248,.3);
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
  }
  .corner {
    position: absolute;
    width: 60px;
    height: 60px;
    border-color: $catColor;
    border-style: solid;
    opacity: .5;
  }
  .corner-tl { top: 20px; left: 20px;  border-width: 3px 0 0 3px; }
  .corner-tr { top: 20px; right: 20px; border-width: 3px 3px 0 0; }
  .corner-bl { bottom: 20px; left: 20px;  border-width: 0 0 3px 3px; }
  .corner-br { bottom: 20px; right: 20px; border-width: 0 3px 3px 0; }
  .logo { font-size: 13px; color: {$catColor}; font-weight: bold; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 10px; }
  .headline { font-size: 11px; letter-spacing: 6px; text-transform: uppercase; color: rgba(255,255,255,.4); margin-bottom: 18px; }
  .name { font-size: 38px; font-weight: bold; color: #fff; margin-bottom: 10px; text-align: center; }
  .completed { font-size: 12px; color: rgba(255,255,255,.5); margin-bottom: 14px; letter-spacing: 1px; }
  .course { font-size: 22px; font-weight: bold; color: {$catColor}; text-align: center; margin-bottom: 20px; border-bottom: 1px solid rgba(129,140,248,.2); padding-bottom: 18px; }
  .date-row { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 10px; letter-spacing: 1px; }
  .token { font-size: 9px; color: rgba(255,255,255,.2); margin-top: 8px; }
  .verify { font-size: 10px; color: rgba(255,255,255,.3); }
</style>
</head>
<body>
<div class="cert">
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>

  <div class="logo">⚛ NewtonJEE</div>
  <div class="headline">Certificate of Completion</div>
  <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:12px">This certifies that</div>
  <div class="name">{$user['name']}</div>
  <div class="completed">has successfully completed</div>
  <div class="course">{$course['title']}</div>
  <div class="date-row">Issued on {$date}</div>
  <div class="token">Certificate ID: {$token}</div>
  <div class="verify">Verify at newtonjee.com/verify/{$token}</div>
</div>
</body>
</html>
HTML;

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dir = PRIVATE_PATH . '/certificates/' . $userId;
        if (!is_dir($dir)) mkdir($dir, 0750, true);

        $filename = 'certificate_' . $courseId . '_' . time() . '.pdf';
        $path     = $dir . '/' . $filename;
        file_put_contents($path, $dompdf->output());

        return $path;
    }
}
