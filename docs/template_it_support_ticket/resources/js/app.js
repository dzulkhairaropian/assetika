import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import feather from 'feather-icons';
import { initDashboardCharts } from './dashboard-charts';

Alpine.plugin(persist);
window.Alpine = Alpine;
window.feather = feather;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    feather.replace();
    initDashboardCharts();
});


