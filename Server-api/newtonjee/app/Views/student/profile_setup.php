<?php $pageTitle = 'Complete Your Profile'; ?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Complete Your Profile — NewtonJEE</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#080b14;--surface:#0f1220;--border:rgba(255,255,255,.07);--text:#e2e8f8;--muted:#55607a;--accent:#818cf8;--green:#34d399}
    body{min-height:100vh;background:var(--bg);display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;color:var(--text);padding:24px}
    body::before{content:'';position:fixed;inset:0;background:radial-gradient(circle at 50% 50%, rgba(129,140,248,.06) 0%, transparent 60%);pointer-events:none}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:40px 36px;width:100%;max-width:460px;box-shadow:0 24px 48px rgba(0,0,0,.4)}
    .logo{display:flex;align-items:center;gap:10px;margin-bottom:28px}
    .logo-icon{width:38px;height:38px;background:linear-gradient(135deg,#818cf8,#34d399);border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:18px}
    .logo-text{font-family:'Space Mono',monospace;font-weight:700;font-size:16px}
    .logo-text span{color:var(--accent)}
    .avatar-preview{width:56px;height:56px;border-radius:50%;border:2px solid var(--border)}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
    .form-control{width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--accent)}
    select.form-control option{background:#0f1220}
    .btn-submit{width:100%;padding:13px;background:var(--accent);color:#fff;border:none;border-radius:12px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .2s;margin-top:8px}
    .btn-submit:hover{opacity:.88}
    .step-note{font-size:12px;color:var(--muted);text-align:center;margin-bottom:20px}
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">⚛</div>
    <div class="logo-text">Newton<span>JEE</span></div>
  </div>

  <?php
  $flashes = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);
  foreach ($flashes as $f): ?>
    <div style="background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:#f87171;padding:10px 14px;border-radius:10px;font-size:13px;margin-bottom:16px">
      <?= htmlspecialchars($f['message']) ?>
    </div>
  <?php endforeach; ?>

  <?php if (!empty($user['avatar_url'])): ?>
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;padding:12px;background:rgba(129,140,248,.06);border-radius:12px">
    <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" class="avatar-preview">
    <div>
      <div style="font-weight:700"><?= htmlspecialchars($user['name']) ?></div>
      <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($user['email']) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <h2 style="font-size:20px;font-weight:800;margin-bottom:6px">Complete Your Profile</h2>
  <p class="step-note">Just 2 quick fields — then you're in! 🚀</p>

  <form method="POST" action="/setup-profile">
    <input type="hidden" name="_csrf" value="<?php
      if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      echo $_SESSION['csrf_token'];
    ?>">

    <div class="form-group">
      <label class="form-label" for="batch_id">Your Batch *</label>
      <select name="batch_id" id="batch_id" class="form-control" required>
        <option value="">— Select your batch —</option>
        <?php foreach ($batches as $b): ?>
          <option value="<?= $b['id'] ?>" <?= ($_POST['batch_id']??'')==$b['id']?'selected':''?>>
            <?= htmlspecialchars($b['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label class="form-label" for="student_id_custom">Student ID (optional)</label>
      <input type="text" name="student_id_custom" id="student_id_custom" class="form-control"
             value="<?= htmlspecialchars($_POST['student_id_custom'] ?? '') ?>"
             placeholder="e.g. NJ-2026-0042">
      <div style="font-size:11px;color:var(--muted);margin-top:4px">If you received a Student ID from NewtonJEE, enter it here.</div>
    </div>

    <button type="submit" class="btn-submit">Enter the Portal →</button>
  </form>
</div>
</body>
</html>
