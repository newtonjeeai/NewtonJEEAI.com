<?php $pageTitle = 'Course Catalog'; ?>

<div class="page-title">Course Catalog 🗂</div>
<div class="page-subtitle">Browse AI & Robotics courses. All Phase 1 courses are free to enroll.</div>

<!-- Filters -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center">
  <form method="GET" action="/courses" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;flex:1">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
           class="form-control" placeholder="Search courses…" style="max-width:220px;padding:8px 14px">
    <select name="category" class="form-control" style="max-width:140px;padding:8px 14px">
      <?php foreach (['All','AI','Robotics'] as $c): ?>
        <option value="<?= $c ?>" <?= $category===$c?'selected':''?>><?= $c ?></option>
      <?php endforeach; ?>
    </select>
    <select name="level" class="form-control" style="max-width:160px;padding:8px 14px">
      <?php foreach (['All','Beginner','Intermediate','Advanced'] as $l): ?>
        <option value="<?= $l ?>" <?= $level===$l?'selected':''?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    <a href="/courses" class="btn btn-secondary btn-sm">Clear</a>
  </form>
</div>

<?php if (empty($courses)): ?>
  <div class="card" style="text-align:center;padding:48px">
    <div style="font-size:48px;margin-bottom:12px">🔍</div>
    <div class="font-bold">No courses found</div>
    <div class="text-muted text-sm">Try adjusting your filters.</div>
  </div>
<?php else: ?>

<div class="course-grid">
<?php
$categoryColors = [
  'AI'       => ['bg'=>'linear-gradient(135deg,#312e81,#1e1b4b)', 'pill'=>'rgba(129,140,248,.2)', 'text'=>'#818cf8', 'icon'=>'🧠'],
  'Robotics' => ['bg'=>'linear-gradient(135deg,#14532d,#052e16)', 'pill'=>'rgba(52,211,153,.2)',  'text'=>'#34d399', 'icon'=>'🤖'],
];
$levelColors = ['Beginner'=>'#34d399','Intermediate'=>'#fbbf24','Advanced'=>'#f87171'];

foreach ($courses as $c):
  $cat   = $categoryColors[$c['category']] ?? $categoryColors['AI'];
  $lvlCl = $levelColors[$c['level']] ?? '#818cf8';
  $rating = $c['avg_rating'] ? round((float)$c['avg_rating'], 1) : null;
?>
<div class="course-card">

  <div class="course-hero" style="background:<?= $cat['bg'] ?>">
    <span><?= $cat['icon'] ?></span>
    <?php if ($c['is_enrolled']): ?>
      <span class="pill" style="position:absolute;top:10px;right:10px;background:rgba(52,211,153,.2);color:#34d399;font-size:9px">ENROLLED</span>
    <?php endif; ?>
  </div>

  <div class="course-body">
    <div class="course-category" style="color:<?= $cat['text'] ?>"><?= $c['category'] ?></div>
    <div class="course-title"><?= htmlspecialchars($c['title']) ?></div>
    <div class="course-meta">
      <span style="color:<?= $lvlCl ?>;font-weight:700"><?= $c['level'] ?></span>
      <span>· <?= $c['module_count'] ?> modules</span>
      <?php if ($rating): ?><span>· ⭐ <?= $rating ?></span><?php endif; ?>
      <?php if ($c['student_count'] > 0): ?><span>· <?= $c['student_count'] ?> students</span><?php endif; ?>
    </div>
    <?php if ($c['is_enrolled'] && $c['progress_pct']): ?>
      <div class="progress-bar">
        <div class="progress-fill" data-pct="<?= $c['progress_pct'] ?>" style="background:<?= $cat['text'] ?>"></div>
      </div>
      <div class="text-xs text-muted" style="margin-top:2px"><?= $c['progress_pct'] ?>% complete</div>
    <?php endif; ?>
    <div class="text-sm" style="color:<?= (float)$c['price']>0 ? '#fbbf24' : '#34d399' ?>;font-weight:700;margin-top:8px">
      <?= (float)$c['price'] > 0 ? '₹' . number_format((float)$c['price']) : 'Free' ?>
    </div>
  </div>

  <div class="course-actions">
    <a href="/courses/<?= htmlspecialchars($c['slug']) ?>" class="btn btn-secondary btn-sm">👁 Preview</a>
    <?php if ($c['is_enrolled']): ?>
      <a href="/courses/<?= htmlspecialchars($c['slug']) ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center">Continue →</a>
    <?php elseif ((float)$c['price'] > 0): ?>
      <span class="btn btn-sm" style="flex:1;justify-content:center;background:rgba(251,191,36,.1);color:#fbbf24;border:1px solid rgba(251,191,36,.3)">Coming Soon</span>
    <?php else: ?>
      <form method="POST" action="/courses/<?= $c['id'] ?>/enroll" style="flex:1">
        <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">
        <button type="submit" class="btn btn-primary btn-sm w-full" style="justify-content:center">Enroll Free</button>
      </form>
    <?php endif; ?>
  </div>

</div>
<?php endforeach; ?>
</div>

<?php endif; ?>
