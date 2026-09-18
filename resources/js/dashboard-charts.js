document.addEventListener('DOMContentLoaded', () => {
    const { totalAssets, totalComponentUnits, chartData, deptChartData, productDataByCategory, consumableChartData, purchaseChartData, componentChartData } = window.chartData || {};

    // shadcn-inspired color palette
    const chartColors = {
        blue: '#3b82f6',
        green: '#22c55e',
        orange: '#f97316',
        red: '#ef4444',
        purple: '#8b5cf6',
        cyan: '#06b6d4',
        pink: '#ec4899',
    };

    const defaultDonutColors = [
        chartColors.blue,
        chartColors.green,
        chartColors.orange,
        chartColors.red,
        chartColors.purple,
    ];

    // Custom plugin to render center text in Doughnut charts (shadcn style)
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            const centerConfig = chart.config.options.plugins?.centerText;
            if (!centerConfig || centerConfig.value === undefined) return;

            const { left, top, width, height } = chartArea;
            const centerX = left + width / 2;
            const centerY = top + height / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // Value text (bold number)
            ctx.font = 'bold 24px Outfit, sans-serif';
            ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a';
            ctx.fillText(Number(centerConfig.value).toLocaleString(), centerX, centerY - 8);

            // Label text (muted subtext)
            ctx.font = '500 12px Outfit, sans-serif';
            ctx.fillStyle = document.documentElement.classList.contains('dark') ? '#94a3b8' : '#64748b';
            ctx.fillText(centerConfig.label || 'Total', centerX, centerY + 14);

            ctx.restore();
        }
    };

    // Chart.js register center plugin
    if (typeof Chart !== 'undefined') {
        Chart.register(centerTextPlugin);
    }

    const createDoughnutConfig = (labels, data, colors = defaultDonutColors, centerLabel = 'Total', centerValue = undefined) => ({
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
                        padding: 16,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8,
                        boxHeight: 8,
                        font: { family: 'Outfit, sans-serif', size: 12, weight: '500' },
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
                    displayColors: true,
                    boxPadding: 4
                }
            }
        }
    });

    const createLineConfig = (labels, data, label, lineColor = chartColors.green) => ({
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                fill: false,
                borderColor: lineColor,
                borderWidth: 2,
                tension: 0, // Crisp linear line matching shadcn ChartLineLinear
                pointBackgroundColor: '#ffffff',
                pointBorderColor: lineColor,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: lineColor,
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
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
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
                    border: { display: false },
                    ticks: {
                        font: { family: 'Outfit, sans-serif', size: 11 },
                        color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8'
                    }
                },
                y: {
                    border: { display: false },
                    grid: {
                        color: document.documentElement.classList.contains('dark') ? 'rgba(148, 163, 184, 0.1)' : 'rgba(226, 232, 240, 0.6)',
                        dash: [4, 4]
                    },
                    ticks: {
                        font: { family: 'Outfit, sans-serif', size: 11 },
                        color: document.documentElement.classList.contains('dark') ? '#64748b' : '#94a3b8',
                        precision: 0
                    }
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

    // 1. Asset Status Distribution Chart (Donut)
    const statusCanvas = mountCanvas('#assetStatusChart');
    if (statusCanvas && chartData) {
        const statusColors = chartData.colors || [chartColors.blue, chartColors.green, chartColors.orange, chartColors.red];
        window.statusChartInstance = new Chart(statusCanvas, createDoughnutConfig(chartData.labels, chartData.data, statusColors, 'Total Assets', totalAssets));

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

    // 2. Department Chart (Donut)
    const deptCanvas = mountCanvas('#deptAssetChart');
    if (deptCanvas && deptChartData && deptChartData.data?.length) {
        const deptColors = [chartColors.blue, chartColors.green, chartColors.orange, chartColors.purple, chartColors.cyan];
        window.deptChartInstance = new Chart(deptCanvas, createDoughnutConfig(deptChartData.labels, deptChartData.data, deptColors, 'Total Assets'));

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

    // 3. Product Chart (Donut)
    const productCanvas = mountCanvas('#productAssetChart');
    if (productCanvas && productDataByCategory && productDataByCategory.all) {
        window.productChartDataMap = productDataByCategory;
        const initialData = productDataByCategory.all;
        const productColors = [chartColors.blue, chartColors.cyan, chartColors.purple, chartColors.orange, chartColors.green];
        window.productChartInstance = new Chart(productCanvas, createDoughnutConfig(initialData.labels, initialData.data, productColors, 'Total Units'));

        window.updateProductChartJS = (catId) => {
            const newData = (window.productChartDataMap && window.productChartDataMap[catId]) || { labels: ['No Data'], data: [0] };
            if (window.productChartInstance) {
                window.productChartInstance.data.labels = newData.labels;
                window.productChartInstance.data.datasets[0].data = newData.data;
                const total = newData.data.reduce((a, b) => Number(a) + Number(b), 0);
                window.productChartInstance.options.plugins.centerText.value = total;
                window.productChartInstance.update();
            }
        };
    }

    // 4. Consumables Chart (Donut)
    const consumableCanvas = mountCanvas('#requestedConsumablesChart');
    if (consumableCanvas && consumableChartData && consumableChartData.data?.length) {
        const consumableColors = [chartColors.blue, chartColors.orange, chartColors.green, chartColors.pink, chartColors.purple];
        window.consumableChartInstance = new Chart(consumableCanvas, createDoughnutConfig(consumableChartData.labels, consumableChartData.data, consumableColors, 'Requests'));

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

    // 5. Consumable Usage Timeline Chart (Linear Line)
    const consumableTLCanvas = mountCanvas('#consumableTimelineChart');
    const { consumableTimelineChartData } = window.chartData || {};
    if (consumableTLCanvas && consumableTimelineChartData && consumableTimelineChartData.data?.length) {
        window.consumableTLChartInstance = new Chart(
            consumableTLCanvas,
            createLineConfig(consumableTimelineChartData.labels, consumableTimelineChartData.data, 'Consumables Distributed', chartColors.green)
        );

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

    // 6. Purchase Timeline Chart (Linear Line)
    const purchaseCanvas = mountCanvas('#purchaseTimelineChart');
    if (purchaseCanvas && purchaseChartData && purchaseChartData.data?.length) {
        window.purchaseTimelineChartInstance = new Chart(
            purchaseCanvas,
            createLineConfig(purchaseChartData.labels, purchaseChartData.data, 'Assets Purchased', chartColors.blue)
        );

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

    // 7. Hardware Lifecycle & Age Distribution Bar Chart
    const ageCanvas = mountCanvas('#assetAgeChart');
    const { assetAgeDistribution } = window.chartData || {};
    if (ageCanvas && assetAgeDistribution && assetAgeDistribution.data?.length) {
        new Chart(ageCanvas, {
            type: 'bar',
            data: {
                labels: assetAgeDistribution.labels,
                datasets: [{
                    label: 'Asset Count',
                    data: assetAgeDistribution.data,
                    backgroundColor: ['#12b76a', '#3b82f6', '#06b6d4', '#f79009', '#f04438'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => ' ' + ctx.raw + ' units'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                    }
                }
            }
        });
    }

    // 8. Top Hardware Vendors Bar Chart
    const vendorCanvas = mountCanvas('#vendorAssetChart');
    const { vendorChartData } = window.chartData || {};
    if (vendorCanvas && vendorChartData && vendorChartData.data?.length) {
        new Chart(vendorCanvas, {
            type: 'bar',
            data: {
                labels: vendorChartData.labels,
                datasets: [{
                    label: 'Units Registered',
                    data: vendorChartData.data,
                    backgroundColor: '#465fff',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: '600' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 12 },
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => ' ' + ctx.raw + ' units'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: 'Outfit, sans-serif', size: 11 } } },
                    y: {
                        border: { display: false },
                        grid: { color: 'rgba(226, 232, 240, 0.6)', dash: [4, 4] },
                        ticks: { font: { family: 'Outfit, sans-serif', size: 11 }, precision: 0 }
                    }
                }
            }
        });
    }

    // 9. Component Status Doughnut Chart with Center Number
    const componentCanvas = mountCanvas('#componentPieChart');
    if (componentCanvas && componentChartData && componentChartData.data?.length) {
        const compTotal = totalComponentUnits !== undefined ? totalComponentUnits : componentChartData.data.reduce((a, b) => Number(a) + Number(b), 0);
        const compColors = componentChartData.colors || ['#12b76a', '#465fff', '#f79009', '#ef4444'];
        new Chart(componentCanvas, createDoughnutConfig(componentChartData.labels, componentChartData.data, compColors, 'Total Units', compTotal));
    }
});

