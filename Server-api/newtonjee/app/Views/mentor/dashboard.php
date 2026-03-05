<?php $pageTitle = 'Mentor Dashboard'; ?>

<div class="page-title">Mentor Dashboard 🎓</div>
<div class="page-subtitle">Your courses and pending submissions — <?= date('l, F j') ?></div>

<!-- Stats -->
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-bg-icon">📚</div>
    <div class="stat-label">My Courses</div>
    <div class="stat-value" style="color:#34d399"><?= count($courses) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-bg-icon">📥</div>
    <div class="stat-label">Pending Grades</div>
    <div class="stat-value" style="color:<?= $pendingSubmissions > 0 ? '#f87171' : '#34d399' ?>"><?= $pendingSubmissions ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-bg-icon">👥</div>
    <div class="stat-label">Total Students</div>
    <div class="stat-value" style="color:#818cf8"><?= array_sum(array_column($courses,'enrolled')) ?></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

  <!-- My Courses -->
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <strong>My Courses</strong>
      <a href="/mentor/courses" class="btn btn-sm btn-secondary">View all →</a>
    </div>
    <?php if (empty($courses)): ?>
      <p class="text-muted text-sm">No courses assigned yet.</p>
    <?php else: foreach ($courses as $c): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm font-bold"><?= htmlspecialchars($c['title']) ?></div>
            <div class="text-xs text-muted"><?= $c['enrolled'] ?> students · <?= $c['avg_progress'] ?>% avg progress</div>
          </div>
          <a href="/mentor/courses/<?= $c['id'] ?>/students" class="btn btn-sm btn-secondary">Students</a>
        </div>
        <div class="progress-bar" style="margin-top:7px">
          <div class="progress-fill" data-pct="<?= $c['avg_progress'] ?>" style="background:#34d399"></div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Pending Submissions -->
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <strong>Pending Submissions</strong>
      <a href="/mentor/submissions?filter=pending" class="btn btn-sm btn-secondary">View all →</a>
    </div>
    <?php if (empty($recentSubmissions)): ?>
      <div style="text-align:center;padding:24px">
        <div style="font-size:28px;margin-bottom:6px">🎉</div>
        <div class="text-muted text-sm">All caught up!</div>
      </div>
    <?php else: foreach ($recentSubmissions as $s): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm font-bold"><?= htmlspecialchars($s['student']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($s['assignment']) ?></div>
            <?php if ($s['drive_url']): ?>
              <div class="text-xs" style="color:#22d3ee">📎 Drive link submitted</div>
            <?php endif; ?>
          </div>
          <a href="/mentor/submissions/<?= $s['id'] ?>" class="btn btn-sm btn-primary">Grade</a>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

</div>
