/* Lightweight account presence heartbeat (online / away / offline). */
(function () {
  const body = document.body;
  if (!body || body.dataset.presenceEnabled !== '1') return;

  const endpoint = body.dataset.presenceUrl;
  const csrf = body.dataset.csrf;
  const dot = document.querySelector('[data-presence-dot]');
  if (!endpoint || !csrf || !dot) return;

  let lastActivity = Date.now();
  let lastSentStatus = '';
  let requestInFlight = false;
  const idleAfterMs = 2 * 60 * 1000;

  function setDot(status) {
    dot.dataset.status = status;
    const labels = { online: 'Online', away: 'Away', offline: 'Offline' };
    dot.title = labels[status] || 'Offline';
    dot.setAttribute('aria-label', labels[status] || 'Offline');
  }

  function currentStatus() {
    if (document.visibilityState === 'hidden') return 'away';
    return Date.now() - lastActivity >= idleAfterMs ? 'away' : 'online';
  }

  async function send(status, force) {
    if (requestInFlight || (!force && status === lastSentStatus)) return;
    requestInFlight = true;
    const payload = new FormData();
    payload.append('_csrf', csrf);
    payload.append('status', status);
    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        body: payload,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (response.ok) {
        const result = await response.json();
        if (result && result.ok) {
          lastSentStatus = status;
          setDot(status);
        }
      }
    } catch (error) {
      // If the heartbeat cannot reach the application, do not show a false green dot.
      setDot('offline');
    } finally {
      requestInFlight = false;
    }
  }

  function activity() {
    lastActivity = Date.now();
    if (document.visibilityState === 'visible') send('online', false);
  }

  ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach(function (eventName) {
    window.addEventListener(eventName, activity, { passive: true });
  });

  window.addEventListener('offline', function () { setDot('offline'); });
  window.addEventListener('online', function () {
    lastActivity = Date.now();
    send('online', true);
  });

  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'visible') {
      lastActivity = Date.now();
      send('online', true);
    } else {
      send('away', true);
    }
  });

  // Best-effort offline signal when the tab is closed or navigated away.
  window.addEventListener('pagehide', function () {
    const payload = new FormData();
    payload.append('_csrf', csrf);
    payload.append('status', 'offline');
    if (navigator.sendBeacon) {
      navigator.sendBeacon(endpoint, payload);
    } else {
      fetch(endpoint, { method: 'POST', body: payload, credentials: 'same-origin', keepalive: true });
    }
  });

  setDot('online');
  send('online', true);
  window.setInterval(function () {
    send(currentStatus(), true);
  }, 30000);
})();
