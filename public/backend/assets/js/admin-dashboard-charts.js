(function () {
    if (typeof ApexCharts === 'undefined' || !window.adminDashboardCharts) {
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
            muted: 'rgb(148, 163, 184)',
            surface: surfaceColor(),
        };
    }

    function donutOptions(el, series, labels, colors, theme) {
        var total = series.reduce(function (a, b) { return a + b; }, 0);
        return {
            series: total > 0 ? series : [1, 0],
            chart: { type: 'donut', height: 230, fontFamily: 'inherit' },
            labels: labels,
            colors: colors,
            legend: {
                position: 'bottom',
                fontSize: '11px',
                labels: { colors: theme.text },
                markers: { width: 8, height: 8, radius: 4 },
            },
            dataLabels: { enabled: total > 0, style: { fontSize: '11px', fontWeight: 600 } },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            name: { fontSize: '11px', color: theme.text },
                            value: { fontSize: '14px', fontWeight: 700, color: theme.isDark ? '#e2e8f0' : '#1e293b' },
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
        };
    }

    function renderCharts() {
        var data = window.adminDashboardCharts;
        var theme = chartTheme();

        var elPublish = document.querySelector('#chartAdminPublish');
        if (elPublish) {
            if (elPublish._chart) {
                elPublish._chart.destroy();
            }
            elPublish._chart = new ApexCharts(elPublish, donutOptions(
                elPublish,
                data.publish,
                [data.labels.published, data.labels.draft],
                [theme.success, theme.muted],
                theme
            ));
            elPublish._chart.render();
        }

        var elEnrollment = document.querySelector('#chartAdminEnrollment');
        if (elEnrollment) {
            if (elEnrollment._chart) {
                elEnrollment._chart.destroy();
            }
            elEnrollment._chart = new ApexCharts(elEnrollment, donutOptions(
                elEnrollment,
                data.enrollment,
                [data.labels.berlangsung, data.labels.selesai],
                [theme.primary, theme.success],
                theme
            ));
            elEnrollment._chart.render();
        }

        var elTop = document.querySelector('#chartAdminTopCourses');
        if (elTop && data.topCourses.labels.length) {
            if (elTop._chart) {
                elTop._chart.destroy();
            }
            elTop._chart = new ApexCharts(elTop, {
                series: [{ name: data.labels.peserta, data: data.topCourses.series }],
                chart: {
                    type: 'bar',
                    height: 230,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        barHeight: '55%',
                    },
                },
                colors: [theme.primary],
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '10px', fontWeight: 600, colors: ['#fff'] },
                    offsetX: 4,
                },
                xaxis: {
                    labels: { style: { colors: theme.text, fontSize: '10px' } },
                },
                yaxis: {
                    labels: {
                        style: { colors: theme.text, fontSize: '10px' },
                        maxWidth: 110,
                    },
                },
                grid: {
                    borderColor: theme.grid,
                    strokeDashArray: 4,
                    padding: { left: 4, right: 12 },
                },
                tooltip: { theme: theme.isDark ? 'dark' : 'light' },
            });
            elTop._chart.render();
        }
    }

    renderCharts();

    document.addEventListener('click', function (e) {
        if (e.target.closest('.layout-setting, #switcher-dark-theme')) {
            setTimeout(renderCharts, 400);
        }
    });
})();
