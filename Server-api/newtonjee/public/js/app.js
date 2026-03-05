/* NewtonJEE — app.js */
'use strict';

/* ── Theme ─────────────────────────────────────────────────── */
(function () {
  const saved = localStorage.getItem('nj_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', saved);
  updateThemeBtn(saved);
})();

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next    = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('nj_theme', next);
  updateThemeBtn(next);
}

function updateThemeBtn(theme) {
  const btn = document.querySelector('.theme-toggle');
  if (btn) btn.textContent = theme === 'dark' ? '☀️' : '🌙';
}

/* ── Sidebar (mobile) ──────────────────────────────────────── */
function toggleSidebar() {
  document.getElementById('sidebar')?.classList.toggle('open');
}

// Close sidebar on outside click (mobile)
document.addEventListener('click', function (e) {
  const sidebar = document.getElementById('sidebar');
  const toggle  = document.querySelector('.sidebar-toggle');
  if (sidebar && sidebar.classList.contains('open') &&
      !sidebar.contains(e.target) && e.target !== toggle) {
    sidebar.classList.remove('open');
  }
});

/* ── CSRF helper for fetch() ───────────────────────────────── */
function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function csrfFetch(url, options = {}) {
  options.headers = options.headers || {};
  options.headers['X-CSRF-TOKEN'] = getCsrfToken();
  return fetch(url, options);
}

/* ── Notification polling (every 30 seconds) ─────────────── */
(function startNotifPolling() {
  // Only run if the badge element exists (i.e. student is logged in)
  const badge = document.querySelector('.nav-badge');
  const dot   = document.querySelector('.notif-dot');
  if (!badge && !dot) return;

  function poll() {
    fetch('/api/notifications', { credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        const count = parseInt(data.unread_count || 0);

        // Update sidebar badge
        const b = document.querySelector('.nav-badge');
        if (b) {
          b.textContent = count > 0 ? count : '';
          b.style.display = count > 0 ? 'inline-block' : 'none';
        }

        // Update bell dot
        const d = document.querySelector('.notif-dot');
        if (d) d.style.display = count > 0 ? 'block' : 'none';
      })
      .catch(() => {}); // silently fail
  }

  poll();
  setInterval(poll, 30000);
})();

/* ── Flash message auto-dismiss ────────────────────────────── */
document.querySelectorAll('.flash').forEach(function (el) {
  setTimeout(function () {
    el.style.opacity = '0';
    el.style.transition = 'opacity .4s';
    setTimeout(function () { el.remove(); }, 400);
  }, 5000);
});

/* ── Progress bar animation ────────────────────────────────── */
document.querySelectorAll('.progress-fill[data-pct]').forEach(function (el) {
  const pct = el.getAttribute('data-pct');
  el.style.width = '0%';
  requestAnimationFrame(function () {
    el.style.width = pct + '%';
  });
});

/* ── Confirm dialogs ────────────────────────────────────────── */
document.querySelectorAll('[data-confirm]').forEach(function (el) {
  el.addEventListener('click', function (e) {
    if (!window.confirm(el.getAttribute('data-confirm'))) {
      e.preventDefault();
    }
  });
});

/* ── Drive submission form helpers ─────────────────────────── */
(function () {
  const typeRadios = document.querySelectorAll('input[name="submission_type"]');
  if (!typeRadios.length) return;

  function switchSubmissionType() {
    const selected = document.querySelector('input[name="submission_type"]:checked')?.value;
    const driveSection  = document.getElementById('drive-section');
    const uploadSection = document.getElementById('upload-section');
    if (driveSection)  driveSection.style.display  = selected === 'drive_link'   ? '' : 'none';
    if (uploadSection) uploadSection.style.display = selected === 'file_upload'  ? '' : 'none';
  }

  typeRadios.forEach(r => r.addEventListener('change', switchSubmissionType));
  switchSubmissionType();
})();

/* ── Drive URL live validation ──────────────────────────────── */
(function () {
  const driveInput = document.getElementById('drive_url');
  if (!driveInput) return;

  const hint = document.createElement('div');
  hint.style.cssText = 'font-size:11px;margin-top:4px;';
  driveInput.after(hint);

  const validHosts = ['drive.google.com','docs.google.com','colab.research.google.com','colab.googleapis.com'];

  driveInput.addEventListener('input', function () {
    const val = driveInput.value.trim();
    if (!val) { hint.textContent = ''; return; }
    try {
      const url  = new URL(val);
      const host = url.hostname.toLowerCase();
      if (validHosts.includes(host)) {
        hint.textContent = '✅ Valid Google Drive / Colab URL';
        hint.style.color = '#34d399';
      } else {
        hint.textContent = '⚠ Must be a Google Drive or Colab link';
        hint.style.color = '#f87171';
      }
    } catch {
      hint.textContent = '⚠ Enter a valid URL starting with https://';
      hint.style.color = '#f87171';
    }
  });
})();

/* ── File size check ─────────────────────────────────────────── */
(function () {
  const fileInput = document.getElementById('notebook_file');
  if (!fileInput) return;
  const MAX = 25 * 1024 * 1024;
  fileInput.addEventListener('change', function () {
    const f = fileInput.files[0];
    if (f && f.size > MAX) {
      alert('File is too large. Maximum size is 25 MB.');
      fileInput.value = '';
    }
  });
})();
