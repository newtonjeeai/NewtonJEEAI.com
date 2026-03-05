<?php $pageTitle = 'Admin Dashboard'; ?>

<div class="page-title">Admin Dashboard 📊</div>
<div class="page-subtitle">NewtonJEE portal overview — <?= date('l, F j, Y') ?></div>

<!-- Stats -->
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <?php
  $statCards = [
    ['Total Students',   $stats['total_students'],    '#818cf8', '👥'],
    ['Active Courses',   $stats['active_courses'],    '#34d399', '📚'],
    ['Certificates',     $stats['certificates'],      '#fbbf24', '🏆'],
    ['Pending Grades',   $stats['pending_grades'],    '#f87171', '📥'],
    ['Enrollments',      $stats['total_enrollments'], '#22d3ee', '📊'],
    ['Avg Completion',   $stats['avg_completion'].'%',$stats['avg_completion']>=60?'#34d399':'#fbbf24', '📈'],
  ];
  foreach ($statCards as [$label, $val, $color, $icon]): ?>
  <div class="stat-card">
    <div class="stat-bg-icon"><?= $icon ?></div>
    <div class="stat-label"><?= $label ?></div>
    <div class="stat-value" style="color:<?= $color ?>"><?= $val ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

  <!-- Pending Submissions -->
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <strong>Pending Grades</strong>
      <a href="/admin/submissions?filter=pending" class="btn btn-sm btn-secondary">View all →</a>
    </div>
    <?php if (empty($recentSubmissions)): ?>
      <p class="text-muted text-sm">No pending submissions. 🎉</p>
    <?php else: foreach ($recentSubmissions as $s): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-sm font-bold"><?= htmlspecialchars($s['student']) ?></div>
            <div class="text-xs text-muted"><?= htmlspecialchars($s['assignment']) ?> · <?= htmlspecialchars($s['course']) ?></div>
          </div>
          <a href="/admin/submissions/<?= $s['id'] ?>" class="btn btn-sm btn-primary">Grade</a>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Course Engagement -->
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <strong>Course Engagement</strong>
      <a href="/admin/analytics" class="btn btn-sm btn-secondary">Analytics →</a>
    </div>
    <?php foreach ($courseEngagement as $c): ?>
      <div style="padding:10px 0;border-bottom:1px solid var(--border)">
        <div class="flex items-center justify-between" style="margin-bottom:5px">
          <div class="text-sm font-bold"><?= htmlspecialchars($c['title']) ?></div>
          <span class="text-xs text-muted"><?= $c['enrolled'] ?> enrolled</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" data-pct="<?= $c['avg_progress'] ?>"
               style="background:<?= $c['category']==='AI' ? '#818cf8' : '#34d399' ?>"></div>
        </div>
        <div class="text-xs text-muted" style="margin-top:2px">
          <?= $c['avg_progress'] ?>% avg · <?= $c['completed'] ?> completed
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Recent Students -->
  <div class="card" style="grid-column:span 2">
    <div class="flex items-center justify-between mb-4">
      <strong>Recent Students</strong>
      <a href="/admin/users" class="btn btn-sm btn-secondary">Manage all →</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Batch</th>
            <th>Joined</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentStudents as $s): ?>
          <tr>
            <td>
              <div class="flex items-center gap-2">
                <?php if ($s['avatar_url']): ?>
                  <img src="<?= htmlspecialchars($s['avatar_url']) ?>" style="width:28px;height:28px;border-radius:50%">
                <?php else: ?>
                  <div style="width:28px;height:28px;border-radius:50%;background:rgba(129,140,248,.2);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#818cf8">
                    <?= strtoupper(substr($s['name'],0,2)) ?>
                  </div>
                <?php endif; ?>
                <span class="font-bold text-sm"><?= htmlspecialchars($s['name']) ?></span>
              </div>
            </td>
            <td class="text-muted text-sm"><?= htmlspecialchars($s['email']) ?></td>
            <td><span class="pill" style="background:rgba(129,140,248,.1);color:#818cf8"><?= htmlspecialchars($s['batch'] ?? '—') ?></span></td>
            <td class="text-muted text-sm"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
            <td><a href="/admin/users/<?= $s['id'] ?>" class="btn btn-sm btn-secondary">View</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
