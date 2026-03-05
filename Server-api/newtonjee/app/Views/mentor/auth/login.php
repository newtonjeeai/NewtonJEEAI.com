<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mentor Login — NewtonJEE</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--bg:#080b14;--surface:#0f1220;--border:rgba(255,255,255,.07);--text:#e2e8f8;--muted:#55607a;--green:#34d399;--red:#f87171}
    body{min-height:100vh;background:var(--bg);display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;color:var(--text);padding:24px}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:44px 40px;width:100%;max-width:400px;box-shadow:0 24px 48px rgba(0,0,0,.4)}
    .logo{display:flex;align-items:center;gap:10px;margin-bottom:32px;justify-content:center}
    .logo-icon{width:40px;height:40px;background:linear-gradient(135deg,#34d399,#22d3ee);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}
    .logo-text{font-family:'Space Mono',monospace;font-size:18px;font-weight:700;color:var(--text)}
    .logo-text span{color:var(--green)}
    .badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);border-radius:20px;font-size:11px;color:var(--green);font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
    h1{font-size:22px;font-weight:700;text-align:center;margin-bottom:6px}
    .subtitle{font-size:13px;color:var(--muted);text-align:center;margin-bottom:28px}
    .form-group{margin-bottom:16px}
    .form-label{display:block;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
    .form-control{width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;font-family:inherit;outline:none;transition:border-color .2s}
    .form-control:focus{border-color:var(--green)}
    .btn-submit{width:100%;padding:13px;background:var(--green);color:#000;border:none;border-radius:12px;font-size:15px;font-weight:800;font-family:inherit;cursor:pointer;transition:all .2s}
    .btn-submit:hover{opacity:.88}
    .flash-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.25);color:var(--red);padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:18px}
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-icon">⚛</div>
    <div class="logo-text">Newton<span>JEE</span></div>
  </div>
  <div style="text-align:center;margin-bottom:20px">
    <span class="badge">🎓 Mentor Portal</span>
  </div>
  <h1>Mentor Login</h1>
  <p class="subtitle">Email and password login. Students use Google sign-in instead.</p>

  <?php
  $flashes = $_SESSION['flash'] ?? [];
  unset($_SESSION['flash']);
  foreach ($flashes as $f): ?>
    <div class="flash-error"><?= htmlspecialchars($f['message']) ?></div>
  <?php endforeach; ?>

  <form method="POST" action="/mentor/login">
    <input type="hidden" name="_csrf" value="<?php
      if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      echo $_SESSION['csrf_token'];
    ?>">
    <div class="form-group">
      <label class="form-label" for="email">Email Address</label>
      <input type="email" name="email" id="email" class="form-control"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="mentor@newtonjee.com" required autocomplete="email">
    </div>
    <div class="form-group">
      <label class="form-label" for="password">Password</label>
      <input type="password" name="password" id="password" class="form-control"
             placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-submit">Sign In as Mentor</button>
  </form>
</div>
</body>
</html>
