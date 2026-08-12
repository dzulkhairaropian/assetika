document.addEventListener('DOMContentLoaded', () => {
    const { trendChart, chartStatusData, categoryChart, priorityChart, typeChart, requesterChart } = window.ticketChartData || {};
    const fontFamily = "Outfit, sans-serif";
    const gridBorder = '#e5e7eb';

    const createDonut = (selector, series, labels, colors, totalLabel) => {
        const el = document.querySelector(selector);
        if (!el) return;
        new ApexCharts(el, {
            series, labels, colors,
            chart: { fontFamily, type: 'donut', height: 288 },
            legend: { position: 'bottom', fontFamily },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { show: true },
                            value: { show: true, fontSize: '20px', fontWeight: 'bold' },
                            total: { show: true, label: totalLabel || 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
        }).render();
    };

    const createHBar = (selector, series, labels, color) => {
        const el = document.querySelector(selector);
        if (!el) return;
        new ApexCharts(el, {
            series: [{ name: 'Tickets', data: series }],
            chart: { fontFamily, type: 'bar', height: 288, toolbar: { show: false } },
            colors: [color],
            plotOptions: { bar: { borderRadius: 6, horizontal: true, barHeight: '55%' } },
            xaxis: { categories: labels, labels: { style: { fontFamily } } },
            yaxis: { labels: { style: { fontFamily } } },
            dataLabels: { enabled: false },
            grid: { borderColor: gridBorder, strokeDashArray: 4 },
        }).render();
    };

    // 1. Trend Area Chart
    if (trendChart && document.querySelector('#ticketTrendChart')) {
        new ApexCharts(document.querySelector('#ticketTrendChart'), {
            series: [{ name: 'Tickets', data: trendChart.data }],
            chart: { fontFamily, type: 'area', height: 320, toolbar: { show: false } },
            colors: ['#465FFF'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: trendChart.labels, labels: { style: { fontFamily } } },
            yaxis: { labels: { style: { fontFamily } } },
            dataLabels: { enabled: false },
            grid: { borderColor: gridBorder, strokeDashArray: 4 },
        }).render();
    }

    // 2. Status Donut
    if (chartStatusData) {
        const el = document.querySelector('#ticketStatusChart');
        if (el) {
            new ApexCharts(el, {
                series: chartStatusData.data,
                labels: chartStatusData.labels,
                colors: chartStatusData.colors,
                chart: { fontFamily, type: 'donut', height: 320 },
                legend: { position: 'bottom', fontFamily },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: { show: true },
                                value: { show: true, fontSize: '20px', fontWeight: 'bold' },
                                total: { show: true, label: 'Total', formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
            }).render();
        }
    }

    // 3. Category, 4. Type Donuts
    if (categoryChart) createDonut('#ticketCategoryChart', categoryChart.data, categoryChart.labels, ['#465FFF', '#00F5D4', '#FF9F1C', '#FF5400', '#7209B7'], 'Total');
    if (typeChart) createDonut('#ticketTypeChart', typeChart.data, typeChart.labels, ['#00F5D4', '#7209B7', '#465FFF', '#FF5400', '#FF9F1C'], 'Total');

    // 5. Priority Horizontal Bar
    if (priorityChart) createHBar('#ticketPriorityChart', priorityChart.data, priorityChart.labels, '#F79009');

    // 6. Requester Horizontal Bar
    if (requesterChart) createHBar('#ticketRequesterChart', requesterChart.data, requesterChart.labels, '#12B76A');
});