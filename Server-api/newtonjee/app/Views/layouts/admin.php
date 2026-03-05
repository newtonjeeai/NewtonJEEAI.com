<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — NewtonJEE Admin</title>
  <link rel="stylesheet" href="/public/css/app.css">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <style>
    .admin-sidebar { background: #0a0d18; border-right-color: rgba(248,113,113,.1); }
    .admin-logo-badge { background: rgba(248,113,113,.15); color: #f87171; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 20px; text-transform: uppercase; letter-spacing: .8px; margin-top: 2px; }
    .nav-item.active { background: rgba(248,113,113,.1); color: #f87171; }
    .nav-item:hover { color: var(--text); }
  </style>
</head>
<body>

<?php
$flashes = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>

<div class="portal-wrap">

  <!-- Admin Sidebar -->
  <aside class="sidebar admin-sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background:linear-gradient(135deg,#f87171,#fb923c)">⚛</div>
      <div>
        <div class="logo-name">Newton<span style="color:#f87171">JEE</span></div>
        <div class="admin-logo-badge">Admin</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php
      $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $adminNav = [
        ['/admin',                 '📊', 'Dashboard'],
        ['/admin/users',           '👥', 'Students'],
        ['/admin/courses',         '📚', 'Courses'],
        ['/admin/assignments',     '📓', 'Assignments'],
        ['/admin/submissions',     '📥', 'Submissions'],
        ['/admin/exams',           '📝', 'Exams'],
        ['/admin/certificates',    '🏆', 'Certificates'],
        ['/admin/announcements',   '📣', 'Announcements'],
        ['/admin/calendar',        '📅', 'Calendar'],
        ['/admin/analytics',       '📈', 'Analytics'],
        ['/admin/settings',        '⚙️', 'Settings'],
      ];
      foreach ($adminNav as [$href, $icon, $label]):
        $active = ($href === '/admin' ? $path === '/admin' : str_starts_with($path, $href)) ? 'active' : '';
      ?>
        <a href="<?= $href ?>" class="nav-item <?= $active ?>">
          <span class="nav-icon"><?= $icon ?></span>
          <span class="nav-label"><?= $label ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-profile">
      <div class="profile-initials" style="background:linear-gradient(135deg,#f87171,#fb923c)">
        <?= strtoupper(substr($admin['name'] ?? 'A', 0, 2)) ?>
      </div>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($admin['name'] ?? '') ?></div>
        <div class="profile-role" style="color:#f87171"><?= ucfirst(str_replace('_',' ',$admin['role'] ?? 'admin')) ?></div>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <div class="topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
      <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>
        <a href="/" target="_blank" class="btn btn-sm btn-secondary" title="View student portal">↗ Portal</a>
        <form action="/admin/logout" method="POST" style="display:inline">
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
