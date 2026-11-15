(function () {
  var el = document.getElementById('countdown');
  if (!el) { return; }

  var remaining = parseInt(el.getAttribute('data-remaining'), 10);
  if (isNaN(remaining) || remaining < 0) { remaining = 0; }

  function pad(n) { return (n < 10 ? '0' : '') + n; }

  function render() {
    var h = Math.floor(remaining / 3600);
    var m = Math.floor((remaining % 3600) / 60);
    var s = remaining % 60;
    el.textContent = (h > 0 ? pad(h) + ':' : '') + pad(m) + ':' + pad(s);
  }

  function finish() {
    el.textContent = '00:00';
    el.classList.add('time-up');
    var form = document.getElementById('exam-form');
    if (form) { form.submit(); }   // auto-submit bypasses the confirm dialog
  }

  render();

  var timer = setInterval(function () {
    remaining--;
    if (remaining <= 0) {
      clearInterval(timer);
      finish();
      return;
    }
    render();
    if (remaining <= 60) { el.classList.add('time-warning'); }
  }, 1000);
})();