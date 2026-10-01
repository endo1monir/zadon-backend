import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;

Alpine.start();

window.renderChart = function (selector, options) {
    const element = document.querySelector(selector);

    if (!element) {
        return null;
    }

    return new ApexCharts(element, options).render();
};
