/* SMU Exit Exam Tracker - progress page charts (Chart.js 4) */
(function () {
    if (typeof Chart === 'undefined') {
        return; /* chart lib missing - page still works without graphs */
    }

    var SMU_GREEN      = '#1a6b3c';
    var SMU_GREEN_SOFT = 'rgba(26,107,60,0.12)';
    var SMU_NAVY       = '#123c69';

    var pctAxis = {
        min: 0,
        max: 100,
        ticks: {
            callback: function (value) { return value + '%'; }
        }
    };

    var historyCanvas = document.getElementById('historyChart');
    if (historyCanvas && typeof HISTORY !== 'undefined' && HISTORY.length > 0) {
/* SMU v13 chart-theme */ if(window.Chart){ try{ Chart.defaults.color="#ffffff"; Chart.defaults.global.defaultFontColor="#ffffff"; Chart.defaults.borderColor="rgba(255,255,255,.18)"; Chart.defaults.plugins.legend.labels.color="#ffffff"; Chart.defaults.scale.grid.color="rgba(255,255,255,.15)"; }catch(e){} }
        new Chart(historyCanvas, {
            type: 'line',
            data: {
                labels: HISTORY.map(function (h) { return h.label; }),
                datasets: [{
                    label: 'Score %',
                    data: HISTORY.map(function (h) { return h.value; }),
                    borderColor: SMU_GREEN,
                    backgroundColor: SMU_GREEN_SOFT,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: SMU_GREEN
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var kind = HISTORY[ctx.dataIndex].type === 'mock' ? 'Mock exam' : 'Practice';
                                return kind + ': ' + ctx.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: { y: pctAxis }
            }
        });
    }

    var subjectCanvas = document.getElementById('subjectChart');
    if (subjectCanvas && typeof SUBJECTS !== 'undefined' && SUBJECTS.labels.length > 0) {
        new Chart(subjectCanvas, {
            type: 'bar',
            data: {
                labels: SUBJECTS.labels,
                datasets: [{
                    label: 'Average %',
                    data: SUBJECTS.values,
                    backgroundColor: SMU_NAVY,
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
