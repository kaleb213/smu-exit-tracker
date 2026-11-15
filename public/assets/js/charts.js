/* charts.js — lightweight canvas charts (zero dependencies, low bandwidth).
   Data is passed via data-attributes on <canvas> elements. */

function prepCanvas(cv) {
  var dpr = window.devicePixelRatio || 1;
  var w = cv.clientWidth || cv.parentNode.clientWidth || 400;
  var h = cv.clientHeight || 220;
  cv.width = w * dpr; cv.height = h * dpr;
  var g = cv.getContext('2d');
  g.setTransform(dpr, 0, 0, dpr, 0, 0);
  return { g: g, w: w, h: h };
}

function bandColor(v) { return v >= 75 ? '#6fd598' : (v >= 50 ? '#f2c230' : '#ff7b6b'); }

function drawLine(cv, labels, data) {
  var p = prepCanvas(cv), g = p.g, W = p.w, H = p.h, pad = { l: 34, r: 10, t: 14, b: 26 };
  var min = 0, max = 100;
  g.clearRect(0, 0, W, H);
  g.strokeStyle = 'rgba(255,255,255,.14)'; g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '10px Segoe UI'; g.lineWidth = 1;
  for (var v = min; v <= max; v += 25) {
    var y = H - pad.b - (v - min) / (max - min) * (H - pad.t - pad.b);
    g.beginPath(); g.moveTo(pad.l, y); g.lineTo(W - pad.r, y); g.stroke();
    g.fillText(v, 6, y + 3);
  }
  if (data.length === 0) {
    g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '12px Segoe UI'; g.textAlign = 'center';
    g.fillText('No attempts recorded yet', W / 2, H / 2); g.textAlign = 'left'; return;
  }
  var xs = data.map(function (d, i) { return pad.l + i * (W - pad.l - pad.r) / Math.max(1, data.length - 1); });
  var ys = data.map(function (d) { return H - pad.b - (d - min) / (max - min) * (H - pad.t - pad.b); });
  if (data.length > 1) {
    var grd = g.createLinearGradient(0, 0, 0, H);
    grd.addColorStop(0, 'rgba(255,255,255,.22)'); grd.addColorStop(1, 'rgba(255,255,255,0)');
    g.beginPath(); g.moveTo(xs[0], H - pad.b);
    for (var i = 0; i < data.length; i++) g.lineTo(xs[i], ys[i]);
    g.lineTo(xs[xs.length - 1], H - pad.b); g.closePath(); g.fillStyle = grd; g.fill();
    g.beginPath();
    for (var j = 0; j < data.length; j++) { j ? g.lineTo(xs[j], ys[j]) : g.moveTo(xs[j], ys[j]); }
    g.strokeStyle = '#ffffff'; g.lineWidth = 2.5; g.stroke();
  }
  for (var k = 0; k < data.length; k++) {
    g.beginPath(); g.arc(xs[k], ys[k], 4.5, 0, 7);
    g.fillStyle = '#f2c230'; g.fill(); g.strokeStyle = '#fff'; g.lineWidth = 2; g.stroke();
    g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '9.5px Segoe UI'; g.textAlign = 'center';
    g.fillText(labels[k], xs[k], H - 8);
  }
  g.textAlign = 'left';
}

function drawBar(cv, labels, data) {
  var p = prepCanvas(cv), g = p.g, W = p.w, H = p.h, pad = { l: 34, r: 10, t: 14, b: 44 };
  var max = 100; g.clearRect(0, 0, W, H);
  g.strokeStyle = 'rgba(255,255,255,.14)'; g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '10px Segoe UI';
  for (var v = 0; v <= max; v += 25) {
    var y = H - pad.b - v / max * (H - pad.t - pad.b);
    g.beginPath(); g.moveTo(pad.l, y); g.lineTo(W - pad.r, y); g.stroke();
    g.fillText(v, 6, y + 3);
  }
  if (data.length === 0) {
    g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '12px Segoe UI'; g.textAlign = 'center';
    g.fillText('No data yet', W / 2, H / 2); g.textAlign = 'left'; return;
  }
  var n = data.length, bw = Math.min(60, (W - pad.l - pad.r) / n * 0.5);
  for (var i = 0; i < n; i++) {
    var x = pad.l + (i + 0.5) * (W - pad.l - pad.r) / n - bw / 2;
    var h = data[i] / max * (H - pad.t - pad.b);
    var y = H - pad.b - h;
    g.fillStyle = bandColor(data[i]);
    g.beginPath();
    if (g.roundRect) { g.roundRect(x, y, bw, h, [5, 5, 0, 0]); g.fill(); } else { g.fillRect(x, y, bw, h); }
    g.fillStyle = '#ffffff'; g.font = 'bold 10.5px Segoe UI'; g.textAlign = 'center';
    g.fillText(data[i] + '%', x + bw / 2, y - 5);
    g.fillStyle = 'rgba(255,255,255,.55)'; g.font = '9.5px Segoe UI';
    var words = String(labels[i]).split(' ');
    if (words.length > 1) { g.fillText(words[0], x + bw / 2, H - pad.b + 14); g.fillText(words.slice(1).join(' ').slice(0, 18), x + bw / 2, H - pad.b + 25); }
    else g.fillText(labels[i], x + bw / 2, H - pad.b + 14);
  }
  g.textAlign = 'left';
}

function drawDonut(cv, pct) {
  var p = prepCanvas(cv), g = p.g, W = p.w, H = p.h;
  var cx = W / 2, cy = H / 2, r = Math.min(W, H) / 2 - 12;
  g.clearRect(0, 0, W, H);
  g.lineWidth = 16; g.strokeStyle = 'rgba(255,255,255,.20)';
  g.beginPath(); g.arc(cx, cy, r, 0, Math.PI * 2); g.stroke();
  var grd = g.createLinearGradient(0, 0, W, H);
  grd.addColorStop(0, '#ffffff'); grd.addColorStop(1, '#f2c230');
  g.strokeStyle = grd; g.lineCap = 'round';
  g.beginPath(); g.arc(cx, cy, r, -Math.PI / 2, -Math.PI / 2 + Math.PI * 2 * Math.max(0, Math.min(100, pct)) / 100); g.stroke();
  g.fillStyle = '#ffffff'; g.font = 'bold 26px Segoe UI'; g.textAlign = 'center';
  g.fillText(pct + '%', cx, cy + 2);
  g.fillStyle = 'rgba(255,255,255,.6)'; g.font = '10.5px Segoe UI';
  g.fillText('readiness', cx, cy + 20);
  g.textAlign = 'left';
}

function drawAllCharts(root) {
  root = root || document;
  root.querySelectorAll('canvas[data-chart]').forEach(function (cv) {
    var type = cv.getAttribute('data-chart');
    var labels = [], data = [];
    try {
      labels = JSON.parse(cv.getAttribute('data-labels') || '[]');
      data = JSON.parse(cv.getAttribute('data-data') || '[]');
    } catch (e) { /* ignore malformed input */ }
    if (type === 'line') drawLine(cv, labels, data);
    else if (type === 'bar') drawBar(cv, labels, data);
    else if (type === 'donut') drawDonut(cv, Number(cv.getAttribute('data-value') || 0));
  });
}

document.addEventListener('DOMContentLoaded', function () { drawAllCharts(); });
window.addEventListener('resize', function () { drawAllCharts(); });
