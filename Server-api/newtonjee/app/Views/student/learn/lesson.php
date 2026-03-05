<?php $pageTitle = htmlspecialchars($lesson['title']); ?>
<meta name="csrf-token" content="<?= $this->generateCsrf() ?>">

<div style="display:grid;grid-template-columns:1fr 280px;gap:0;height:calc(100vh - 60px);margin:-28px -32px">

  <!-- ── LEFT: Lesson content ───────────────────────────── -->
  <div style="overflow-y:auto;padding:28px 28px 40px">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-xs text-muted" style="margin-bottom:16px">
      <a href="/my-courses" style="color:var(--muted)">My Courses</a>
      <span>›</span>
      <a href="/courses/<?= htmlspecialchars($course['slug']) ?>" style="color:var(--muted)"><?= htmlspecialchars($course['title']) ?></a>
      <span>›</span>
      <span style="color:var(--accent)"><?= htmlspecialchars($lesson['module_title']) ?></span>
    </div>

    <!-- Lesson title -->
    <h1 style="font-size:22px;font-weight:800;margin-bottom:6px;letter-spacing:-.3px"><?= htmlspecialchars($lesson['title']) ?></h1>
    <div class="text-xs text-muted" style="margin-bottom:22px">
      <?php
      $typeIcons = ['video'=>'▶ Video','pdf'=>'📄 PDF','text'=>'📖 Reading'];
      echo $typeIcons[$lesson['type']] ?? $lesson['type'];
      if ($lesson['duration_min']) echo ' · ' . $lesson['duration_min'] . ' min';
      ?>
    </div>

    <!-- ── VIDEO ─────────────────────────────────────────────── -->
    <?php if ($lesson['type'] === 'video' && $lesson['content_url']): ?>
      <div style="position:relative;padding-bottom:56.25%;height:0;border-radius:16px;overflow:hidden;margin-bottom:24px;border:1px solid var(--border)">
        <iframe
          id="lesson-video"
          src="<?= htmlspecialchars($lesson['content_url']) ?><?= $progress && $progress['last_position'] > 10 ? '&start=' . $progress['last_position'] : '' ?>"
          title="<?= htmlspecialchars($lesson['title']) ?>"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          style="position:absolute;top:0;left:0;width:100%;height:100%">
        </iframe>
      </div>
      <?php if ($progress && $progress['last_position'] > 10): ?>
        <div style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);border-radius:8px;padding:9px 14px;margin-bottom:16px;font-size:12px;color:#fbbf24">
          ↩ Resuming from <?= gmdate('i:s', $progress['last_position']) ?> — where you left off
        </div>
      <?php endif; ?>

    <!-- ── PDF ───────────────────────────────────────────────── -->
    <?php elseif ($lesson['type'] === 'pdf' && $lesson['content_url']): ?>
      <div style="border-radius:16px;overflow:hidden;border:1px solid var(--border);margin-bottom:24px">
        <embed src="<?= htmlspecialchars($lesson['content_url']) ?>"
               type="application/pdf"
               width="100%"
               height="680px">
      </div>

    <!-- ── TEXT / READING ────────────────────────────────────── -->
    <?php elseif ($lesson['type'] === 'text' && $lesson['content_text']): ?>
      <div class="card" style="margin-bottom:24px;line-height:1.85;font-size:15px">
        <?= nl2br(htmlspecialchars($lesson['content_text'])) ?>
      </div>
    <?php endif; ?>

    <!-- Mark complete / navigation -->
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-top:16px;border-top:1px solid var(--border)">
      <div class="flex gap-2">
        <?php if ($prevLesson): ?>
          <a href="/learn/<?= htmlspecialchars($course['slug']) ?>/<?= $prevLesson['id'] ?>"
             class="btn btn-secondary btn-sm">← Prev</a>
        <?php endif; ?>
        <?php if ($nextLesson): ?>
          <a href="/learn/<?= htmlspecialchars($course['slug']) ?>/<?= $nextLesson['id'] ?>"
             class="btn btn-secondary btn-sm" id="next-btn">Next →</a>
        <?php endif; ?>
      </div>

      <?php if ($progress && $progress['is_completed']): ?>
        <span style="color:#34d399;font-weight:700;font-size:13px">✅ Completed</span>
      <?php else: ?>
        <button id="complete-btn" onclick="markComplete()"
                class="btn btn-success">
          ✓ Mark as Complete
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── RIGHT: Course sidebar ─────────────────────────────── -->
  <div style="border-left:1px solid var(--border);overflow-y:auto;background:var(--surface)">
    <div style="padding:16px 14px;border-bottom:1px solid var(--border)">
      <div class="text-xs text-muted font-bold" style="text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">Course Content</div>
      <div class="text-sm font-bold"><?= htmlspecialchars($course['title']) ?></div>
    </div>

    <?php
    // Group lessons by module
    $modules = [];
    foreach ($allLessons as $l) {
        $modules[$l['module_id']]['title']   = $l['module_title'];
        $modules[$l['module_id']]['lessons'][] = $l;
    }
    foreach ($modules as $modId => $mod):
    ?>
    <div style="border-bottom:1px solid var(--border)">
      <div style="padding:10px 14px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);background:rgba(0,0,0,.1)">
        <?= htmlspecialchars($mod['title']) ?>
      </div>
      <?php foreach ($mod['lessons'] as $l):
        $isActive = $l['id'] == $lessonId;
        $isDone   = $l['is_completed'];
        $typeIcon = ['video'=>'▶','pdf'=>'📄','text'=>'📖'][$l['type']] ?? '•';
      ?>
      <a href="/learn/<?= htmlspecialchars($course['slug']) ?>/<?= $l['id'] ?>"
         style="display:flex;align-items:center;gap:10px;padding:10px 14px;font-size:12px;
                background:<?= $isActive ? 'rgba(129,140,248,.12)' : 'transparent' ?>;
                border-left:3px solid <?= $isActive ? '#818cf8' : 'transparent' ?>;
                color:<?= $isActive ? 'var(--accent)' : ($isDone ? '#34d399' : 'var(--muted)') ?>;
                text-decoration:none">
        <span style="flex-shrink:0"><?= $isDone ? '✓' : $typeIcon ?></span>
        <span style="flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($l['title']) ?></span>
        <?php if ($l['duration_min']): ?>
          <span style="font-size:10px;color:var(--muted);flex-shrink:0"><?= $l['duration_min'] ?>m</span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
async function markComplete() {
  const btn = document.getElementById('complete-btn');
  if (!btn) return;
  btn.disabled = true;
  btn.textContent = 'Saving…';

  try {
    const res = await csrfFetch('/learn/<?= $lesson['id'] ?>/complete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'position=0'
    });
    const data = await res.json();
    if (data.ok) {
      btn.outerHTML = '<span style="color:#34d399;font-weight:700;font-size:13px">✅ Completed</span>';
      // Auto-advance to next lesson after 1.5s
      const nextBtn = document.getElementById('next-btn');
      if (nextBtn) {
        setTimeout(() => { window.location.href = nextBtn.href; }, 1500);
      }
    }
  } catch(e) {
    btn.disabled = false;
    btn.textContent = '✓ Mark as Complete';
  }
}
</script>
