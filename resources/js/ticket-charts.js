document.addEventListener('DOMContentLoaded', () => {
    const { trendChart, chartStatusData, categoryChart, priorityChart, typeChart, requesterChart } = window.ticketChartData || {};

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
                borderColor: 'transparent',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            onHover: (event, activeElements, chart) => {
                chart.draw();
            },
            plugins: {
                centerText: {
                    label: centerLabel,
                    value: centerValue
                },
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: { family: 'Outfit, sans-serif', size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Outfit, sans-serif', size: 13, weight: 'bold' },
                    bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                    padding: 10,
                    cornerRadius: 8
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
                borderRadius: 6,
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
                    titleFont: { family: 'Outfit, sans-serif' },
                    bodyFont: { family: 'Outfit, sans-serif' },
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    border: { dash: [4, 4] },
                    grid: { color: 'rgba(226, 232, 240, 0.6)' },
                    ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit, sans-serif', size: 11 } }
                }
            }
        }
    });

    // 1. Trend Line Chart (Crisp Sharp Line)
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
                    borderColor: '#465fff',
                    borderWidth: 2,
                    tension: 0,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#465fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#465fff'
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
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: true, color: '#e2e8f0' },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 } }
                    },
                    y: {
                        border: { dash: [4, 4], display: false },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                    }
                }
            }
        });
    }

    // 2. Status Donut
    const statusCanvas = mountCanvas('#ticketStatusChart');
    if (chartStatusData && statusCanvas) {
        new Chart(statusCanvas, createDoughnutConfig(chartStatusData.labels, chartStatusData.data, chartStatusData.colors || ['#465fff', '#12b76a', '#f79009', '#f04438']));
    }

    // 3. Category Donut
    const catCanvas = mountCanvas('#ticketCategoryChart');
    if (categoryChart && catCanvas) {
        new Chart(catCanvas, createDoughnutConfig(categoryChart.labels, categoryChart.data, ['#465fff', '#12b76a', '#f79009', '#06b6d4', '#8b5cf6']));
    }

    // 4. Type Donut
    const typeCanvas = mountCanvas('#ticketTypeChart');
    if (typeChart && typeCanvas) {
        new Chart(typeCanvas, createDoughnutConfig(typeChart.labels, typeChart.data, ['#06b6d4', '#8b5cf6', '#465fff', '#f79009', '#12b76a']));
    }

    // 5. Priority Horizontal Bar
    const priorityCanvas = mountCanvas('#ticketPriorityChart');
    if (priorityChart && priorityCanvas) {
        new Chart(priorityCanvas, createHBarConfig(priorityChart.labels, priorityChart.data, '#f79009'));
    }

    // 6. Requester Horizontal Bar
    const reqCanvas = mountCanvas('#ticketRequesterChart');
    if (requesterChart && reqCanvas) {
        new Chart(reqCanvas, createHBarConfig(requesterChart.labels, requesterChart.data, '#12b76a'));
    }
});
