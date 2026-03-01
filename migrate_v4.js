const fs = require('fs');
const path = require('path');

const srcDir = __dirname;
const destDir = path.join(srcDir, 'V4');

if (!fs.existsSync(destDir)) {
    fs.mkdirSync(destDir);
}

// 1. Create theme.css
const themeCss = `
/* ── BASE THEME VARIABLES ── */
:root {
  --bg: #0a0c12;
  --bg2: #0f1219;
  --bg3: #151b27;
  --amber: #f59e0b;
  --amber2: #fbbf24;
  --amber-glow: rgba(245,158,11,0.15);
  --cyan: #22d3ee;
  --green: #4ade80;
  --text: #e8eaf0;
  --text-muted: #8892a4;
  --border: rgba(255,255,255,0.07);
  --card-bg: rgba(255,255,255,0.03);
  --card: rgba(255,255,255,0.03);
  
  /* Additional variables from course pages */
  --accent: #f59e0b;
  --border-hover: rgba(245,158,11,0.25);
}

[data-theme="light"] {
  --bg: #f0f4fa;
  --bg2: #e4ecf7;
  --bg3: #dce6f4;
  --amber: #d97706;
  --amber2: #b45309;
  --amber-glow: rgba(217,119,6,0.12);
  --cyan: #0369a1;
  --green: #16a34a;
  --text: #0a1628;
  --text-muted: #425570;
  --border: rgba(10,22,40,0.09);
  --card-bg: #ffffff;
  --card: #ffffff;
  --border-hover: rgba(217,119,6,0.25);
}

/* ── LIGHT MODE OVERRIDES ── */
[data-theme="light"] header { background: rgba(248,250,253,0.97); border-bottom-color: rgba(10,22,40,0.09); }
[data-theme="light"] footer { background: var(--bg2) !important; border-top-color: rgba(10,22,40,0.09) !important; }
[data-theme="light"] .hero { background: var(--bg2); }
[data-theme="light"] .course-card,
[data-theme="light"] .why-tile,
[data-theme="light"] .path-card,
[data-theme="light"] .testi-card,
[data-theme="light"] .cert-step,
[data-theme="light"] .faq-item,
[data-theme="light"] .learn-item,
[data-theme="light"] .outcome-card,
[data-theme="light"] .who-for,
[data-theme="light"] .project-card,
[data-theme="light"] .cert-visual,
[data-theme="light"] .instructor-card,
[data-theme="light"] .schedule-card,
[data-theme="light"] .review-card,
[data-theme="light"] .related-card,
[data-theme="light"] .enrol-card { 
    background: var(--card-bg); 
    border-color: rgba(10,22,40,0.09); 
    box-shadow: 0 2px 12px rgba(10,22,40,0.06); 
}
[data-theme="light"] .course-hero { background: var(--bg2); }
[data-theme="light"] .why-section,
[data-theme="light"] .path-section,
[data-theme="light"] .faq-section { background: var(--bg3); }
[data-theme="light"] .logo svg text:first-child { fill: #0a1628 !important; }
[data-theme="light"] nav a { color: var(--amber); }
[data-theme="light"] h1, [data-theme="light"] h2, [data-theme="light"] h3, [data-theme="light"] .week-header, [data-theme="light"] .text-white { color: var(--text); }
[data-theme="light"] .sticky-bottom { background: rgba(248,250,253,0.97); }
`;
fs.writeFileSync(path.join(destDir, 'theme.css'), themeCss);

// 2. Create theme.js
const themeJs = `
// Immediate theme set to prevent Flash of Unstyled Content
(function() {
  try {
    var sysPrefersLight = window.matchMedia('(prefers-color-scheme: light)').matches;
    var t = localStorage.getItem('njee-theme');
    if (t === 'light' || (!t && sysPrefersLight)) {
      document.documentElement.setAttribute('data-theme', 'light');
    }
  } catch(e) {}
})();

function toggleTheme() {
  var html = document.documentElement;
  var isLight = html.getAttribute('data-theme') === 'light';
  if (isLight) {
    html.removeAttribute('data-theme');
    document.querySelectorAll('.theme-toggle').forEach(function(b) {
      if (b.querySelector('.toggle-icon')) {
        b.querySelector('.toggle-icon').textContent = '☀️';
        b.querySelector('.toggle-label').textContent = 'Light';
      }
    });
    try { localStorage.setItem('njee-theme', 'dark'); } catch(e) {}
  } else {
    html.setAttribute('data-theme', 'light');
    document.querySelectorAll('.theme-toggle').forEach(function(b) {
      if (b.querySelector('.toggle-icon')) {
        b.querySelector('.toggle-icon').textContent = '🌙';
        b.querySelector('.toggle-label').textContent = 'Dark';
      }
    });
    try { localStorage.setItem('njee-theme', 'light'); } catch(e) {}
  }
}

document.addEventListener('DOMContentLoaded', function() {
  if (document.documentElement.getAttribute('data-theme') === 'light') {
    document.querySelectorAll('.theme-toggle').forEach(function(b) {
      if (b.querySelector('.toggle-icon')) {
        b.querySelector('.toggle-icon').textContent = '🌙';
        b.querySelector('.toggle-label').textContent = 'Dark';
      }
    });
  }
});
`;
fs.writeFileSync(path.join(destDir, 'theme.js'), themeJs);

// 3. Process HTML files
const files = fs.readdirSync(srcDir).filter(f => f.endsWith('.html') && fs.statSync(path.join(srcDir, f)).isFile());

files.forEach(file => {
    let content = fs.readFileSync(path.join(srcDir, file), 'utf8');

    // a. Remove any existing :root and [data-theme="light"] from <style> to prevent conflicts
    content = content.replace(/\s*:root\s*\{[^}]*\}/g, '');
    content = content.replace(/\s*\[data-theme="light"\]\s*\{[^}]*\}/g, '');
    // Some files might have specific light overrides like [data-theme="light"] .course-card { ... }
    content = content.replace(/\[data-theme="light"\][^{]+\{[^}]*\}/g, '');

    // Strip empty <style> tags if they get emptied out
    content = content.replace(/<style>\s*<\/style>/g, '');

    // Strip global IIFE for theme
    content = content.replace(/<script>\(function\(\)\{try\{var t=localStorage\.getItem\('njee-theme'\).*?<\/script>/, '');

    // b. Inject theme.css and theme.js in <head>
    if (content.includes('</head>')) {
        content = content.replace('</head>', `
<link rel="stylesheet" href="theme.css">
<script src="theme.js"></script>
</head>`);
    }

    // c. Ensure theme toggle and header-right wrapper
    const toggleHtml = '<button class="theme-toggle" onclick="toggleTheme()"><span class="toggle-icon">☀️</span><span class="toggle-label">Light</span></button>';

    if (content.includes('<header>')) {
        // If it already has .header-right, inject toggle if missing
        if (content.includes('class="header-right"')) {
            if (!content.includes('toggleTheme()')) {
                content = content.replace('<div class="header-right">', `<div class="header-right">\n    ${toggleHtml}`);
            }
        }
        // If it doesn't have .header-right but has a header button
        else if (content.match(/<button[^>]*class="btn-header"[^>]*>.*?<\/button>/)) {
            const btnMatch = content.match(/(<button[^>]*class="btn-header"[^>]*>.*?<\/button>)/);
            if (btnMatch) {
                const wrapper = `<div class="header-right" style="display:flex; align-items:center; gap:12px;">\n    ${toggleHtml}\n    ${btnMatch[1]}\n  </div>`;
                content = content.replace(btnMatch[1], wrapper);
            }
        }
        // If there's no header button
        else {
            content = content.replace('</header>', `<div class="header-right" style="display:flex; align-items:center; gap:12px;">\n    ${toggleHtml}\n  </div>\n</header>`);
        }
    }

    // Strip original toggleTheme from bottom of specific files
    content = content.replace(/function toggleTheme\(\)[\s\S]*?\}\s*document\.addEventListener\('DOMContentLoaded'[\s\S]*?\}\);/g, '');

    // d. Replace hardcoded inline styles. E.g. background: #0a0c12 -> background: var(--bg)
    content = content.replace(/#(?:0a0c12|0A0C12)/g, 'var(--bg)');
    content = content.replace(/#(?:0f1219|0F1219)/g, 'var(--bg2)');
    content = content.replace(/#(?:151b27|151B27)/g, 'var(--bg3)');
    content = content.replace(/#(?:f59e0b|F59E0B)/g, 'var(--amber)');
    content = content.replace(/#(?:fbbf24|FBBF24)/g, 'var(--amber2)');
    content = content.replace(/#(?:22d3ee|22D3EE)/g, 'var(--cyan)');
    content = content.replace(/#(?:e8eaf0|E8EAF0)/g, 'var(--text)');
    content = content.replace(/#(?:8892a4|8892A4)/g, 'var(--text-muted)');
    content = content.replace(/#(?:4ade80|4ADE80)/g, 'var(--green)');

    fs.writeFileSync(path.join(destDir, file), content);
    console.log('Migrated', file);
});

console.log('Migration complete. Files saved in V4 folder.');
