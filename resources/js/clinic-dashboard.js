/**
 * Clinic dashboard — Chart.js initialization (SPA-safe).
 */
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    BarController,
    BarElement,
    ArcElement,
    DoughnutController,
    Filler,
    Legend,
    Tooltip,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    BarController,
    BarElement,
    ArcElement,
    DoughnutController,
    Filler,
    Legend,
    Tooltip
);

/** @type {Map<string, Chart>} */
const chartInstances = new Map();

function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

function chartColors() {
    const dark = isDarkMode();
    return {
        grid: dark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(15, 76, 129, 0.08)',
        text: dark ? '#9CA3AF' : '#64748b',
        brand: dark ? '#60A5FA' : '#0F4C81',
        emerald: dark ? '#34D399' : '#059669',
        amber: dark ? '#FBBF24' : '#D97706',
        violet: dark ? '#A78BFA' : '#7C3AED',
        cyan: dark ? '#22D3EE' : '#0891B2',
    };
}

/**
 * @param {HTMLCanvasElement} canvas
 * @param {object} config
 */
function renderChart(canvas, config) {
    const existing = chartInstances.get(canvas.id);
    if (existing) {
        existing.destroy();
        chartInstances.delete(canvas.id);
    }

    const colors = chartColors();
    const merged = {
        ...config,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: colors.text, boxWidth: 10, padding: 16 },
                },
                tooltip: {
                    rtl: document.documentElement.dir === 'rtl',
                },
            },
            scales: config.type === 'doughnut' || config.type === 'pie' ? undefined : {
                x: {
                    grid: { display: false },
                    ticks: { color: colors.text, maxRotation: 0 },
                },
                y: {
                    grid: { color: colors.grid },
                    ticks: { color: colors.text },
                    beginAtZero: true,
                },
            },
            ...(config.options || {}),
        },
    };

    if (merged.data?.datasets) {
        merged.data.datasets = merged.data.datasets.map((ds, i) => ({
            borderWidth: 2,
            tension: 0.35,
            fill: ds.fill ?? false,
            pointRadius: 3,
            pointHoverRadius: 5,
            ...ds,
            borderColor: ds.borderColor ?? [colors.brand, colors.emerald, colors.amber, colors.violet, colors.cyan][i % 5],
            backgroundColor: ds.backgroundColor ?? (ds.fill ? `${ds.borderColor ?? colors.brand}22` : 'transparent'),
        }));
    }

    const chart = new Chart(canvas, merged);
    chartInstances.set(canvas.id, chart);
}

export function initClinicDashboard(root = document) {
    const scope = root.querySelector?.('[data-clinic-dashboard]')
        ? root.querySelector('[data-clinic-dashboard]')
        : root.closest?.('[data-clinic-dashboard]') ?? root;

    if (!scope || !scope.querySelector) {
        return;
    }

    scope.querySelectorAll('[data-clinic-chart]').forEach((canvas) => {
        if (!(canvas instanceof HTMLCanvasElement)) {
            return;
        }
        const raw = canvas.getAttribute('data-chart-config');
        if (!raw) {
            return;
        }
        try {
            const config = JSON.parse(raw);
            config.type = config.type || canvas.getAttribute('data-clinic-chart') || 'line';
            renderChart(canvas, config);
        } catch (e) {
            console.warn('clinic-dashboard: invalid chart config', e);
        }
    });
}

export function destroyClinicDashboardCharts() {
    chartInstances.forEach((chart) => chart.destroy());
    chartInstances.clear();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initClinicDashboard());
} else {
    initClinicDashboard();
}

document.addEventListener('clinic:theme-changed', () => {
    destroyClinicDashboardCharts();
    initClinicDashboard();
});
