/* SMU Exit Exam Tracker - instructor trends charts (Chart.js 4) */
(function () {
    if (typeof Chart === 'undefined') {
        return;
    }

    var GREEN = '#1a6b3c';
    var NAVY  = '#123c69';
    var pctAxis = {
        min: 0,
        max: 100,
        ticks: { callback: function (value) { return value + '%'; } }
    };

    var trendCanvas = document.getElementById('trendChart');
    if (trendCanvas && typeof TRENDS !== 'undefined' && TRENDS.labels.length > 0) {
/* SMU v13 chart-theme */ if(window.Chart){ try{ Chart.defaults.color="#ffffff"; Chart.defaults.global.defaultFontColor="#ffffff"; Chart.defaults.borderColor="rgba(255,255,255,.18)"; Chart.defaults.plugins.legend.labels.color="#ffffff"; Chart.defaults.scale.grid.color="rgba(255,255,255,.15)"; }catch(e){} }
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: TRENDS.labels,
                datasets: [{
                    label: 'Class average %',
                    data: TRENDS.values,
                    borderColor: NAVY,
                    backgroundColor: 'rgba(18,60,105,0.12)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4
                }]
            },
            options: { responsive: true, scales: { y: pctAxis } }
        });
    }

    var subjCanvas = document.getElementById('subjectTrendChart');
    if (subjCanvas && typeof SUBJAVG !== 'undefined' && SUBJAVG.labels.length > 0) {
        new Chart(subjCanvas, {
            type: 'bar',
            data: {
                labels: SUBJAVG.labels,
                datasets: [{
                    label: 'Average %',
                    data: SUBJAVG.values,
                    backgroundColor: GREEN,
                    borderRadius: 6,
                    maxBarThickness: 70
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: pctAxis }
            }
        });
    }
})();

/* SMU v15 chart-white-hard */
window.addEventListener('load', function () {
  try {
    if (!window.Chart) { return; }
    var fixInst = function (inst) {
      try {
        if (!inst || !inst.options) { return; }
        var o = inst.options;
        o.plugins = o.plugins || {};
        var lg = o.plugins.legend = o.plugins.legend || {};
        lg.labels = lg.labels || {}; lg.labels.color = "#ffffff";
        o.scales = o.scales || {};
        Object.keys(o.scales).forEach(function (ax) {
          try {
            var s = o.scales[ax]; if (!s) { return; }
            s.ticks = s.ticks || {}; s.ticks.color = "#ffffff";
            s.grid = s.grid || {}; s.grid.color = "rgba(255,255,255,.15)";
            if (s.title) { s.title.color = "#ffffff"; }
          } catch (e) {}
        });
        inst.update("none");
      } catch (e) {}
    };
    var regs = window.Chart.instances || {};
    Object.keys(regs).forEach(function (k) { fixInst(regs[k]); });
  } catch (e) {}
});
