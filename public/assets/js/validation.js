/* validation.js — client-side form checks (input validation NFR).
   The server always re-validates: this only improves usability. */

document.addEventListener('DOMContentLoaded', function () {
  /* registration: password length + match */
  var regForm = document.getElementById('register-form');
  if (regForm) {
    regForm.addEventListener('submit', function (e) {
      var pw = regForm.querySelector('[name=password]');
      var cf = regForm.querySelector('[name=password_confirm]');
      var errs = [];
      if (pw.value.length < 8) errs.push('Password must be at least 8 characters.');
      if (pw.value !== cf.value) errs.push('Passwords do not match.');
      if (errs.length) {
        e.preventDefault();
        var box = document.getElementById('register-errors');
        box.className = 'auth-errors';
        box.innerHTML = errs.map(function (x) { return '• ' + x; }).join('<br>');
      }
    });
  }

  /* Branded confirmation dialog for destructive actions (no browser popup). */
  var confirmDialog = document.createElement('div');
  confirmDialog.className = 'smu-modal-backdrop';
  confirmDialog.hidden = true;
  confirmDialog.innerHTML = '<section class="smu-modal" role="dialog" aria-modal="true" aria-labelledby="smu-modal-title"><div class="smu-modal-mark">!</div><h2 id="smu-modal-title">Please confirm</h2><p class="smu-modal-message"></p><div class="smu-modal-actions"><button type="button" class="btn btn-ghost smu-modal-cancel">Cancel</button><button type="button" class="btn btn-danger smu-modal-confirm">Continue</button></div></section>';
  document.body.appendChild(confirmDialog);
  var pendingForm = null;
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (form.dataset.confirmed === 'yes') { form.dataset.confirmed = 'no'; return; }
      event.preventDefault();
      pendingForm = form;
      confirmDialog.querySelector('.smu-modal-message').textContent = form.getAttribute('data-confirm') || 'Are you sure?';
      confirmDialog.hidden = false;
      confirmDialog.querySelector('.smu-modal-cancel').focus();
    });
  });
  confirmDialog.querySelector('.smu-modal-cancel').addEventListener('click', function () {
    confirmDialog.hidden = true; pendingForm = null;
  });
  confirmDialog.querySelector('.smu-modal-confirm').addEventListener('click', function () {
    if (!pendingForm) return;
    var form = pendingForm; pendingForm = null; confirmDialog.hidden = true;
    form.dataset.confirmed = 'yes'; form.requestSubmit();
  });
  confirmDialog.addEventListener('click', function (event) {
    if (event.target === confirmDialog) { confirmDialog.hidden = true; pendingForm = null; }
  });

  /* Accessible show/hide password controls. */
  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      var field = document.getElementById(button.getAttribute('data-password-toggle'));
      if (!field) return;
      var showing = field.type === 'password';
      field.type = showing ? 'text' : 'password';
      button.setAttribute('aria-pressed', showing ? 'true' : 'false');
      button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
      button.innerHTML = showing
        ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.1 0 2.1-.2 3-.5"/></svg>'
        : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
    });
  });

  /* admin exam config: numeric bounds */
  var cfg = document.getElementById('config-form');
  if (cfg) {
    cfg.addEventListener('submit', function (e) {
      var bad = [];
      cfg.querySelectorAll('input[type=number]').forEach(function (i) {
        var min = Number(i.min || 0), max = Number(i.max || 999999);
        var v = Number(i.value);
        if (v < min || v > max) bad.push(i.name + ' must be between ' + min + ' and ' + max);
      });
      if (bad.length) {
        e.preventDefault();
        alert(bad.join('\n'));
      }
    });
  }
});
