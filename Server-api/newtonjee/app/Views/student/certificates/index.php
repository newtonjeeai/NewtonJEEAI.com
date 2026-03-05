<?php $pageTitle = 'My Certificates'; ?>

<div class="page-title">Certificates 🏆</div>
<div class="page-subtitle">Your earned certificates. Download and share with employers and universities.</div>

<?php if (empty($certificates)): ?>
<div class="card" style="text-align:center;padding:56px">
  <div style="font-size:52px;margin-bottom:14px">🏆</div>
  <div class="font-bold" style="font-size:17px">No certificates yet</div>
  <div class="text-muted text-sm" style="margin-top:8px;margin-bottom:24px">
    Complete a course to earn your certificate. Each certificate is verified and downloadable.
  </div>
  <a href="/courses" class="btn btn-primary">Browse Courses →</a>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px">
<?php foreach ($certificates as $cert):
  $catColor = $cert['category'] === 'Robotics' ? '#34d399' : '#818cf8';
  $catBg    = $cert['category'] === 'Robotics' ? 'rgba(52,211,153,.08)' : 'rgba(129,140,248,.08)';
  $catIcon  = $cert['category'] === 'Robotics' ? '🤖' : '🧠';
?>
<div class="card" style="border-color:<?= $catColor ?>33;background:<?= $catBg ?>">
  <!-- Certificate preview strip -->
  <div style="background:linear-gradient(135deg,var(--surface2),var(--surface));
              border-radius:12px;padding:22px 20px;text-align:center;margin-bottom:16px;
              border:1px solid <?= $catColor ?>22">
    <div style="font-size:36px;margin-bottom:8px"><?= $catIcon ?></div>
    <div style="font-size:10px;color:<?= $catColor ?>;font-weight:800;text-transform:uppercase;letter-spacing:2px;margin-bottom:6px">
      Certificate of Completion
    </div>
    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px">
      <?= htmlspecialchars($cert['course_title']) ?>
    </div>
    <div style="font-size:11px;color:var(--muted)">NewtonJEE · <?= date('F Y', strtotime($cert['issued_at'])) ?></div>
  </div>

  <div class="flex gap-2">
    <?php if ($cert['file_path']): ?>
      <a href="/certificates/<?= htmlspecialchars($cert['verify_token']) ?>/download"
         class="btn btn-primary btn-sm" style="flex:1;justify-content:center">
        ⬇ Download PDF
      </a>
    <?php endif; ?>
    <a href="/verify/<?= htmlspecialchars($cert['verify_token']) ?>"
       target="_blank"
       class="btn btn-secondary btn-sm" title="Public verification link"
       style="flex:1;justify-content:center">
      🔗 Verify
    </a>
  </div>

  <div style="margin-top:10px;padding:8px 10px;background:rgba(0,0,0,.15);border-radius:8px">
    <div class="text-xs text-muted" style="margin-bottom:2px">Certificate ID</div>
    <code style="font-size:9px;color:<?= $catColor ?>;word-break:break-all"><?= htmlspecialchars($cert['verify_token']) ?></code>
  </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>
