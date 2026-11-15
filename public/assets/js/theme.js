/* theme.js — light/dark mode toggle, persisted in localStorage.
   Dark (campus glass) is the default; ☀️ switches to light, 🌙 back. */

(function () {
  var KEY = 'smu-theme';

  function apply(mode) {
    document.body.classList.toggle('light', mode === 'light');
    var btns = document.querySelectorAll('#theme-toggle');
    for (var i = 0; i < btns.length; i++) {
      btns[i].textContent = (mode === 'light') ? '🌙' : '☀️';
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var saved;
    try { saved = localStorage.getItem(KEY); } catch (e) { saved = null; }
    apply(saved === 'light' ? 'light' : 'dark');

    var btns = document.querySelectorAll('#theme-toggle');
    for (var i = 0; i < btns.length; i++) {
      btns[i].addEventListener('click', function () {
        var next = document.body.classList.contains('light') ? 'dark' : 'light';
        try { localStorage.setItem(KEY, next); } catch (e) { /* private mode */ }
        apply(next);
      });
    }
  });
})();
