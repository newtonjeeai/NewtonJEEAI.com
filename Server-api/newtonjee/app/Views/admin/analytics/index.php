<?php $pageTitle = 'Analytics'; ?>

<div class="page-title">Analytics 📈</div>
<div class="page-subtitle">Platform-wide engagement metrics — <?= date('F Y') ?></div>

<!-- KPI Grid -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
<?php
$kpiCards = [
  ['Students',       $kpi['students'],       '#818cf8','👥'],
  ['Enrollments',    $kpi['enrolled'],        '#22d3ee','📚'],
  ['Completions',    $kpi['completed'],       '#34d399','✅'],
  ['Certificates',   $kpi['certificates'],   '#fbbf24','🏆'],
  ['Submissions',    $kpi['submissions'],    '#a78bfa','📓'],
  ['Avg Score',      ($kpi['avg_score']??'—').'%', '#34d399','⭐'],
  ['Pending Grades', $kpi['pending_grades'], '#f87171','📥'],
  ['Active Courses', $kpi['active_courses'], '#fb923c','📖'],
];
foreach ($kpiCards as [$label,$val,$color,$icon]): ?>
<div class="stat-card">
  <div class="stat-bg-icon"><?= $icon ?></div>
  <div class="stat-label"><?= $label ?></div>
  <div class="stat-value" style="color:<?= $color ?>"><?= $val ?></div>
</div>
<?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">

  <!-- Activity Chart (last 30 days) -->
  <div class="card">
    <strong style="display:block;margin-bottom:14px">Lessons Completed — Last 30 Days</strong>
    <?php if (!empty($recentActivity)):
      $maxVal = max(array_column($recentActivity, 'lessons_completed'));
    ?>
    <div style="display:flex;align-items:flex-end;gap:3px;height:80px">
      <?php foreach ($recentActivity as $day):
        $height = $maxVal > 0 ? round(($day['lessons_completed'] / $maxVal) * 80) : 0;
      ?>
        <div title="<?= $day['day'] ?>: <?= $day['lessons_completed'] ?> lessons"
             style="flex:1;height:<?= $height ?>px;background:#818cf8;border-radius:3px 3px 0 0;opacity:.85;min-height:2px;transition:height .3s"></div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:6px">
      <span class="text-xs text-muted"><?= $recentActivity[0]['day'] ?? '' ?></span>
      <span class="text-xs text-muted"><?= end($recentActivity)['day'] ?? '' ?></span>
    </div>
    <?php else: ?>
    <p class="text-muted text-sm">No activity data yet.</p>
    <?php endif; ?>
  </div>

  <!-- Batch Stats -->
  <div class="card">
    <strong style="display:block;margin-bottom:14px">Batch Breakdown</strong>
    <?php foreach ($batchStats as $b): ?>
    <div style="margin-bottom:12px">
      <div class="flex items-center justify-between" style="margin-bottom:4px">
        <span class="text-sm font-bold"><?= htmlspecialchars($b['batch'] ?? 'No Batch') ?></span>
        <span class="text-xs text-muted"><?= $b['students'] ?> students</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" data-pct="<?= $b['avg_progress'] ?? 0 ?>" style="background:#818cf8"></div>
      </div>
      <div class="text-xs text-muted"><?= $b['avg_progress'] ?? 0 ?>% avg progress</div>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- Course Stats Table -->
<div class="card" style="margin-bottom:16px">
  <strong style="display:block;margin-bottom:14px">Course Performance</strong>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Course</th><th>Category</th><th>Enrolled</th>
          <th>Avg Progress</th><th>Completed</th><th>Submissions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($courseStats as $c): ?>
        <tr>
          <td class="font-bold text-sm"><?= htmlspecialchars($c['title']) ?></td>
          <td><span class="pill" style="background:<?= $c['category']==='AI'?'rgba(129,140,248,.15)':'rgba(52,211,153,.15)' ?>;color:<?= $c['category']==='AI'?'#818cf8':'#34d399' ?>"><?= $c['category'] ?></span></td>
          <td><?= $c['total_enrolled'] ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:6px">
              <div style="width:60px;height:5px;background:var(--surface2);border-radius:100px;overflow:hidden">
                <div style="width:<?= $c['avg_pct'] ?>%;height:100%;background:#818cf8;border-radius:100px"></div>
              </div>
              <span class="text-xs"><?= $c['avg_pct'] ?>%</span>
            </div>
          </td>
          <td><?= $c['completed'] ?></td>
          <td><?= $c['total_submissions'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Top Students -->
<div class="card">
  <strong style="display:block;margin-bottom:14px">Top Students by Progress</strong>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Student</th><th>Batch</th><th>Courses</th><th>Avg Progress</th><th>Completed</th></tr>
      </thead>
      <tbody>
        <?php foreach ($topStudents as $s): ?>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <?php if ($s['avatar_url']): ?>
                <img src="<?= htmlspecialchars($s['avatar_url']) ?>" style="width:26px;height:26px;border-radius:50%">
              <?php else: ?>
                <div style="width:26px;height:26px;border-radius:50%;background:rgba(129,140,248,.2);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#818cf8">
                  <?= strtoupper(substr($s['name'],0,2)) ?>
                </div>
              <?php endif; ?>
              <div>
                <div class="font-bold text-sm"><?= htmlspecialchars($s['name']) ?></div>
                <div class="text-xs text-muted"><?= htmlspecialchars($s['email']) ?></div>
              </div>
            </div>
          </td>
          <td class="text-sm text-muted"><?= htmlspecialchars($s['batch'] ?? '—') ?></td>
          <td><?= $s['courses'] ?></td>
          <td>
            <span style="color:#34d399;font-weight:700"><?= $s['avg_pct'] ?>%</span>
          </td>
          <td><?= $s['completed'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
