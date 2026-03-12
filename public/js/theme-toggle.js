// Theme toggle script for CesiZen
// Moves page theme logic into a separate file. Loaded with `defer`.

document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('theme-toggle');
  if (!btn) return;

  const sunIcon = document.getElementById('icon-sun');
  const moonIcon = document.getElementById('icon-moon');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  const stored = localStorage.getItem('theme');
  const html = document.documentElement;

  function setDark(d) {
    if (d) {
      html.classList.add('dark');
      if (sunIcon) sunIcon.classList.remove('hidden');
      if (moonIcon) moonIcon.classList.add('hidden');
      localStorage.setItem('theme', 'dark');
      btn.setAttribute('aria-pressed', 'true');
    } else {
      html.classList.remove('dark');
      if (sunIcon) sunIcon.classList.add('hidden');
      if (moonIcon) moonIcon.classList.remove('hidden');
      localStorage.setItem('theme', 'light');
      btn.setAttribute('aria-pressed', 'false');
    }
  }

  // Initialize theme: stored value wins, otherwise use OS preference
  if (stored === 'dark' || (!stored && prefersDark)) {
    setDark(true);
  } else {
    setDark(false);
  }

  btn.addEventListener('click', function () {
    setDark(!html.classList.contains('dark'));
  });
});
