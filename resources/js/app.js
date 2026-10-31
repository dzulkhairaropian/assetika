import "./bootstrap";
import Alpine from "alpinejs";
import { Chart, registerables } from "chart.js";
import { createIcons, icons } from "lucide";

// flatpickr
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

// FullCalendar
import { Calendar } from "@fullcalendar/core";

Chart.register(...registerables);

// Center text plugin for doughnut charts
const centerTextPlugin = {
    id: 'centerText',
    beforeDraw(chart) {
        if (chart.config.type !== 'doughnut') return;
        const opts = chart.config.options.plugins?.centerText;
        if (!opts || opts.display === false) return;

        const { ctx, chartArea } = chart;
        if (!chartArea) return;
        const { left, top, width, height } = chartArea;

        // Check if user is hovering over a specific segment
        const activeElements = chart.getActiveElements();
        let displayLabel = opts.label || 'Total';
        let displayValue = opts.value !== undefined
            ? opts.value
            : chart.data.datasets[0]?.data.reduce((a, b) => Number(a) + Number(b), 0) || 0;

        if (activeElements && activeElements.length > 0) {
            const index = activeElements[0].index;
            if (chart.data.labels[index] !== undefined) {
                displayLabel = chart.data.labels[index];
            }
            if (chart.data.datasets[0]?.data[index] !== undefined) {
                displayValue = chart.data.datasets[0].data[index];
            }
        }

        const isDark = document.documentElement.classList.contains('dark');
        const centerX = left + width / 2;
        const centerY = top + height / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Draw top label (subtle uppercase)
        ctx.font = "500 11px 'Plus Jakarta Sans', system-ui, sans-serif";
        ctx.fillStyle = isDark ? '#94a3b8' : '#64748b';
        ctx.fillText(String(displayLabel).toUpperCase(), centerX, centerY - 12);

        // Draw main value (large bold)
        ctx.font = "700 24px 'Plus Jakarta Sans', system-ui, sans-serif";
        ctx.fillStyle = isDark ? '#f8fafc' : '#0f172a';
        ctx.fillText(Number(displayValue).toLocaleString('id-ID'), centerX, centerY + 12);

        ctx.restore();
    }
};

Chart.register(centerTextPlugin);

// Chart.js default font & color settings
Chart.defaults.font.family = "'Plus Jakarta Sans', system-ui, sans-serif";
Chart.defaults.color = "#64748b";

window.Alpine = Alpine;
window.Chart = Chart;
window.createIcons = createIcons;
window.lucideIcons = icons;
window.flatpickr = flatpickr;
window.FullCalendar = Calendar;

Alpine.start();

// Initialize components on DOM ready
document.addEventListener("DOMContentLoaded", () => {
    createIcons({ icons });

    // Map imports
    if (document.querySelector("#mapOne")) {
        import("./components/map").then((module) => module.initMap());
    }

    // Chart imports
    if (document.querySelector("#chartOne")) {
        import("./components/chart/chart-1").then((module) =>
            module.initChartOne(),
        );
    }
    if (document.querySelector("#chartTwo")) {
        import("./components/chart/chart-2").then((module) =>
            module.initChartTwo(),
        );
    }
    if (document.querySelector("#chartThree")) {
        import("./components/chart/chart-3").then((module) =>
            module.initChartThree(),
        );
    }
    if (document.querySelector("#chartSix")) {
        import("./components/chart/chart-6").then((module) =>
            module.initChartSix(),
        );
    }
    if (document.querySelector("#chartEight")) {
        import("./components/chart/chart-8").then((module) =>
            module.initChartEight(),
        );
    }
    if (document.querySelector("#chartThirteen")) {
        import("./components/chart/chart-13").then((module) =>
            module.initChartThirteen(),
        );
    }

    // Calendar init
    if (document.querySelector("#calendar")) {
        import("./components/calendar-init").then((module) =>
            module.calendarInit(),
        );
    }
});
