
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
