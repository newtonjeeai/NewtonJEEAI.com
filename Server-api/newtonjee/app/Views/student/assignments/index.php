<?php $pageTitle = 'Assignments'; ?>

<div class="page-title">Assignments & Projects 📓</div>
<div class="page-subtitle">Download your starter notebook, complete in Google Colab, submit via Drive link.</div>

<?php
$statusColors = [
  'graded'   => ['bg' => 'rgba(52,211,153,.15)',  'fg' => '#34d399',  'label' => 'Graded'],
  'submitted'=> ['bg' => 'rgba(129,140,248,.15)', 'fg' => '#818cf8',  'label' => 'Submitted'],
  'overdue'  => ['bg' => 'rgba(248,113,113,.15)', 'fg' => '#f87171',  'label' => 'Overdue'],
  'pending'  => ['bg' => 'rgba(251,191,36,.15)',  'fg' => '#fbbf24',  'label' => 'Pending'],
];
?>

<?php if (empty($assignments)): ?>
  <div class="card" style="text-align:center;padding:48px">
    <div style="font-size:48px;margin-bottom:12px">📋</div>
    <div class="font-bold" style="margin-bottom:6px">No assignments yet</div>
    <div class="text-muted text-sm">Enrol in a course to see assignments here.</div>
    <a href="/courses" class="btn btn-primary" style="margin-top:20px">Browse Courses →</a>
  </div>
<?php else: ?>
  <div style="display:flex;flex-direction:column;gap:10px">
    <?php foreach ($assignments as $a):
      $s = $statusColors[$a['status']] ?? $statusColors['pending'];
      $isPastDeadline = strtotime($a['deadline']) < time();
    ?>
    <div class="card" style="padding:0;overflow:hidden">
      <div style="display:flex;align-items:stretch">

        <!-- Status bar -->
        <div style="width:4px;background:<?= $s['fg'] ?>;flex-shrink:0;border-radius:16px 0 0 16px"></div>

        <!-- Main content -->
        <div style="flex:1;padding:18px 20px;display:flex;gap:16px;align-items:center">
          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap">
              <a href="/assignments/<?= $a['id'] ?>" class="font-bold" style="color:var(--text);font-size:15px">
                <?= htmlspecialchars($a['title']) ?>
              </a>
              <span class="pill" style="background:<?= $s['bg'] ?>;color:<?= $s['fg'] ?>">
                <?= $s['label'] ?>
              </span>
              <?php if ($a['has_notebook']): ?>
                <span class="pill" style="background:rgba(34,211,238,.1);color:#22d3ee">🐍 Notebook</span>
              <?php endif; ?>
            </div>
            <div class="text-sm text-muted"><?= htmlspecialchars($a['course_title']) ?></div>
            <div class="text-xs text-muted" style="margin-top:4px">
              Deadline: <?= date('M d, Y · g:i A', strtotime($a['deadline'])) ?>
              <?= $isPastDeadline ? ' <span style="color:#f87171">(closed)</span>' : '' ?>
              &nbsp;·&nbsp; Max score: <?= $a['max_score'] ?>
              <?php if ($a['score'] !== null): ?>
                &nbsp;·&nbsp; <strong style="color:#34d399">Score: <?= $a['score'] ?>/<?= $a['max_score'] ?></strong>
              <?php endif; ?>
            </div>
          </div>

          <!-- Actions -->
          <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap">
            <?php if ($a['has_notebook']): ?>
              <a href="/assignments/<?= $a['id'] ?>/notebook"
                 class="btn btn-sm btn-secondary"
                 title="Download starter notebook">⬇ .ipynb</a>
            <?php endif; ?>
            <?php if (!empty($a['colab_url'])): ?>
              <a href="<?= htmlspecialchars($a['colab_url']) ?>"
                 target="_blank" rel="noopener"
                 class="btn btn-sm"
                 style="background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3)">
                 ▶ Open in Colab
              </a>
            <?php endif; ?>
            <a href="/assignments/<?= $a['id'] ?>" class="btn btn-sm btn-primary">
              <?= $a['submission_id'] ? 'View Submission' : 'Submit' ?>
            </a>
          </div>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
