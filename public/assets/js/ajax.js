/* ajax.js — Practice Mode engine.
   Pulls the current question from the server (answers are NEVER sent
   to the browser), submits each answer via AJAX and renders the
   immediate feedback. */

(function () {
  var state = {
    sessionId: 0,
    answered: 0,
    total: 0,
    correct: 0,
    finished: false
  };

  var el = {
    qnum: document.getElementById('q-num'),
    qtext: document.getElementById('q-text'),
    opts: document.getElementById('q-options'),
    feedback: document.getElementById('q-feedback'),
    progress: document.getElementById('practice-progress'),
    next: document.getElementById('btn-next'),
    skip: document.getElementById('btn-skip'),
    submit: document.getElementById('btn-submit'),
    counter: document.getElementById('practice-counter')
  };

  function url(route) {
    var base = document.body.getAttribute('data-baseurl') || '';
    return base + '/index.php/' + route;
  }

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function renderQuestion(q) {
    if (!q) { finish(); return; }
    el.qnum.textContent = 'QUESTION ' + (state.answered + 1) + ' OF ' + state.total;
    el.qtext.textContent = q.question_text;
    var letters = ['A', 'B', 'C', 'D'];
    el.opts.innerHTML = '';
    letters.forEach(function (L) {
      var text = q['option_' + L.toLowerCase()];
      var div = document.createElement('div');
      div.className = 'opt';
      div.setAttribute('data-option', L);
      div.innerHTML = '<span class="k">' + L + '</span><span>' + esc(text) + '</span>';
      div.addEventListener('click', function () { answer(q.id, L); });
      el.opts.appendChild(div);
    });
    el.feedback.className = 'feedback';
    el.feedback.textContent = '';
    el.next.style.display = 'none';
  }

  function answer(questionId, selected) {
    if (state.finished) return;
    var body = new URLSearchParams();
    body.set('_csrf', document.body.getAttribute('data-csrf') || '');
    body.set('session', state.sessionId);
    body.set('question', questionId);
    body.set('selected', selected);

    fetch(url('practice/answer'), { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.ok) { el.feedback.textContent = res.error || 'Error saving answer.'; return; }
        // mark options
        el.opts.querySelectorAll('.opt').forEach(function (o) {
          o.classList.add('locked');
          var L = o.getAttribute('data-option');
          if (L === res.correctOption) o.classList.add('correct');
          else if (L === selected && !res.correct) o.classList.add('wrong');
        });
        // feedback panel
        el.feedback.className = 'feedback ' + (res.correct ? 'fb-ok' : 'fb-bad');
        var verdict = res.correct ? '<b>Correct!</b> ' : '<b>Incorrect.</b> The correct answer is <b>' + res.correctOption + '</b>. ';
        el.feedback.innerHTML = verdict + esc(res.explanation || '');
        if (res.correct) state.correct++;
        state.answered++;
        updateProgress();
        el.next.style.display = 'inline-block';
      })
      .catch(function () {
        el.feedback.className = 'feedback fb-bad';
        el.feedback.textContent = 'Network error — please try again.';
      });
  }

  function updateProgress() {
    if (el.progress) el.progress.style.width = Math.round(state.answered / state.total * 100) + '%';
    if (el.counter) el.counter.textContent = state.answered + ' / ' + state.total + ' answered · ' + state.correct + ' correct';
  }

  function next() {
    fetch(url('practice/question?session=' + state.sessionId), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (!res.ok) return;
        if (res.finished) { finish(); return; }
        renderQuestion(res.question);
      });
  }

  function skip() {
    var body = new URLSearchParams();
    body.set('_csrf', document.body.getAttribute('data-csrf') || '');
    body.set('session', state.sessionId);
    fetch(url('practice/skip'), { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function () { state.answered++; updateProgress(); next(); });
  }

  function finish() {
    state.finished = true;
    // submit and go to the results page
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = url('practice/submit');
    form.innerHTML =
      '<input type="hidden" name="_csrf" value="' + (document.body.getAttribute('data-csrf') || '') + '">' +
      '<input type="hidden" name="session" value="' + state.sessionId + '">';
    document.body.appendChild(form);
    form.submit();
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('practice-run')) return;
    state.sessionId = Number(document.getElementById('practice-run').getAttribute('data-session'));
    state.total = Number(document.getElementById('practice-run').getAttribute('data-total'));
    el.next.addEventListener('click', next);
    el.skip.addEventListener('click', skip);
    el.submit.addEventListener('click', finish);
    updateProgress();
    next(); // load the first question
  });
})();
