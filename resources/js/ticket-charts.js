document.addEventListener('DOMContentLoaded', () => {
    const { trendChart, chartStatusData, categoryChart, priorityChart, typeChart, requesterChart } = window.ticketChartData || {};

    const chartColors = {
        blue: '#3b82f6',
        green: '#22c55e',
        orange: '#f97316',
        red: '#ef4444',
        purple: '#8b5cf6',
        cyan: '#06b6d4',
    };

    const mountCanvas = (selector) => {
        const container = document.querySelector(selector);
        if (!container) return null;
        container.innerHTML = '<canvas></canvas>';
        return container.querySelector('canvas');
    };

    const createDoughnutConfig = (labels, data, colors, centerLabel = 'Total', centerValue = undefined) => ({
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                hoverOffset: 6,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            onHover: (event, activeElements, chart) => {
                chart.draw();
            },
            plugins: {
                centerText: {
                    label: centerLabel,
                    value: centerValue !== undefined ? centerValue : data.reduce((a, b) => Number(a) + Number(b), 0)
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 14,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        font: { family: 'Outfit, sans-serif', size: 11, weight: '500' },
                        color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#475569'
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
                    titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                    bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                    cornerRadius: 8,
                }
            }
        }
    });

    const createHBarConfig = (labels, data, color) => ({
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: color,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
                    padding: { top: 8, bottom: 8, left: 12, right: 12 },
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    border: { display: false },
                    grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                    ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit, sans-serif', size: 11 } }
                }
            }
        }
    });

    // 1. Trend Line Chart (Crisp Linear Line)
    const trendCanvas = mountCanvas('#ticketTrendChart');
    if (trendChart && trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: trendChart.labels,
                datasets: [{
                    label: 'Tickets',
                    data: trendChart.data,
                    fill: false,
                    borderColor: chartColors.blue,
                    borderWidth: 2,
                    tension: 0,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: chartColors.blue,
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        padding: { top: 8, bottom: 8, left: 12, right: 12 },
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 } }
                    },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                    }
                }
            }
        });
    }

    // 2. Status Donut
    const statusCanvas = mountCanvas('#ticketStatusChart');
    if (chartStatusData && statusCanvas) {
        new Chart(statusCanvas, createDoughnutConfig(chartStatusData.labels, chartStatusData.data, chartStatusData.colors || [chartColors.blue, chartColors.green, chartColors.orange, chartColors.red], 'Status'));
    }

    // 3. Category Donut
    const catCanvas = mountCanvas('#ticketCategoryChart');
    if (categoryChart && catCanvas) {
        new Chart(catCanvas, createDoughnutConfig(categoryChart.labels, categoryChart.data, [chartColors.blue, chartColors.green, chartColors.orange, chartColors.cyan, chartColors.purple], 'Categories'));
    }

    // 4. Type Donut
    const typeCanvas = mountCanvas('#ticketTypeChart');
    if (typeChart && typeCanvas) {
        new Chart(typeCanvas, createDoughnutConfig(typeChart.labels, typeChart.data, [chartColors.cyan, chartColors.purple, chartColors.blue, chartColors.orange, chartColors.green], 'Types'));
    }

    // 5. Priority Horizontal Bar
    const priorityCanvas = mountCanvas('#ticketPriorityChart');
    if (priorityChart && priorityCanvas) {
        new Chart(priorityCanvas, createHBarConfig(priorityChart.labels, priorityChart.data, chartColors.orange));
    }

    // 6. Requester Horizontal Bar
    const reqCanvas = mountCanvas('#ticketRequesterChart');
    if (reqCanvas && requesterChart) {
        new Chart(reqCanvas, createHBarConfig(requesterChart.labels, requesterChart.data, chartColors.green));
    }

    if (window.feather) {
        window.feather.replace();
    }
});

