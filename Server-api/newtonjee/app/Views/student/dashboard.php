<?php $pageTitle = 'Dashboard'; ?>

<div class="page-title">Good morning, <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?> 👋</div>
<div class="page-subtitle">You're <?= 100 - $totalPct ?>% away from your next badge. Keep the momentum!</div>

<!-- ── Stat strip ──────────────────────────────────────────── -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-bg-icon">📈</div>
    <div class="stat-label">Overall Progress</div>
    <div class="stat-value" style="color:#818cf8"><?= $totalPct ?>%</div>
    <div class="stat-sub">all courses</div>
  </div>
  <div class="stat-card">
    <div class="stat-bg-icon">📚</div>
    <div class="stat-label">Active Courses</div>
    <div class="stat-value" style="color:#34d399"><?= count($activeCourses) ?></div>
    <div class="stat-sub">enrolled</div>
  </div>
  <div class="stat-card">
    <div class="stat-bg-icon">🏅</div>
    <div class="stat-label">Badges Earned</div>
    <div class="stat-value" style="color:#fbbf24"><?= count(array_filter($badges, fn($b) => $b['earned'])) ?>/<?= count($badges) ?></div>
    <div class="stat-sub">next: AI Developer</div>
  </div>
  <div class="stat-card">
    <div class="stat-bg-icon">⏱</div>
    <div class="stat-label">Hours Learned</div>
    <div class="stat-value" style="color:#f87171"><?= $hoursLearned ?>h</div>
    <div class="stat-sub">lesson time</div>
  </div>
</div>

<!-- ── Two-column main area ────────────────────────────────── -->
<div style="display:grid;grid-template-columns:1fr 300px;gap:18px">

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Active Courses -->
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <strong>Active Courses</strong>
        <a href="/my-courses" class="btn btn-sm btn-secondary">View all →</a>
      </div>
      <?php if (empty($activeCourses)): ?>
        <p class="text-muted text-sm">No courses yet. <a href="/courses">Browse the catalog →</a></p>
      <?php else: foreach ($activeCourses as $c): ?>
        <div class="flex items-center gap-3" style="padding:12px 0;border-bottom:1px solid var(--border)">
          <div style="width:38px;height:38px;border-radius:12px;background:rgba(129,140,248,.15);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">📚</div>
          <div style="flex:1;min-width:0">
            <div class="font-bold text-sm" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              <?= htmlspecialchars($c['title']) ?>
            </div>
            <div class="progress-bar" style="margin:6px 0 2px">
              <div class="progress-fill" data-pct="<?= $c['progress_pct'] ?>" style="background:#818cf8"></div>
            </div>
            <div class="text-xs text-muted"><?= $c['progress_pct'] ?>% complete</div>
          </div>
          <a href="/courses/<?= htmlspecialchars($c['slug']) ?>" class="btn btn-sm btn-secondary" style="flex-shrink:0">Continue</a>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Upcoming Exams -->
    <?php if (!empty($upcomingExams)): ?>
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <strong>Upcoming Exams</strong>
        <a href="/exams" class="btn btn-sm btn-secondary">View all →</a>
      </div>
      <?php foreach ($upcomingExams as $e): ?>
        <div class="flex items-center gap-3" style="padding:10px 0;border-bottom:1px solid var(--border)">
          <div style="width:8px;height:8px;border-radius:50%;background:#818cf8;flex-shrink:0"></div>
          <div style="flex:1">
            <div class="font-bold text-sm"><?= htmlspecialchars($e['title']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($e['exam_date']) ?> · <?= $e['duration_min'] ?> min</div>
          </div>
          <span class="pill" style="background:rgba(129,140,248,.15);color:#818cf8">Upcoming</span>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Badges -->
    <div class="card">
      <strong style="display:block;margin-bottom:14px">Your Badges</strong>
      <div style="display:flex;flex-wrap:wrap;gap:10px">
        <?php foreach ($badges as $b): ?>
          <div title="<?= htmlspecialchars($b['label']) ?>"
               style="width:46px;height:46px;border-radius:14px;
                      background:<?= $b['earned'] ? 'rgba(129,140,248,.15)' : 'var(--surface2)' ?>;
                      border:2px solid <?= $b['earned'] ? 'rgba(129,140,248,.4)' : 'var(--border)' ?>;
                      display:flex;align-items:center;justify-content:center;
                      font-size:22px;opacity:<?= $b['earned'] ? '1' : '.3' ?>">
            <?= $b['icon'] ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Today's Classes -->
    <?php if (!empty($todayEvents)): ?>
    <div class="card">
      <strong style="display:block;margin-bottom:14px">Today's Classes</strong>
      <?php foreach ($todayEvents as $ev): ?>
        <div style="display:flex;gap:10px;margin-bottom:10px">
          <div style="font-size:10px;color:var(--muted);padding-top:4px;min-width:60px;font-family:var(--mono)"><?= htmlspecialchars($ev['time_fmt']) ?></div>
          <div style="flex:1;background:rgba(129,140,248,.1);border-left:3px solid #818cf8;border-radius:0 8px 8px 0;padding:7px 10px">
            <div class="text-sm font-bold"><?= htmlspecialchars($ev['title']) ?></div>
            <?php if ($ev['tag']): ?>
              <span class="pill" style="background:rgba(129,140,248,.15);color:#818cf8;font-size:9px"><?= htmlspecialchars($ev['tag']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
      <a href="/calendar" class="btn btn-sm btn-secondary w-full" style="margin-top:6px;justify-content:center">Open Calendar →</a>
    </div>
    <?php endif; ?>

    <!-- Announcements -->
    <div class="card">
      <div class="flex items-center justify-between" style="margin-bottom:12px">
        <strong>Latest</strong>
        <?php if ($unreadCount > 0): ?>
          <span class="pill" style="background:rgba(248,113,113,.15);color:#f87171"><?= $unreadCount ?> new</span>
        <?php endif; ?>
      </div>
      <?php if (empty($latestAnnouncements)): ?>
        <p class="text-xs text-muted">No announcements yet.</p>
      <?php else: foreach ($latestAnnouncements as $a): ?>
        <div style="padding:10px 12px;margin-bottom:8px;border-radius:10px;
                    background:<?= $a['is_unread'] ? 'rgba(129,140,248,.08)' : 'var(--surface2)' ?>;
                    border:1px solid <?= $a['is_unread'] ? 'rgba(129,140,248,.25)' : 'var(--border)' ?>">
          <div class="text-sm <?= $a['is_unread'] ? 'font-bold' : '' ?>"><?= htmlspecialchars($a['title']) ?></div>
          <div class="text-xs text-muted" style="margin-top:3px"><?= date('M d', strtotime($a['created_at'])) ?></div>
        </div>
      <?php endforeach; endif; ?>
      <a href="/announcements" class="btn btn-sm btn-secondary w-full" style="justify-content:center">All Announcements →</a>
    </div>

  </div>

</div>
