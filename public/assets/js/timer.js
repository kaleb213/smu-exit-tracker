/* timer.js — Mock Exam countdown (client-side timer backed by a
   server-side deadline). When time runs out the exam auto-submits. */

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('mock-timer');
    if (!el) return;

    var remaining = Number(el.getAttribute('data-remaining') || 0);
    var autoSubmit = document.getElementById('mock-form');

    function pad(n) { return n < 10 ? '0' + n : String(n); }

    function tick() {
      if (remaining <= 0) {
        el.textContent = 'TIME UP';
        el.classList.add('danger');
        if (autoSubmit && !autoSubmit.dataset.submitted) {
          autoSubmit.dataset.submitted = '1';
          autoSubmit.submit(); // auto-submit on deadline
        }
        return;
      }
      var h = Math.floor(remaining / 3600);
      var m = Math.floor((remaining % 3600) / 60);
      var s = remaining % 60;
      el.textContent = (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(s);
      // danger: red + pulse once under 5 minutes remain
      if (remaining <= 300) el.classList.add('danger');
      remaining--;
    }

    tick();
    setInterval(tick, 1000);
  });
})();
