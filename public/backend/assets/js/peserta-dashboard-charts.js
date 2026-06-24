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

    function renderCharts() {
        var data = window.pesertaDashboardCharts;
        var theme = chartTheme();

        var elBar = document.querySelector('#chartProgressKursus');
        if (elBar && data.progress.labels.length) {
            if (elBar._chart) {
                elBar._chart.destroy();
            }
            var barHeight = Math.max(220, data.progress.labels.length * 42);
            elBar._chart = new ApexCharts(elBar, {
                series: [{ name: data.labels.progres, data: data.progress.series }],
                chart: {
                    type: 'bar',
                    height: barHeight,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: '52%',
                        distributed: false,
                    },
                },
                colors: [theme.primary],
                dataLabels: {
                    enabled: true,
                    formatter: function (v) { return v + '%'; },
                    style: {
                        fontSize: '10px',
                        fontWeight: 600,
                        colors: ['#fff'],
                    },
                    offsetX: 6,
                },
                xaxis: {
                    max: 100,
                    tickAmount: 5,
                    labels: { style: { colors: theme.text, fontSize: '10px' } },
                },
                yaxis: {
                    labels: {
                        style: { colors: theme.text, fontSize: '10px', fontWeight: 500 },
                        maxWidth: 150,
                    },
                },
                grid: {
                    borderColor: theme.grid,
                    strokeDashArray: 4,
                    padding: { left: 8, right: 16, top: 4, bottom: 0 },
                },
                tooltip: { theme: theme.isDark ? 'dark' : 'light' },
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
                    height: 240,
                    fontFamily: 'inherit',
                },
                labels: [data.labels.aktif, data.labels.selesai],
                colors: [theme.primary, theme.success],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
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
                            size: '68%',
                            labels: {
                                show: true,
                                name: { fontSize: '11px', color: theme.text },
                                value: { fontSize: '15px', fontWeight: 700, color: theme.isDark ? '#e2e8f0' : '#1e293b' },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '10px',
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
