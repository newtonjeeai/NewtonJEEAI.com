<?php $pageTitle = 'Create Assignment'; ?>

<div style="margin-bottom:16px">
  <a href="/admin/assignments" class="btn btn-sm btn-secondary">← Back to Assignments</a>
</div>

<div class="page-title">Create Assignment 📓</div>
<div class="page-subtitle">Upload a starter Jupyter notebook and configure submission settings.</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

  <form method="POST" action="/admin/assignments/create" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= $this->generateCsrf() ?>">

    <div class="card" style="margin-bottom:14px">
      <h3 style="margin-bottom:16px">Basic Details</h3>

      <div class="form-group">
        <label class="form-label" for="course_id">Course *</label>
        <select name="course_id" id="course_id" class="form-control" required>
          <option value="">— Select a course —</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($_POST['course_id']??'')==$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="title">Assignment Title *</label>
        <input type="text" name="title" id="title" class="form-control"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
               placeholder="e.g. Module 2 — Linear Regression Exercise" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Description / Instructions</label>
        <textarea name="description" id="description" class="form-control" rows="4"
                  placeholder="Describe what students need to do, any specific functions to implement, etc."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label" for="deadline">Deadline *</label>
          <input type="datetime-local" name="deadline" id="deadline" class="form-control"
                 value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="max_score">Max Score</label>
          <input type="number" name="max_score" id="max_score" class="form-control"
                 value="<?= htmlspecialchars($_POST['max_score'] ?? '100') ?>" min="1" max="1000">
        </div>
      </div>
    </div>

    <!-- Notebook settings -->
    <div class="card" style="margin-bottom:14px;border-color:rgba(34,211,238,.2)">
      <h3 style="margin-bottom:4px;color:#22d3ee">🐍 Jupyter Notebook Settings</h3>
      <p class="text-xs text-muted" style="margin-bottom:16px">
        Upload a starter .ipynb file that students can download and open in Google Colab.
      </p>

      <div class="form-group">
        <label class="form-label" for="notebook_file">Starter Notebook (.ipynb)</label>
        <input type="file" name="notebook_file" id="notebook_file" class="form-control"
               accept=".ipynb" style="padding:8px">
        <div class="form-hint">
          Must be a valid Jupyter Notebook (.ipynb). Max 25 MB. Stored securely outside web root.
          Students download via authenticated endpoint only.
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="colab_url">Google Colab URL (optional)</label>
        <input type="url" name="colab_url" id="colab_url" class="form-control"
               value="<?= htmlspecialchars($_POST['colab_url'] ?? '') ?>"
               placeholder="https://colab.research.google.com/drive/...">
        <div class="form-hint">
          If provided, students will see an "Open in Colab" button that launches this notebook directly.
          Use this if you want to host the notebook on your Google Drive instead of uploading here.
        </div>
      </div>
    </div>

    <!-- Submission settings -->
    <div class="card" style="margin-bottom:14px">
      <h3 style="margin-bottom:16px">Submission Settings</h3>

      <div class="form-group">
        <label class="form-label">Submission Type</label>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach (['drive_link'=>'Google Drive link only (recommended)','file_upload'=>'File upload only (.ipynb / PDF)','both'=>'Both methods allowed'] as $val => $desc): ?>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;border-radius:10px;border:1px solid var(--border);background:var(--surface2)">
              <input type="radio" name="submission_type" value="<?= $val ?>"
                     <?= ($_POST['submission_type']??'drive_link')===$val?'checked':'' ?>>
              <span class="text-sm"><?= $desc ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px;border-radius:10px;background:var(--surface2);border:1px solid var(--border)">
        <input type="checkbox" name="allow_resubmit" value="1" <?= isset($_POST['allow_resubmit'])?'checked':'' ?>>
        <div>
          <div class="text-sm font-bold">Allow Resubmission</div>
          <div class="text-xs text-muted">Students can update their submission before the deadline</div>
        </div>
      </label>
    </div>

    <div class="flex gap-2">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:11px 16px;background:var(--surface2);border:1px solid var(--border);border-radius:10px">
        <input type="checkbox" name="is_published" value="1" <?= isset($_POST['is_published'])?'checked':'' ?>>
        <span class="text-sm">Publish immediately</span>
      </label>
      <button type="submit" class="btn btn-primary">Create Assignment</button>
    </div>
  </form>

  <!-- Sidebar guide -->
  <div style="position:sticky;top:80px">
    <div class="card" style="border-color:rgba(34,211,238,.2)">
      <h4 style="margin-bottom:12px;color:#22d3ee">📋 Notebook Workflow</h4>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php
        $steps = [
          ['1','Upload your .ipynb starter here','#22d3ee'],
          ['2','Students download the notebook from their Assignments page','#818cf8'],
          ['3','They click "Open in Colab" (if Colab URL is set) or open locally','#fb923c'],
          ['4','They complete the notebook in Colab','#fbbf24'],
          ['5','They share with <strong>mentor@newtonjee.com</strong> (Viewer access)','#f87171'],
          ['6','They paste the Drive link in the submission form','#34d399'],
          ['7','You open the link here and grade it','#a78bfa'],
        ];
        foreach ($steps as [$n, $text, $color]): ?>
          <div style="display:flex;gap:10px;align-items:flex-start">
            <div style="width:22px;height:22px;border-radius:50%;background:<?= $color ?>;color:#000;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px"><?= $n ?></div>
            <div class="text-xs text-muted" style="line-height:1.5"><?= $text ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card" style="margin-top:12px;background:rgba(248,113,113,.05);border-color:rgba(248,113,113,.2)">
      <h4 style="margin-bottom:8px;color:#f87171;font-size:13px">⚠ Security Note</h4>
      <p class="text-xs text-muted" style="line-height:1.6">
        Uploaded notebooks are stored <strong>outside the web root</strong> at
        <code style="background:rgba(255,255,255,.05);padding:1px 4px;border-radius:4px">/var/www/private/notebooks/</code>.
        Students access them only via an authenticated PHP download endpoint.
      </p>
    </div>
  </div>
</div>
