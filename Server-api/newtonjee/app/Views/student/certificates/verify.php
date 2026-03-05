<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Certificate — NewtonJEE</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#080b14;--surface:#0f1220;--border:rgba(255,255,255,.07);--text:#e2e8f8;--muted:#55607a;--accent:#818cf8;--green:#34d399;--red:#f87171}
    body{min-height:100vh;background:var(--bg);display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;color:var(--text);padding:24px}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:40px 36px;width:100%;max-width:480px;text-align:center}
    .logo{font-family:'Space Mono',monospace;font-weight:700;font-size:18px;margin-bottom:8px}
    .logo span{color:var(--accent)}
    .valid{border-color:rgba(52,211,153,.35)!important}
    .invalid{border-color:rgba(248,113,113,.35)!important}
    .badge-valid{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:rgba(52,211,153,.12);border:1px solid rgba(52,211,153,.3);color:var(--green);border-radius:20px;font-weight:700;font-size:13px;margin-bottom:20px}
    .badge-invalid{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:var(--red);border-radius:20px;font-weight:700;font-size:13px;margin-bottom:20px}
    .cert-name{font-size:26px;font-weight:800;margin-bottom:6px}
    .cert-course{font-size:16px;color:var(--accent);font-weight:700;margin-bottom:6px}
    .cert-issued{font-size:12px;color:var(--muted);margin-bottom:20px}
    .token{font-size:10px;color:var(--muted);word-break:break-all;margin-top:16px;background:rgba(255,255,255,.03);padding:8px 12px;border-radius:8px}
    .back{margin-top:24px;font-size:13px;color:var(--muted)}
    .back a{color:var(--accent)}
    .divider{height:1px;background:var(--border);margin:20px 0}
  </style>
</head>
<body>
<div class="card <?= $cert ? ($cert['revoked_at'] ? 'invalid' : 'valid') : 'invalid' ?>">
  <div class="logo">Newton<span>JEE</span></div>
  <div style="font-size:11px;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:24px">Certificate Verification</div>

  <?php if (!$cert): ?>
    <div class="badge-invalid">✕ Certificate Not Found</div>
    <p style="font-size:14px;color:var(--muted)">
      No certificate was found with this verification token.<br>
      The link may be invalid or the certificate may have been removed.
    </p>

  <?php elseif ($cert['revoked_at']): ?>
    <div class="badge-invalid">✕ Certificate Revoked</div>
    <div class="cert-name"><?= htmlspecialchars($cert['student_name']) ?></div>
    <div class="cert-course"><?= htmlspecialchars($cert['course_title']) ?></div>
    <p style="font-size:13px;color:var(--muted)">
      This certificate was revoked on <?= date('F j, Y', strtotime($cert['revoked_at'])) ?>.
    </p>

  <?php else: ?>
    <?php $catColor = $cert['category'] === 'Robotics' ? '#34d399' : '#818cf8'; ?>
    <div class="badge-valid">✓ Valid Certificate</div>

    <div style="font-size:42px;margin-bottom:12px"><?= $cert['category'] === 'Robotics' ? '🤖' : '🧠' ?></div>

    <div style="font-size:12px;color:<?= $catColor ?>;font-weight:800;text-transform:uppercase;letter-spacing:2px;margin-bottom:10px">
      Certificate of Completion
    </div>

    <div class="cert-name"><?= htmlspecialchars($cert['student_name']) ?></div>
    <div style="font-size:13px;color:var(--muted);margin-bottom:8px">has successfully completed</div>
    <div class="cert-course" style="color:<?= $catColor ?>"><?= htmlspecialchars($cert['course_title']) ?></div>

    <div class="divider"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;text-align:left">
      <div style="background:rgba(255,255,255,.03);border-radius:10px;padding:10px 14px">
        <div style="font-size:10px;color:var(--muted);margin-bottom:3px">Issued By</div>
        <div style="font-size:13px;font-weight:700">NewtonJEE</div>
        <div style="font-size:10px;color:var(--muted)">newtonjee.com</div>
      </div>
      <div style="background:rgba(255,255,255,.03);border-radius:10px;padding:10px 14px">
        <div style="font-size:10px;color:var(--muted);margin-bottom:3px">Issue Date</div>
        <div style="font-size:13px;font-weight:700"><?= date('F j, Y', strtotime($cert['issued_at'])) ?></div>
      </div>
    </div>

    <div class="token">
      <span style="display:block;font-size:9px;color:var(--muted);margin-bottom:3px">CERTIFICATE ID</span>
      <?= htmlspecialchars($cert['verify_token']) ?>
    </div>
  <?php endif; ?>

  <div class="back">
    <a href="/">← Back to NewtonJEE Portal</a>
  </div>
</div>
</body>
</html>
