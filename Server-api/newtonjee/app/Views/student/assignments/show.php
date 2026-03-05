<?php $pageTitle = htmlspecialchars($assignment['title']); ?>

<div style="margin-bottom:16px">
  <a href="/assignments" class="btn btn-sm btn-secondary">← Back to Assignments</a>
</div>

<?php
$statusColors = [
  'graded'   => ['bg'=>'rgba(52,211,153,.15)','fg'=>'#34d399','label'=>'Graded'],
  'submitted'=> ['bg'=>'rgba(129,140,248,.15)','fg'=>'#818cf8','label'=>'Submitted'],
  'overdue'  => ['bg'=>'rgba(248,113,113,.15)','fg'=>'#f87171','label'=>'Overdue'],
  'pending'  => ['bg'=>'rgba(251,191,36,.15)','fg'=>'#fbbf24','label'=>'Pending'],
];
$s = $statusColors[$assignment['status']] ?? $statusColors['pending'];
$isPastDeadline = strtotime($assignment['deadline']) < time();
$canSubmit = !$isPastDeadline && ($assignment['submission_id'] === null || $assignment['allow_resubmit']);
?>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px">

  <!-- ── Left: Assignment info ─────────────────────────────── -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <div class="card">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:14px">
        <div>
          <div class="page-title" style="margin-bottom:4px"><?= htmlspecialchars($assignment['title']) ?></div>
          <div class="text-sm text-muted"><?= htmlspecialchars($assignment['course_title']) ?></div>
        </div>
        <span class="pill" style="background:<?= $s['bg'] ?>;color:<?= $s['fg'] ?>;flex-shrink:0"><?= $s['label'] ?></span>
      </div>

      <?php if ($assignment['description']): ?>
        <p style="font-size:14px;line-height:1.7;color:var(--text);margin-bottom:14px">
          <?= nl2br(htmlspecialchars($assignment['description'])) ?>
        </p>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;background:var(--surface2);border-radius:10px;font-size:13px">
        <div><span class="text-muted">Deadline:</span><br><strong><?= date('M d, Y · g:i A', strtotime($assignment['deadline'])) ?></strong></div>
        <div><span class="text-muted">Max Score:</span><br><strong><?= $assignment['max_score'] ?> pts</strong></div>
        <div><span class="text-muted">Resubmit:</span><br><strong><?= $assignment['allow_resubmit'] ? 'Allowed' : 'Not allowed' ?></strong></div>
        <div><span class="text-muted">Type:</span><br><strong><?= ucfirst(str_replace('_',' ',$assignment['submission_type'])) ?></strong></div>
      </div>
    </div>

    <!-- Notebook download + Colab -->
    <?php if ($assignment['notebook_path'] || $assignment['colab_url']): ?>
    <div class="card">
      <div class="font-bold" style="margin-bottom:14px">🐍 Starter Notebook</div>
      <p class="text-sm text-muted" style="margin-bottom:14px;line-height:1.6">
        Download the starter <code>.ipynb</code> file, open it in Google Colab, complete the exercises,
        then submit your Google Drive link below.
      </p>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <?php if ($assignment['notebook_path']): ?>
          <a href="/assignments/<?= $assignment['id'] ?>/notebook" class="btn btn-primary">
            ⬇ Download Starter Notebook
          </a>
        <?php endif; ?>
        <?php if ($assignment['colab_url']): ?>
          <a href="<?= htmlspecialchars($assignment['colab_url']) ?>"
             target="_blank" rel="noopener"
             class="btn" style="background:rgba(251,191,36,.15);color:#fbbf24;border:1px solid rgba(251,191,36,.3)">
            ▶ Open in Google Colab
          </a>
        <?php endif; ?>
      </div>
      <div class="text-xs text-muted" style="margin-top:12px;padding:10px;background:var(--surface2);border-radius:8px;line-height:1.6">
        💡 <strong>Steps:</strong> Download → Open in Colab → Complete exercises → 
        File → Save a copy in Drive → Share with 
        <strong><?= htmlspecialchars(MENTOR_DRIVE_EMAIL) ?></strong> (Viewer) → 
        Paste the Drive link below
      </div>
    </div>
    <?php endif; ?>

    <!-- Previous submission / score -->
    <?php if ($assignment['submission_id']): ?>
    <div class="card">
      <div class="font-bold" style="margin-bottom:12px">Your Submission</div>

      <?php if ($assignment['submission_type'] === 'drive_link' && $assignment['drive_url']): ?>
        <div style="margin-bottom:10px">
          <div class="text-xs text-muted" style="margin-bottom:4px">Submitted Drive Link</div>
          <a href="<?= htmlspecialchars($assignment['drive_url']) ?>" target="_blank" rel="noopener"
             class="btn btn-sm btn-secondary">🔗 Open in Google Drive →</a>
        </div>
      <?php elseif ($assignment['file_name']): ?>
        <div style="margin-bottom:10px">
          <div class="text-xs text-muted" style="margin-bottom:4px">Uploaded File</div>
          <span class="pill" style="background:var(--surface2);color:var(--text)"><?= htmlspecialchars($assignment['file_name']) ?></span>
        </div>
      <?php endif; ?>

      <div class="text-xs text-muted">Submitted: <?= date('M d, Y · g:i A', strtotime($assignment['submitted_at'])) ?></div>

      <?php if ($assignment['score'] !== null): ?>
        <div style="margin-top:14px;padding:14px;border-radius:10px;background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2)">
          <div class="text-xs text-muted" style="margin-bottom:4px">Score</div>
          <div class="font-bold" style="font-size:22px;color:#34d399"><?= $assignment['score'] ?> / <?= $assignment['max_score'] ?></div>
          <?php if ($assignment['feedback']): ?>
            <div class="text-sm" style="margin-top:10px;padding-top:10px;border-top:1px solid rgba(52,211,153,.15)">
              <div class="text-xs text-muted" style="margin-bottom:4px">Mentor Feedback</div>
              <?= nl2br(htmlspecialchars($assignment['feedback'])) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div style="margin-top:10px" class="text-xs text-muted">⏳ Awaiting mentor review...</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>

  <!-- ── Right: Submission form ────────────────────────────── -->
  <div>
    <?php if ($canSubmit): ?>
    <div class="card">
      <div class="font-bold" style="margin-bottom:16px">
        <?= $assignment['submission_id'] ? '🔄 Resubmit' : '📤 Submit Assignment' ?>
      </div>

      <form action="/assignments/<?= $assignment['id'] ?>/submit" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">

        <!-- Submission type toggle -->
        <?php if ($assignment['submission_type'] === 'both'): ?>
        <div class="form-group">
          <div class="form-label">Submission Method</div>
          <div style="display:flex;gap:10px">
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="submission_type" value="drive_link" checked style="margin-right:6px">
              <span class="text-sm">Google Drive Link</span>
            </label>
            <label style="flex:1;cursor:pointer">
              <input type="radio" name="submission_type" value="file_upload" style="margin-right:6px">
              <span class="text-sm">Upload File</span>
            </label>
          </div>
        </div>
        <?php else: ?>
          <input type="hidden" name="submission_type" value="<?= htmlspecialchars($assignment['submission_type']) ?>">
        <?php endif; ?>

        <!-- Drive Link section -->
        <?php if (in_array($assignment['submission_type'], ['drive_link','both'])): ?>
        <div id="drive-section">
          <div class="form-group">
            <label class="form-label" for="drive_url">Google Drive / Colab Link</label>
            <input type="url" id="drive_url" name="drive_url" class="form-control"
                   placeholder="https://drive.google.com/file/d/..."
                   value="<?= htmlspecialchars($assignment['drive_url'] ?? '') ?>">
            <div class="form-hint">Paste your Google Drive or Colab shareable link here.</div>
          </div>

          <!-- Share confirmation checkbox -->
          <div style="padding:12px 14px;background:rgba(34,211,238,.07);border:1px solid rgba(34,211,238,.2);border-radius:10px;margin-bottom:14px">
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
              <input type="checkbox" name="shared_confirmed" value="1" required
                     style="margin-top:3px;flex-shrink:0"
                     <?= $assignment['drive_shared_confirmed'] ? 'checked' : '' ?>>
              <span class="text-sm" style="line-height:1.5">
                I confirm I have shared my notebook with
                <strong><?= htmlspecialchars(MENTOR_DRIVE_EMAIL) ?></strong>
                with <em>Viewer</em> access before submitting.
              </span>
            </label>
          </div>
        </div>
        <?php endif; ?>

        <!-- File upload section -->
        <?php if (in_array($assignment['submission_type'], ['file_upload','both'])): ?>
        <div id="upload-section" <?= $assignment['submission_type'] === 'both' ? 'style="display:none"' : '' ?>>
          <div class="form-group">
            <label class="form-label" for="notebook_file">Upload File (.ipynb, .pdf, .zip)</label>
            <input type="file" id="notebook_file" name="notebook_file" class="form-control"
                   accept=".ipynb,.pdf,.zip">
            <div class="form-hint">Max 25 MB. Accepted: .ipynb, .pdf, .zip</div>
          </div>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary w-full" style="justify-content:center">
          <?= $assignment['submission_id'] ? '🔄 Resubmit' : '📤 Submit Assignment' ?>
        </button>
      </form>
    </div>

    <?php elseif ($isPastDeadline && !$assignment['submission_id']): ?>
      <div class="card" style="text-align:center;padding:28px">
        <div style="font-size:36px;margin-bottom:10px">⏰</div>
        <div class="font-bold">Deadline Passed</div>
        <div class="text-sm text-muted" style="margin-top:6px">Submissions are closed for this assignment.</div>
      </div>

    <?php elseif ($assignment['submission_id'] && !$assignment['allow_resubmit']): ?>
      <div class="card" style="text-align:center;padding:28px">
        <div style="font-size:36px;margin-bottom:10px">✅</div>
        <div class="font-bold">Submitted!</div>
        <div class="text-sm text-muted" style="margin-top:6px">Resubmission is not enabled for this assignment.</div>
      </div>
    <?php endif; ?>
  </div>

</div>
