<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — NewtonJEE</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:      #080b14;
      --surface: #0f1220;
      --border:  rgba(255,255,255,.07);
      --text:    #e2e8f8;
      --muted:   #55607a;
      --accent:  #818cf8;
      --green:   #34d399;
    }
    body {
      min-height: 100vh;
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Sans', sans-serif;
      color: var(--text);
      padding: 24px;
    }

    /* animated background dots */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background-image:
        radial-gradient(circle at 20% 20%, rgba(129,140,248,.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(52,211,153,.06) 0%, transparent 50%);
      pointer-events: none;
    }

    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 24px;
      padding: 48px 44px;
      width: 100%;
      max-width: 440px;
      text-align: center;
      position: relative;
      box-shadow: 0 32px 64px rgba(0,0,0,.4);
    }

    .logo {
      display: inline-flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 32px;
    }
    .logo-icon {
      width: 48px; height: 48px;
      background: linear-gradient(135deg, #818cf8, #34d399);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 24px;
    }
    .logo-text {
      font-family: 'Space Mono', monospace;
      font-size: 22px;
      font-weight: 700;
      color: var(--text);
    }
    .logo-text span { color: var(--accent); }

    h1 {
      font-size: 26px;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 10px;
      letter-spacing: -.4px;
    }
    .subtitle {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 36px;
      line-height: 1.6;
    }

    .btn-google {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      width: 100%;
      padding: 15px 20px;
      background: #fff;
      color: #1e2340;
      border: none;
      border-radius: 14px;
      font-size: 16px;
      font-weight: 700;
      font-family: 'DM Sans', sans-serif;
      cursor: pointer;
      text-decoration: none;
      transition: all .2s;
      box-shadow: 0 4px 16px rgba(0,0,0,.3);
    }
    .btn-google:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,.4);
    }
    .btn-google:active { transform: translateY(0); }

    .google-logo {
      width: 22px; height: 22px;
      flex-shrink: 0;
    }

    .divider {
      margin: 28px 0;
      display: flex;
      align-items: center;
      gap: 12px;
      color: var(--muted);
      font-size: 12px;
    }
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      margin-top: 28px;
    }
    .feature {
      background: rgba(255,255,255,.03);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 12px;
      font-size: 12px;
      color: var(--muted);
    }
    .feature span { display: block; font-size: 18px; margin-bottom: 4px; }

    .flash-error {
      background: rgba(248,113,113,.12);
      border: 1px solid rgba(248,113,113,.3);
      color: #f87171;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      text-align: left;
    }

    footer {
      margin-top: 28px;
      font-size: 11px;
      color: var(--muted);
    }
    footer a { color: var(--muted); }
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
    <div class="flash-<?= htmlspecialchars($f['type']) ?>">
      <?= htmlspecialchars($f['message']) ?>
    </div>
  <?php endforeach; ?>

  <h1>Welcome back 👋</h1>
  <p class="subtitle">Sign in with your Google account to access your courses, assignments, and learning dashboard.</p>

  <a href="/auth/google" class="btn-google">
    <!-- Google 'G' logo SVG -->
    <svg class="google-logo" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
      <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
      <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
      <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
      <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
      <path fill="none" d="M0 0h48v48H0z"/>
    </svg>
    Continue with Google
  </a>

  <div class="divider">Your NewtonJEE Learning Portal</div>

  <div class="features">
    <div class="feature"><span>🤖</span>AI & Robotics Courses</div>
    <div class="feature"><span>🐍</span>Python Notebooks</div>
    <div class="feature"><span>🏆</span>Certificates</div>
    <div class="feature"><span>📊</span>Progress Tracking</div>
  </div>

  <footer>
    By signing in, you agree to NewtonJEE's
    <a href="/terms">Terms of Service</a> &amp; <a href="/privacy">Privacy Policy</a>.
  </footer>

</div>

</body>
</html>
