
/* ============================================================
   SMU FORM UI v2 - validation popup + password eye, all forms
   ============================================================ */
(function () {
  'use strict';

  var EYE = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYE_OFF = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

  var popup = null, popupTimer = null, currentField = null;

  function ensurePopup() {
    if (popup) { return popup; }
    popup = document.createElement('div');
    popup.className = 'field-popup';
    popup.setAttribute('role', 'alert');
    document.body.appendChild(popup);
    return popup;
  }

  function hidePopup() {
    if (popupTimer) { clearTimeout(popupTimer); popupTimer = null; }
    if (popup) { popup.classList.remove('show'); }
    currentField = null;
  }

  function messageFor(el) {
    var custom = el.getAttribute('data-error-msg');
    if (custom) { return custom; }
    var v = el.validity;
    if (!v) { return 'Please check this value'; }
    if (v.valueMissing) {
      if (el.tagName === 'SELECT') { return 'Please select an item in the list'; }
      if (el.type === 'checkbox') { return 'Please check this box if you want to proceed'; }
      if (el.type === 'radio') { return 'Please pick one of these options'; }
      return el.getAttribute('data-required-msg') || 'Please fill out this field';
    }
    if (v.typeMismatch) {
      if (el.type === 'email') { return 'Please enter a valid email address'; }
      if (el.type === 'url') { return 'Please enter a valid web address'; }
      return 'Please match the format requested';
    }
    if (v.patternMismatch) { return el.title || 'Please match the format requested'; }
    if (v.tooShort) { return 'Please lengthen this text to ' + el.minLength + ' characters or more'; }
    if (v.tooLong) { return 'Please shorten this text to ' + el.maxLength + ' characters or fewer'; }
    if (v.rangeUnderflow) { return 'Value must be ' + el.min + ' or greater'; }
    if (v.rangeOverflow) { return 'Value must be ' + el.max + ' or smaller'; }
    if (v.badInput) { return 'Please enter a valid value'; }
    return 'Please check this value';
  }

  function showPopup(el, msg) {
    var p = ensurePopup();
    p.textContent = msg;
    p.classList.remove('show', 'above', 'below');
    p.style.top = '-9999px'; p.style.left = '-9999px';

    var w = p.offsetWidth, h = p.offsetHeight;
    var r = el.getBoundingClientRect();
    var sx = window.pageXOffset || document.documentElement.scrollLeft;
    var sy = window.pageYOffset || document.documentElement.scrollTop;

    var top, side;
    if (r.top - h - 16 > 0) { top = r.top + sy - h - 10; side = 'above'; }
    else { top = r.bottom + sy + 10; side = 'below'; }

    var left = r.left + sx;
    var maxX = sx + (document.documentElement.clientWidth || window.innerWidth) - w - 8;
    if (left > maxX) { left = Math.max(maxX, sx + 8); }
    if (left < sx + 8) { left = sx + 8; }

    p.style.top = Math.round(top) + 'px';
    p.style.left = Math.round(left) + 'px';
    p.classList.add(side);
    void p.offsetWidth;
    p.classList.add('show');

    currentField = el;
    if (popupTimer) { clearTimeout(popupTimer); }
    popupTimer = setTimeout(hidePopup, 4000);
  }

  function flag(el, on) {
    if (on) { el.classList.add('field-error'); }
    else { el.classList.remove('field-error'); }
  }

  function firstInvalid(form) {
    var items = form.querySelectorAll('input, select, textarea');
    for (var i = 0; i < items.length; i++) {
      var el = items[i];
      if (!el.willValidate) { continue; }
      if (!el.checkValidity()) { return el; }
    }
    return null;
  }

  function initForm(form) {
    if (form.getAttribute('data-smu-ui') === '1') { return; }
    form.setAttribute('data-smu-ui', '1');
    form.setAttribute('novalidate', 'novalidate');

    form.addEventListener('submit', function (e) {
      var bad = firstInvalid(form);
      if (bad) {
        e.preventDefault();
        flag(bad, true);
        showPopup(bad, messageFor(bad));
        try { bad.focus(); } catch (err) {}
      }
    });

    form.addEventListener('blur', function (e) {
      var el = e.target;
      if (!el || !el.willValidate) { return; }
      if (!el.checkValidity()) {
        flag(el, true);
        showPopup(el, messageFor(el));
      }
    }, true);

    form.addEventListener('input', function (e) {
      var el = e.target;
      if (el.classList.contains('field-error') && el.checkValidity()) { flag(el, false); }
      if (currentField === el) { hidePopup(); }
    }, true);

    form.addEventListener('change', function (e) {
      var el = e.target;
      if (el.classList.contains('field-error') && el.checkValidity()) { flag(el, false); }
      if (currentField === el) { hidePopup(); }
    }, true);
  }

  /* ---- password eye ---- */
  function initEye(input) {
    if (input.getAttribute('data-smu-eye') === '1') { return; }
    input.setAttribute('data-smu-eye', '1');

    var parent = input.parentNode;
    if (!parent) { return; }
    if (parent.tagName === 'FORM' || parent.tagName === 'BODY') {
      var wrap = document.createElement('span');
      wrap.className = 'smu-pw-wrap';
      parent.insertBefore(wrap, input);
      wrap.appendChild(input);
      parent = wrap;
    } else {
      var pos = '';
      if (window.getComputedStyle) { pos = getComputedStyle(parent).position; }
      if (pos === 'static' || pos === '') { parent.classList.add('smu-has-eye'); }
    }

    input.classList.add('smu-pw-input');

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'smu-eye';
    btn.setAttribute('aria-label', 'Show password');
    btn.tabIndex = -1;
    btn.innerHTML = EYE;

    /* keep focus inside the input so the validation popup never fires */
    btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
    btn.addEventListener('pointerdown', function (e) { e.preventDefault(); });

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var show = (input.type === 'password');
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? EYE_OFF : EYE;
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });

    parent.appendChild(btn);
  }

  function initEyes() {
    var pws = document.querySelectorAll('input[type="password"]');
    for (var i = 0; i < pws.length; i++) { initEye(pws[i]); }
  }

  function boot() {
    var forms = document.forms;
    for (var i = 0; i < forms.length; i++) { initForm(forms[i]); }
    initEyes();
  }

  window.addEventListener('scroll', hidePopup, true);
  window.addEventListener('resize', hidePopup);

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();