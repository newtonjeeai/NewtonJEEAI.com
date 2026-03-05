<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'NewtonJEE') ?> — NewtonJEE</title>
  <link rel="icon" href="/public/img/favicon.ico">
  <link rel="stylesheet" href="/public/css/app.css">
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php
// Flash messages
$flashes = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
?>

<div class="portal-wrap">

  <!-- ══ SIDEBAR ══ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">⚛</div>
      <div>
        <div class="logo-name">Newton<span>JEE</span></div>
        <div class="logo-sub">Student Portal</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php
      $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
      $navItems = [
        ['path' => '/dashboard',     'icon' => '⬡',  'label' => 'Home'],
        ['path' => '/courses',       'icon' => '🗂',  'label' => 'Courses'],
        ['path' => '/my-courses',    'icon' => '📚', 'label' => 'My Courses'],
        ['path' => '/announcements', 'icon' => '📣', 'label' => 'Announcements', 'badge' => $unreadCount ?? 0],
        ['path' => '/calendar',      'icon' => '📅', 'label' => 'Calendar'],
        ['path' => '/assignments',   'icon' => '📓', 'label' => 'Assignments'],
        ['path' => '/exams',         'icon' => '📝', 'label' => 'Exams'],
        ['path' => '/certificates',  'icon' => '🏆', 'label' => 'Certificates'],
        ['path' => '/settings',      'icon' => '⚙️', 'label' => 'Settings'],
        ['path' => '/help',          'icon' => '💬', 'label' => 'Help'],
      ];
      foreach ($navItems as $item):
        $active = str_starts_with($currentPath, $item['path']) ? 'active' : '';
      ?>
      <a href="<?= $item['path'] ?>" class="nav-item <?= $active ?>">
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span class="nav-label"><?= $item['label'] ?></span>
        <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
          <span class="nav-badge"><?= (int)$item['badge'] ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </nav>

    <!-- Profile strip -->
    <div class="sidebar-profile">
      <?php if (!empty($user['avatar_url'])): ?>
        <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Avatar" class="profile-avatar">
      <?php else: ?>
        <div class="profile-initials"><?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?></div>
      <?php endif; ?>
      <div class="profile-info">
        <div class="profile-name"><?= htmlspecialchars($user['name'] ?? '') ?></div>
        <div class="profile-role">AI Novice 🌱</div>
      </div>
    </div>
  </aside>

  <!-- ══ MAIN CONTENT ══ -->
  <main class="main-content">

    <!-- Topbar -->
    <div class="topbar">
      <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">☰</button>
      <div class="topbar-right">
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">🌙</button>
        <a href="/announcements" class="notif-bell" title="Announcements">
          🔔
          <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <span class="notif-dot"></span>
          <?php endif; ?>
        </a>
        <form action="/auth/logout" method="POST" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">
          <button type="submit" class="btn-logout" title="Sign out">↩</button>
        </form>
      </div>
    </div>

    <!-- Flash messages -->
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= htmlspecialchars($flash['type']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
        <button class="flash-close" onclick="this.parentElement.remove()">✕</button>
      </div>
    <?php endforeach; ?>

    <!-- Page content injected here -->
    <div class="page-body">
      <?php include $file; ?>
    </div>

  </main>
</div>

<script src="/public/js/app.js"></script>
</body>
</html>
