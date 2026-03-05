<?php $pageTitle = 'Grade Submission'; ?>

<div style="margin-bottom:16px">
  <a href="/admin/submissions" class="btn btn-sm btn-secondary">← Back to Submissions</a>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start">

  <!-- Left: submission info -->
  <div>
    <div class="card" style="margin-bottom:14px">
      <div class="flex items-center justify-between" style="margin-bottom:16px">
        <h2 style="font-size:18px"><?= htmlspecialchars($submission['assignment_title']) ?></h2>
        <?php if ($submission['score'] !== null): ?>
          <span class="pill" style="background:rgba(52,211,153,.15);color:#34d399;font-size:12px">Graded</span>
        <?php else: ?>
          <span class="pill" style="background:rgba(251,191,36,.15);color:#fbbf24;font-size:12px">Needs Grading</span>
        <?php endif; ?>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:18px">
        <div style="background:var(--surface2);border-radius:10px;padding:12px">
          <div class="text-xs text-muted" style="margin-bottom:3px">Student</div>
          <div class="font-bold text-sm"><?= htmlspecialchars($submission['student_name']) ?></div>
          <div class="text-xs text-muted"><?= htmlspecialchars($submission['email']) ?></div>
        </div>
        <div style="background:var(--surface2);border-radius:10px;padding:12px">
          <div class="text-xs text-muted" style="margin-bottom:3px">Course</div>
          <div class="font-bold text-sm"><?= htmlspecialchars($submission['course_title']) ?></div>
          <div class="text-xs text-muted">Submitted <?= date('M d, Y · g:i A', strtotime($submission['submitted_at'])) ?></div>
        </div>
      </div>

      <?php if ($submission['assignment_desc']): ?>
        <div style="background:rgba(129,140,248,.06);border:1px solid rgba(129,140,248,.15);border-radius:10px;padding:14px;margin-bottom:16px">
          <div class="text-xs text-muted font-bold" style="margin-bottom:5px;text-transform:uppercase;letter-spacing:.7px">Assignment Instructions</div>
          <p class="text-sm" style="color:var(--muted);line-height:1.7"><?= nl2br(htmlspecialchars($submission['assignment_desc'])) ?></p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Submission content -->
    <div class="card" style="margin-bottom:14px;border-color:rgba(34,211,238,.2)">
      <h3 style="margin-bottom:14px;color:#22d3ee">📓 Student Submission</h3>

      <?php if ($submission['submission_type'] === 'drive_link' && $submission['drive_url']): ?>
        <div style="background:rgba(34,211,238,.06);border:1px solid rgba(34,211,238,.2);border-radius:12px;padding:16px;margin-bottom:12px">
          <div class="text-xs text-muted font-bold" style="margin-bottom:8px;text-transform:uppercase;letter-spacing:.7px">Google Drive / Colab Link</div>
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <code style="font-size:11px;color:#22d3ee;flex:1;word-break:break-all;background:rgba(0,0,0,.2);padding:8px 10px;border-radius:6px">
              <?= htmlspecialchars($submission['drive_url']) ?>
            </code>
            <a href="<?= htmlspecialchars($submission['drive_url']) ?>"
               target="_blank" rel="noopener"
               class="btn btn-sm" style="background:rgba(34,211,238,.15);color:#22d3ee;border:1px solid rgba(34,211,238,.3);flex-shrink:0">
              Open Notebook ↗
            </a>
          </div>
          <?php if ($submission['drive_shared_confirmed']): ?>
            <div class="text-xs" style="color:#34d399;margin-top:8px">
              ✅ Student confirmed shared with mentor@newtonjee.com
            </div>
          <?php else: ?>
            <div class="text-xs" style="color:#fbbf24;margin-top:8px">
              ⚠ Student did not confirm share — verify access before grading
            </div>
          <?php endif; ?>
        </div>

      <?php elseif ($submission['submission_type'] === 'file_upload' && $submission['file_path']): ?>
        <div style="background:rgba(129,140,248,.06);border:1px solid rgba(129,140,248,.2);border-radius:12px;padding:14px">
          <div class="text-xs text-muted" style="margin-bottom:6px">Uploaded File</div>
          <div class="text-sm font-bold"><?= htmlspecialchars($submission['file_name'] ?? 'notebook.ipynb') ?></div>
          <div class="text-xs text-muted" style="margin-top:3px">Download available via admin file access</div>
        </div>
      <?php else: ?>
        <p class="text-muted text-sm">No submission content available.</p>
      <?php endif; ?>
    </div>

    <?php if ($submission['score'] !== null): ?>
    <div class="card" style="border-color:rgba(52,211,153,.2)">
      <h3 style="margin-bottom:12px;color:#34d399">✅ Graded</h3>
      <div class="flex gap-3 items-center" style="margin-bottom:12px">
        <div style="background:rgba(52,211,153,.1);border-radius:12px;padding:10px 18px">
          <span class="font-mono" style="font-size:24px;font-weight:800;color:#34d399"><?= $submission['score'] ?></span>
          <span class="text-muted"> / <?= $submission['max_score'] ?></span>
        </div>
        <div class="text-xs text-muted">Graded by <?= htmlspecialchars($submission['graded_by_name'] ?? 'Admin') ?> on <?= date('M d, Y', strtotime($submission['graded_at'])) ?></div>
      </div>
      <?php if ($submission['feedback']): ?>
        <div style="background:var(--surface2);border-radius:10px;padding:12px">
          <div class="text-xs text-muted" style="margin-bottom:5px">Feedback</div>
          <p class="text-sm" style="line-height:1.7"><?= nl2br(htmlspecialchars($submission['feedback'])) ?></p>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Right: grading form -->
  <div style="position:sticky;top:80px">
    <div class="card">
      <h3 style="margin-bottom:16px"><?= $submission['score'] !== null ? 'Update Grade' : 'Grade Submission' ?></h3>

      <form method="POST" action="/admin/submissions/<?= $submission['id'] ?>/grade">
        <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">

        <div class="form-group">
          <label class="form-label" for="score">Score (0 – <?= $submission['max_score'] ?>)</label>
          <input type="number" name="score" id="score" class="form-control"
                 min="0" max="<?= $submission['max_score'] ?>"
                 value="<?= htmlspecialchars((string)($submission['score'] ?? '')) ?>"
                 placeholder="e.g. 85" required>
          <div class="form-hint">Out of <?= $submission['max_score'] ?> points</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="feedback">Feedback for Student</label>
          <textarea name="feedback" id="feedback" class="form-control" rows="5"
                    placeholder="Great work on the data preprocessing! Consider adding cross-validation in the final cell..."><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-full" style="justify-content:center">
          <?= $submission['score'] !== null ? 'Update Grade' : 'Submit Grade' ?>
        </button>
      </form>
    </div>

    <div class="card" style="margin-top:12px">
      <div class="text-xs text-muted font-bold" style="margin-bottom:8px">Grading Checklist</div>
      <?php
      $checklist = ['Opened the Drive/Colab link','Reviewed all notebook cells','Checked outputs are correct','Ran any incomplete cells','Verified the methodology'];
      foreach ($checklist as $item): ?>
        <label style="display:flex;align-items:center;gap:8px;margin-bottom:8px;cursor:pointer">
          <input type="checkbox" style="accent-color:#818cf8">
          <span class="text-xs text-muted"><?= $item ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

</div>
