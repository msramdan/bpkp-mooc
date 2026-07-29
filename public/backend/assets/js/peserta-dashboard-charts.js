(function () {
    if (typeof ApexCharts === 'undefined' || !window.pesertaDashboardCharts) {
        return;
    }

    function surfaceColor() {
        var isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
        return isDark ? 'rgb(32, 38, 50)' : '#ffffff';
    }

    function chartTheme() {
        var isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
        return {
            isDark: isDark,
            text: isDark ? '#94a3b8' : '#64748b',
            grid: isDark ? 'rgba(148, 163, 184, 0.1)' : '#eef1f6',
            primary: 'rgb(91, 126, 196)',
            success: 'rgb(52, 211, 153)',
            surface: surfaceColor(),
        };
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function truncateLabel(text, max) {
        var value = String(text || '');
        if (value.length <= max) {
            return value;
        }
        return value.slice(0, Math.max(0, max - 1)).trimEnd() + '…';
    }

    function renderCharts() {
        var data = window.pesertaDashboardCharts;
        var theme = chartTheme();
        var fullTitles = data.progress.titles || data.progress.labels || [];

        var elBar = document.querySelector('#chartProgressKursus');
        if (elBar && data.progress.labels.length) {
            if (elBar._chart) {
                elBar._chart.destroy();
            }
            var count = data.progress.labels.length;
            var barHeight = Math.min(360, Math.max(240, count * 52));
            elBar._chart = new ApexCharts(elBar, {
                series: [{ name: data.labels.progres, data: data.progress.series }],
                chart: {
                    type: 'bar',
                    height: barHeight,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    parentHeightOffset: 0,
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: count > 4 ? '62%' : '52%',
                        distributed: false,
                    },
                },
                colors: [theme.primary],
                dataLabels: {
                    enabled: true,
                    formatter: function (v) { return Math.round(v) + '%'; },
                    style: {
                        fontSize: '11px',
                        fontWeight: 600,
                        colors: ['#fff'],
                    },
                    offsetX: 6,
                },
                xaxis: {
                    categories: data.progress.labels,
                    max: 100,
                    tickAmount: 5,
                    labels: {
                        style: { colors: theme.text, fontSize: '11px' },
                        formatter: function (v) { return Math.round(Number(v)) + '%'; },
                    },
                },
                yaxis: {
                    labels: {
                        style: { colors: theme.text, fontSize: '11px', fontWeight: 500 },
                        maxWidth: 150,
                        formatter: function (value) {
                            return truncateLabel(value, 26);
                        },
                    },
                },
                grid: {
                    borderColor: theme.grid,
                    strokeDashArray: 4,
                    padding: { left: 4, right: 18, top: 8, bottom: 4 },
                },
                tooltip: {
                    theme: theme.isDark ? 'dark' : 'light',
                    custom: function (opts) {
                        var index = opts.dataPointIndex;
                        var title = fullTitles[index] || data.progress.labels[index] || '';
                        var value = Math.round(opts.series[opts.seriesIndex][index] || 0);
                        return '' +
                            '<div class="peserta-dash-chart-tip">' +
                            '  <strong>' + escapeHtml(title) + '</strong>' +
                            '  <span>' + value + '%</span>' +
                            '</div>';
                    },
                },
            });
            elBar._chart.render();
        }

        var elDonut = document.querySelector('#chartStatusKursus');
        if (elDonut) {
            if (elDonut._chart) {
                elDonut._chart.destroy();
            }
            var total = data.status[0] + data.status[1];
            elDonut._chart = new ApexCharts(elDonut, {
                series: total > 0 ? data.status : [1, 0],
                chart: {
                    type: 'donut',
                    height: 270,
                    width: 270,
                    fontFamily: 'inherit',
                },
                labels: [data.labels.aktif, data.labels.selesai],
                colors: [theme.primary, theme.success],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    labels: { colors: theme.text },
                    markers: { width: 8, height: 8, radius: 4 },
                },
                dataLabels: {
                    enabled: total > 0,
                    style: { fontSize: '11px', fontWeight: 600 },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: { fontSize: '12px', color: theme.text },
                                value: {
                                    fontSize: '16px',
                                    fontWeight: 700,
                                    color: theme.isDark ? '#e2e8f0' : '#1e293b',
                                    formatter: function (v) { return String(Math.round(Number(v))); },
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    color: theme.text,
                                    formatter: function () { return String(total); },
                                },
                            },
                        },
                    },
                },
                stroke: { width: 3, colors: [theme.surface] },
                tooltip: { theme: theme.isDark ? 'dark' : 'light' },
            });
            elDonut._chart.render();
        }
    }

    renderCharts();

    document.addEventListener('click', function (e) {
        if (e.target.closest('.layout-setting, #switcher-dark-theme')) {
            setTimeout(renderCharts, 400);
        }
    });
})();
