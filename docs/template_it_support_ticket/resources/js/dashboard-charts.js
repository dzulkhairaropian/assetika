import ApexCharts from "apexcharts";
import feather from "feather-icons";

const COLORS = [
    "#465FFF",
    "#12B76A",
    "#F79009",
    "#F04438",
    "#7A5AF8",
    "#EE46BC",
    "#0BA5EC",
];
const FONT_FAMILY = "Outfit, sans-serif";

const baseOptions = {
    chart: {
        fontFamily: FONT_FAMILY,
        toolbar: { show: false },
        animations: { enabled: true, easing: "easeinout", speed: 800 },
    },
    xaxis: {
        labels: {
            style: {
                fontSize: "12px",
                colors: "#98A2B3",
                fontFamily: FONT_FAMILY,
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: {
                fontSize: "12px",
                colors: "#98A2B3",
                fontFamily: FONT_FAMILY,
            },
        },
    },
    dataLabels: { enabled: false },
    stroke: { curve: "smooth", width: 2 },
    grid: { borderColor: "#E4E7EC", strokeDashArray: 4 },
    legend: {
        fontSize: "12px",
        fontFamily: FONT_FAMILY,
        fontWeight: 500,
        markers: { size: 8 },
    },
    tooltip: {
        style: { fontSize: "12px", fontFamily: FONT_FAMILY },
        theme: "light",
    },
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.1,
            opacityTo: 1,
            stops: [0, 90, 100],
        },
    },
};

function isDark() {
    return document.documentElement.classList.contains("dark");
}

function applyDarkMode(chart) {
    if (isDark()) {
        const dc = {
            stroke: "#344054",
            grid: "#344054",
            labels: "#667085",
            bg: "#1a2231",
        };
        chart.updateOptions({
            stroke: { ...chart.w.config.stroke, curve: "smooth" },
            grid: { borderColor: dc.grid, strokeDashArray: 4 },
            xaxis: { labels: { style: { colors: dc.labels } } },
            yaxis: { labels: { style: { colors: dc.labels } } },
            legend: { labels: { colors: dc.labels } },
            tooltip: { theme: "dark" },
        });
    }
}

function renderEmpty(el, title, description, icon = "inbox") {
    if (!el || el.hasChildNodes()) return;
    const dc = isDark() ? "dark:bg-gray-800" : "";
    el.innerHTML = `<div class="flex w-full flex-1 flex-col items-center justify-center text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 ${dc}">
            <i data-feather="${icon}" class="size-6 text-gray-500 dark:text-gray-400"></i>
        </div>
        <p class="mt-4 text-theme-sm font-semibold text-gray-800 dark:text-white/90">${title}</p>
        <p class="mt-1 max-w-[280px] text-theme-xs text-gray-500 dark:text-gray-400">${description}</p>
    </div>`;
    feather.replace();
}

function initTrendChart() {
    const el = document.getElementById("chartTrend");
    if (!el) return;
    const months = JSON.parse(el.dataset.months || "[]");
    const counts = JSON.parse(el.dataset.counts || "[]");

    if (counts.length === 0 || counts.every((c) => c === 0)) {
        renderEmpty(
            el,
            "No ticket trend yet",
            "Monthly trend will appear after tickets are created.",
            "trending-up"
        );
        return;
    }

    const chart = new ApexCharts(el, {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: "area", height: 300 },
        series: [{ name: "Tickets", data: counts }],
        xaxis: { ...baseOptions.xaxis, categories: months },
        colors: [COLORS[0]],
        yaxis: { ...baseOptions.yaxis, min: 0, tickAmount: 4 },
    });
    chart.render();
    applyDarkMode(chart);

    new MutationObserver(() => applyDarkMode(chart)).observe(
        document.documentElement,
        { attributes: true, attributeFilter: ["class"] },
    );
}

function initStatusChart() {
    const el = document.getElementById("chartStatus");
    if (!el) return;
    const labels = JSON.parse(el.dataset.labels || "[]");
    const counts = JSON.parse(el.dataset.counts || "[]");
    const colors = JSON.parse(el.dataset.colors || "[]");

    if (labels.length === 0) {
        renderEmpty(
            el,
            "No status data yet",
            "Status breakdown will appear after tickets are created.",
            "pie-chart"
        );
        return;
    }

    const chart = new ApexCharts(el, {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: "donut", height: 250 },
        series: counts,
        labels: labels,
        colors: colors.length > 0 ? colors : COLORS,
        plotOptions: {
            pie: {
                donut: {
                    size: "65%",
                    labels: {
                        show: true,
                        name: { fontSize: "12px", fontFamily: FONT_FAMILY },
                        value: {
                            fontSize: "24px",
                            fontWeight: 700,
                            fontFamily: FONT_FAMILY,
                        },
                    },
                },
            },
        },
        legend: { ...baseOptions.legend, position: "bottom" },
        stroke: { show: false },
        dataLabels: { enabled: false },
    });
    chart.render();
    applyDarkMode(chart);

    new MutationObserver(() => applyDarkMode(chart)).observe(
        document.documentElement,
        { attributes: true, attributeFilter: ["class"] },
    );
}

function initBarChart(elId, color, title, description, icon = "bar-chart-2") {
    const el = document.getElementById(elId);
    if (!el) return;
    const labels = JSON.parse(el.dataset.labels || "[]");
    const counts = JSON.parse(el.dataset.counts || "[]");

    if (labels.length === 0) {
        renderEmpty(el, title, description, icon);
        return;
    }

    const chart = new ApexCharts(el, {
        ...baseOptions,
        chart: { ...baseOptions.chart, type: "bar", height: 280 },
        series: [{ name: "Tickets", data: counts }],
        xaxis: { ...baseOptions.xaxis, categories: labels, type: "category" },
        yaxis: { ...baseOptions.yaxis, min: 0, tickAmount: 4 },
        colors: [color],
        plotOptions: {
            bar: {
                borderRadius: 4,
                borderRadiusApplication: "end",
                horizontal: true,
                barHeight: "70%",
            },
        },
        fill: {
            type: "gradient",
            gradient: {
                shadeIntensity: 0.5,
                opacityFrom: 0.7,
                opacityTo: 1,
                stops: [0, 100],
            },
        },
    });
    chart.render();
    applyDarkMode(chart);

    new MutationObserver(() => applyDarkMode(chart)).observe(
        document.documentElement,
        { attributes: true, attributeFilter: ["class"] },
    );
}

export function initDashboardCharts() {
    initTrendChart();
    initStatusChart();
    initBarChart(
        "chartCategory",
        COLORS[0],
        "No category data yet",
        "Top categories will appear after tickets are created.",
        "tag"
    );
    initBarChart(
        "chartDepartment",
        COLORS[1],
        "No department data yet",
        "Top departments will appear after tickets are created.",
        "git-branch"
    );
    initBarChart(
        "chartRequester",
        COLORS[4],
        "No requester data yet",
        "Top requesters will appear after tickets are created.",
        "users"
    );
}
