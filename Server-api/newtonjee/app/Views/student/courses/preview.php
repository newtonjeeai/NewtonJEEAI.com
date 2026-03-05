<?php $pageTitle = htmlspecialchars($course['title']); ?>

<div style="margin-bottom:16px">
  <a href="/courses" class="btn btn-sm btn-secondary">← Back to Catalog</a>
</div>

<?php
$categoryColors = [
  'AI'       => ['bg'=>'linear-gradient(135deg,#312e81,#1e1b4b)','text'=>'#818cf8','icon'=>'🧠'],
  'Robotics' => ['bg'=>'linear-gradient(135deg,#14532d,#052e16)','text'=>'#34d399','icon'=>'🤖'],
];
$cat   = $categoryColors[$course['category']] ?? $categoryColors['AI'];
$levelColors = ['Beginner'=>'#34d399','Intermediate'=>'#fbbf24','Advanced'=>'#f87171'];
$lvlColor = $levelColors[$course['level']] ?? '#818cf8';
?>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start">

  <!-- Left: Course info -->
  <div>
    <!-- Hero banner -->
    <div class="card" style="background:<?= $cat['bg'] ?>;border:none;padding:36px 28px;margin-bottom:16px">
      <div style="font-size:52px;margin-bottom:12px"><?= $cat['icon'] ?></div>
      <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap">
        <span class="pill" style="background:rgba(255,255,255,.15);color:#fff"><?= $course['category'] ?></span>
        <span class="pill" style="background:rgba(255,255,255,.12);color:<?= $lvlColor ?>"><?= $course['level'] ?></span>
        <?php if (!$course['price'] || (float)$course['price'] === 0.0): ?>
          <span class="pill" style="background:rgba(52,211,153,.25);color:#34d399">Free</span>
        <?php endif; ?>
      </div>
      <h1 style="font-size:22px;font-weight:800;color:#fff;margin-bottom:8px;line-height:1.25">
        <?= htmlspecialchars($course['title']) ?>
      </h1>
      <?php if ($course['mentor_name']): ?>
        <div style="display:flex;align-items:center;gap:8px">
          <?php if ($course['mentor_avatar']): ?>
            <img src="<?= htmlspecialchars($course['mentor_avatar']) ?>" style="width:28px;height:28px;border-radius:50%;border:2px solid rgba(255,255,255,.3)">
          <?php endif; ?>
          <span style="font-size:13px;color:rgba(255,255,255,.7)"><?= htmlspecialchars($course['mentor_name']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <!-- Meta row -->
    <div class="card" style="margin-bottom:16px;padding:14px 20px">
      <div style="display:flex;gap:24px;flex-wrap:wrap">
        <?php if ($course['student_count']): ?>
          <div><div class="text-xs text-muted">Students</div><div class="font-bold text-sm"><?= $course['student_count'] ?></div></div>
        <?php endif; ?>
        <?php $modCount = count($modules); if ($modCount): ?>
          <div><div class="text-xs text-muted">Modules</div><div class="font-bold text-sm"><?= $modCount ?></div></div>
        <?php endif; ?>
        <div><div class="text-xs text-muted">Certificate</div><div class="font-bold text-sm">✅ Yes</div></div>
        <div><div class="text-xs text-muted">Level</div><div class="font-bold text-sm" style="color:<?= $lvlColor ?>"><?= $course['level'] ?></div></div>
      </div>
    </div>

    <!-- About -->
    <div class="card" style="margin-bottom:16px">
      <h3 style="margin-bottom:10px">About This Course</h3>
      <p class="text-sm" style="line-height:1.8;color:var(--muted)"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
    </div>

    <!-- Prerequisites -->
    <?php if ($course['prerequisites']): ?>
    <div class="card" style="margin-bottom:16px;border-color:rgba(251,191,36,.2);background:rgba(251,191,36,.04)">
      <h3 style="margin-bottom:10px;color:#fbbf24">Prerequisites</h3>
      <p class="text-sm text-muted">Review required background knowledge before starting this course.</p>
    </div>
    <?php endif; ?>

    <!-- Course Modules -->
    <?php if (!empty($modules)): ?>
    <div class="card">
      <h3 style="margin-bottom:14px">Course Modules</h3>
      <?php foreach ($modules as $i => $m): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)">
          <div style="width:28px;height:28px;border-radius:8px;background:<?= $course['is_enrolled'] && $m['completed_count']==$m['lesson_count'] && $m['lesson_count']>0 ? 'rgba(52,211,153,.2)' : 'rgba(129,140,248,.1)' ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:<?= $course['is_enrolled'] && $m['completed_count']==$m['lesson_count'] && $m['lesson_count']>0 ? '#34d399' : '#818cf8' ?>;flex-shrink:0">
            <?= $course['is_enrolled'] && $m['completed_count']==$m['lesson_count'] && $m['lesson_count']>0 ? '✓' : ($i+1) ?>
          </div>
          <div style="flex:1">
            <div class="font-bold text-sm"><?= htmlspecialchars($m['title']) ?></div>
            <?php if ($course['is_enrolled']): ?>
              <div class="text-xs text-muted"><?= $m['completed_count'] ?>/<?= $m['lesson_count'] ?> lessons completed</div>
            <?php else: ?>
              <div class="text-xs text-muted"><?= $m['lesson_count'] ?> lessons</div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Right: CTA panel -->
  <div style="position:sticky;top:80px">
    <div class="card">
      <?php if ($course['is_enrolled']): ?>
        <div style="margin-bottom:14px">
          <div class="text-xs text-muted font-bold" style="margin-bottom:6px;text-transform:uppercase;letter-spacing:.8px">Your Progress</div>
          <div class="progress-bar" style="height:8px">
            <div class="progress-fill" data-pct="<?= $course['progress_pct'] ?>" style="background:<?= $cat['text'] ?>"></div>
          </div>
          <div class="text-sm font-bold" style="margin-top:6px;color:<?= $cat['text'] ?>"><?= $course['progress_pct'] ?>% complete</div>
        </div>
        <a href="/my-courses" class="btn btn-primary w-full" style="justify-content:center">Continue Learning →</a>
      <?php elseif ((float)$course['price'] > 0): ?>
        <div class="font-mono" style="font-size:26px;font-weight:800;color:#fbbf24;margin-bottom:4px">
          ₹<?= number_format((float)$course['price']) ?>
        </div>
        <div class="text-xs text-muted" style="margin-bottom:16px">Paid enrollment coming in Phase 2</div>
        <button class="btn btn-secondary w-full" style="justify-content:center" disabled>Coming Soon</button>
      <?php else: ?>
        <div class="font-mono" style="font-size:26px;font-weight:800;color:#34d399;margin-bottom:16px">Free</div>
        <form method="POST" action="/courses/<?= $course['id'] ?>/enroll">
          <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">
          <button type="submit" class="btn btn-primary w-full" style="justify-content:center;font-size:15px;padding:13px">
            Enroll Now — Free
          </button>
        </form>
      <?php endif; ?>

      <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
          <div style="text-align:center;padding:10px;background:var(--surface2);border-radius:10px">
            <div style="font-size:18px">📜</div>
            <div class="text-xs text-muted" style="margin-top:3px">Certificate</div>
          </div>
          <div style="text-align:center;padding:10px;background:var(--surface2);border-radius:10px">
            <div style="font-size:18px">🐍</div>
            <div class="text-xs text-muted" style="margin-top:3px">Notebooks</div>
          </div>
          <div style="text-align:center;padding:10px;background:var(--surface2);border-radius:10px">
            <div style="font-size:18px">📅</div>
            <div class="text-xs text-muted" style="margin-top:3px">Flexible</div>
          </div>
          <div style="text-align:center;padding:10px;background:var(--surface2);border-radius:10px">
            <div style="font-size:18px">🏅</div>
            <div class="text-xs text-muted" style="margin-top:3px">Badges</div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
