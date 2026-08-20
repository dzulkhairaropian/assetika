document.addEventListener('DOMContentLoaded', () => {
    const { totalAssets, chartData, deptChartData, productDataByCategory, consumableChartData, purchaseChartData } = window.chartData || {};

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

    const mountCanvas = (selector) => {
        const container = document.querySelector(selector);
        if (!container) return null;
        container.innerHTML = '<canvas></canvas>';
        return container.querySelector('canvas');
    };

    // 1. Asset Status Chart
    const statusCanvas = mountCanvas("#assetStatusChart");
    if (statusCanvas && chartData) {
        window.statusChartInstance = new Chart(statusCanvas, createDoughnutConfig(chartData.labels, chartData.data, chartData.colors || ['#465fff', '#12b76a', '#f79009', '#f04438'], 'Total Assets', totalAssets));

        window.updateStatusChartJS = (catId) => {
            const map = window.chartData?.chartDataByCategory;
            const newData = (map && map[catId]) || { labels: ['No Data'], data: [0] };
            if (window.statusChartInstance) {
                window.statusChartInstance.data.labels = newData.labels;
                window.statusChartInstance.data.datasets[0].data = newData.data;
                const total = newData.data.reduce((a, b) => Number(a) + Number(b), 0);
                window.statusChartInstance.options.plugins.centerText.value = total;
                window.statusChartInstance.update();
            }
        };
    }

    // 2. Department Chart
    const deptCanvas = mountCanvas("#deptAssetChart");
    if (deptCanvas && deptChartData && deptChartData.data?.length) {
        window.deptChartInstance = new Chart(deptCanvas, createDoughnutConfig(deptChartData.labels, deptChartData.data, ['#465fff', '#12b76a', '#f79009', '#3b82f6', '#8b5cf6']));

        window.updateDeptChartJS = (catId) => {
            const map = window.chartData?.deptDataByCategory;
            const newData = (map && map[catId]) || { labels: ['No Data'], data: [0] };
            if (window.deptChartInstance) {
                window.deptChartInstance.data.labels = newData.labels;
                window.deptChartInstance.data.datasets[0].data = newData.data;
                const total = newData.data.reduce((a, b) => Number(a) + Number(b), 0);
                window.deptChartInstance.options.plugins.centerText.value = total;
                window.deptChartInstance.update();
            }
        };
    }

    // 3. Product Chart
    const productCanvas = mountCanvas("#productAssetChart");
    if (productCanvas && productDataByCategory && productDataByCategory.all) {
        window.productChartDataMap = productDataByCategory;
        const initialData = productDataByCategory.all;
        window.productChartInstance = new Chart(productCanvas, createDoughnutConfig(initialData.labels, initialData.data, ['#465fff', '#12b76a', '#f79009', '#06b6d4', '#8b5cf6']));

        // Adapter method for Alpine dropdown filter
        window.updateProductChartJS = (catId) => {
            const newData = (window.productChartDataMap && window.productChartDataMap[catId]) || { labels: ['No Data'], data: [0] };
            if (window.productChartInstance) {
                window.productChartInstance.data.labels = newData.labels;
                window.productChartInstance.data.datasets[0].data = newData.data;
                window.productChartInstance.update();
            }
        };
    }

    // 4. Consumables Chart
    const consumableCanvas = mountCanvas("#requestedConsumablesChart");
    if (consumableCanvas && consumableChartData && consumableChartData.data?.length) {
        window.consumableChartInstance = new Chart(consumableCanvas, createDoughnutConfig(consumableChartData.labels, consumableChartData.data, ['#465fff', '#f79009', '#12b76a', '#ec4899', '#8b5cf6']));

        window.updateConsumableChartJS = (catId) => {
            const map = window.chartData?.consumableDataByCategory;
            const newData = (map && map[catId]) || { labels: ['No Data'], data: [0] };
            if (window.consumableChartInstance) {
                window.consumableChartInstance.data.labels = newData.labels;
                window.consumableChartInstance.data.datasets[0].data = newData.data;
                const total = newData.data.reduce((a, b) => Number(a) + Number(b), 0);
                window.consumableChartInstance.options.plugins.centerText.value = total;
                window.consumableChartInstance.update();
            }
        };
    }

    // 5. Consumable Usage Timeline Chart (Crisp Sharp Line)
    const consumableTLCanvas = mountCanvas("#consumableTimelineChart");
    const { consumableTimelineChartData } = window.chartData || {};
    if (consumableTLCanvas && consumableTimelineChartData && consumableTimelineChartData.data?.length) {
        window.consumableTLChartInstance = new Chart(consumableTLCanvas, {
            type: 'line',
            data: {
                labels: consumableTimelineChartData.labels,
                datasets: [{
                    label: 'Consumables Distributed',
                    data: consumableTimelineChartData.data,
                    fill: false,
                    borderColor: '#12b76a',
                    borderWidth: 2,
                    tension: 0,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#12b76a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#12b76a',
                    pointHoverBorderColor: '#ffffff'
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

        window.updateConsumableTimelineChartJS = (catId) => {
            const map = window.chartData?.consumableTimelineByCategory;
            const newData = (map && map[catId]) || { labels: ['No Data'], data: [0] };
            if (window.consumableTLChartInstance) {
                window.consumableTLChartInstance.data.labels = newData.labels;
                window.consumableTLChartInstance.data.datasets[0].data = newData.data;
                window.consumableTLChartInstance.update();
            }
        };
    }

    // 6. Purchase Timeline Chart (Crisp Sharp Line)
    const purchaseCanvas = mountCanvas("#purchaseTimelineChart");
    if (purchaseCanvas && purchaseChartData && purchaseChartData.data?.length) {
        window.purchaseTimelineChartInstance = new Chart(purchaseCanvas, {
            type: 'line',
            data: {
                labels: purchaseChartData.labels,
                datasets: [{
                    label: 'Assets Purchased',
                    data: purchaseChartData.data,
                    fill: false,
                    borderColor: '#465fff',
                    borderWidth: 2,
                    tension: 0, // Sharp straight lines
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#465fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#465fff',
                    pointHoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
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

        // Adapter method for Alpine category filter
        window.updatePurchaseTimelineChartJS = (catId) => {
            const map = window.chartData?.purchaseDataByCategory;
            const newData = (map && map[catId]) || { labels: ['No Data'], data: [0] };
            if (window.purchaseTimelineChartInstance) {
                window.purchaseTimelineChartInstance.data.labels = newData.labels;
                window.purchaseTimelineChartInstance.data.datasets[0].data = newData.data;
                window.purchaseTimelineChartInstance.update();
            }
        };
    }
});
