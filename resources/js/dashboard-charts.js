document.addEventListener('DOMContentLoaded', () => {
    const { totalAssets, chartData, deptChartData, productDataByCategory, consumableChartData, purchaseChartData } = window.chartData || {};

    const createDonutOptions = (series, labels, colors, totalLabel, totalFormatter) => ({
        series,
        chart: { fontFamily: "Outfit, sans-serif", type: 'donut', height: 320 },
        labels,
        colors,
        legend: { position: 'bottom', fontFamily: "Outfit" },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: { show: true },
                        value: { show: true, fontSize: '20px', fontWeight: 'bold' },
                        total: { show: true, label: totalLabel, formatter: totalFormatter }
                    }
                }
            }
        },
        dataLabels: { enabled: false }
    });

    // 1. Asset Status Chart
    if (document.querySelector("#assetStatusChart") && chartData) {
        new ApexCharts(
            document.querySelector("#assetStatusChart"),
            createDonutOptions(chartData.data, chartData.labels, chartData.colors, 'Total Assets', () => totalAssets)
        ).render();
    }

    // 2. Department Chart
    if (document.querySelector("#deptAssetChart") && deptChartData && deptChartData.data.length) {
        new ApexCharts(
            document.querySelector("#deptAssetChart"),
            createDonutOptions(deptChartData.data, deptChartData.labels, ['#465FFF', '#00F5D4', '#FF9F1C', '#FF5400', '#7209B7'], 'Assets', (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0))
        ).render();
    }

    // 3. Product Chart
    if (document.querySelector("#productAssetChart") && productDataByCategory && productDataByCategory.all) {
        window.productChartDataMap = productDataByCategory;
        window.productChartInstance = new ApexCharts(
            document.querySelector("#productAssetChart"),
            createDonutOptions(productDataByCategory.all.data, productDataByCategory.all.labels, ['#00F5D4', '#7209B7', '#465FFF', '#FF5400', '#FF9F1C'], 'Total Used', (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0))
        );
        window.productChartInstance.render();
    }

    // 4. Consumable Chart
    if (document.querySelector("#requestedConsumablesChart") && consumableChartData && consumableChartData.data.length) {
        new ApexCharts(
            document.querySelector("#requestedConsumablesChart"),
            createDonutOptions(consumableChartData.data, consumableChartData.labels, ['#7209B7', '#FF5400', '#FF9F1C', '#465FFF', '#00F5D4'], 'Total Items', (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0))
        ).render();
    }

    // 5. Purchase Timeline Chart (Area)
    if (document.querySelector("#purchaseTimelineChart") && purchaseChartData && purchaseChartData.data.length) {
        const purchaseOptions = {
            series: [{ name: 'Assets Released', data: purchaseChartData.data }],
            chart: { fontFamily: "Outfit, sans-serif", type: 'area', height: 320, toolbar: { show: false } },
            colors: ['#465FFF'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1, stops: [0, 90, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: purchaseChartData.labels, labels: { style: { fontFamily: 'Outfit, sans-serif' } } },
            yaxis: { labels: { style: { fontFamily: 'Outfit, sans-serif' } } },
            dataLabels: { enabled: false }
        };
        new ApexCharts(document.querySelector("#purchaseTimelineChart"), purchaseOptions).render();
    }
});