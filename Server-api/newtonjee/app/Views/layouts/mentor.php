<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Mentor') ?> — NewtonJEE Mentor</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    .mentor-sidebar { background: #0a1008; border-right-color: rgba(52,211,153,.1); }
    .mentor-logo-badge { background: rgba(52,211,153,.15); color: #34d399; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: .8px; margin-top: 2px; }
    .nav-item.active { background: rgba(52,211,153,.1); color: #34d399; }
  </style>
</head>
<body>
<?php $flashes = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); ?>
<div class="portal-wrap">
  <aside class="sidebar mentor-sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background:linear-gradient(135deg,#34d399,#22d3ee)">⚛</div>
      <div>
        <div class="logo-name">Newton<span style="color:#34d399">JEE</span></div>
        <div class="mentor-logo-badge">Mentor</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <?php
      $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $nav  = [
        ['/mentor',                  '📊', 'Dashboard'],
        ['/mentor/courses',          '📚', 'My Courses'],
        ['/mentor/submissions',      '📥', 'Submissions'],
        ['/mentor/exams/results',    '📝', 'Exam Results'],
        ['/mentor/announcements',    '📣', 'Announcements'],
      ];
      foreach ($nav as [$href, $icon, $label]):
        $active = ($href === '/mentor' ? $path === '/mentor' : str_starts_with($path, $href)) ? 'active' : '';
      ?>
        <a href="<?= $href ?>" class="nav-item <?= $active ?>">
          <span class="nav-icon"><?= $icon ?></span>
          <span class="nav-label"><?= $label ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-profile">
      <div class="profile-initials" style="background:linear-gradient(135deg,#34d399,#22d3ee)">
        <?= strtoupper(substr($mentor['name'] ?? 'M', 0, 2)) ?>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($mentor['name'] ?? '') ?></div>
        <div class="profile-role" style="color:#34d399">Mentor</div>
      </div>
    </div>
  </aside>
  <main class="main-content">
    <div class="topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        <form action="/mentor/logout" method="POST" style="display:inline">
          <input type="hidden" name="_csrf" value="<?php
            if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            echo $_SESSION['csrf_token'];
          ?>">
          <button type="submit" class="btn-logout" title="Sign out">↩</button>
        </form>
      </div>
    </div>
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
        <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
      </div>
    <?php endforeach; ?>
    <div class="page-body">
      <?php include $file; ?>
    </div>
  </main>
</div>
<script src="/public/js/app.js"></script>
</body>
</html>
