(function () {
    if (typeof ApexCharts === 'undefined' || !window.pesertaDashboardCharts) {
        return;
    }

    function chartTheme() {
        var isDark = document.documentElement.getAttribute('data-theme-mode') === 'dark';
        return {
            isDark: isDark,
            text: isDark ? 'rgba(255,255,255,0.7)' : '#64748b',
            grid: isDark ? 'rgba(255,255,255,0.06)' : '#eef1f6',
            primary: 'rgb(43, 72, 139)',
            success: 'rgb(12, 199, 99)',
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
            elBar._chart = new ApexCharts(elBar, {
                series: [{ name: data.labels.progres, data: data.progress.series }],
                chart: {
                    type: 'bar',
                    height: 220,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    sparkline: { enabled: false },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        barHeight: '58%',
                    },
                },
                colors: [theme.primary],
                dataLabels: {
                    enabled: true,
                    formatter: function (v) { return v + '%'; },
                    style: { fontSize: '10px', fontWeight: 600 },
                    offsetX: 4,
                },
                xaxis: {
                    max: 100,
                    labels: { style: { colors: theme.text, fontSize: '10px' } },
                },
                yaxis: {
                    labels: {
                        style: { colors: theme.text, fontSize: '10px' },
                        maxWidth: 140,
                    },
                },
                grid: {
                    borderColor: theme.grid,
                    padding: { left: 4, right: 12, top: 0, bottom: 0 },
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
                chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
                labels: [data.labels.aktif, data.labels.selesai],
                colors: [theme.primary, theme.success],
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    labels: { colors: theme.text },
                    markers: { width: 8, height: 8, radius: 4 },
                },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '11px', fontWeight: 600 },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { fontSize: '11px', color: theme.text },
                                value: { fontSize: '14px', fontWeight: 700 },
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
                stroke: { width: 2, colors: [theme.isDark ? 'rgb(35,34,38)' : '#fff'] },
                tooltip: { theme: theme.isDark ? 'dark' : 'light' },
            });
            elDonut._chart.render();
        }
    }

    renderCharts();

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-theme-mode], .layout-setting, #switcher-dark-theme')) {
            setTimeout(renderCharts, 400);
        }
    });
})();
