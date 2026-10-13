/**
 * Charting Utilities for NSTP Portal
 * Modern Chart.js wrappers with center-stat plugins and dynamic legends
 */

export function createPortalPieChart(canvas, options = {}) {
    if (!window.Chart || !canvas) return null;

    if (typeof canvas === 'string') {
        canvas = document.getElementById(canvas);
        if (!canvas) return null;
    }

    // Destroy existing instance on this canvas to prevent duplicates/memory leaks
    if (canvas._chartInstance) {
        canvas._chartInstance.destroy();
        canvas._chartInstance = null;
    }

    const labels = options.labels || [];
    const data = options.data || [];
    const defaultColors = [
        '#4f46e5', // Indigo
        '#10b981', // Emerald
        '#f59e0b', // Amber
        '#f43f5e', // Rose
        '#06b6d4', // Cyan
        '#8b5cf6', // Violet
        '#ec4899', // Pink
        '#64748b'  // Slate
    ];
    const backgroundColors = options.colors || defaultColors.slice(0, labels.length);
    const total = data.reduce((sum, v) => sum + (Number(v) || 0), 0);
    const cutout = options.cutout !== undefined ? options.cutout : '68%';
    const centerTitle = options.centerTitle !== undefined ? options.centerTitle : (total > 0 ? total.toLocaleString() : '0');
    const centerSubtitle = options.centerSubtitle !== undefined ? options.centerSubtitle : 'Total';

    // Center text plugin for doughnut charts
    const centerTextPlugin = {
        id: 'portalCenterText_' + Math.random().toString(36).substring(2, 9),
        beforeDraw(chart) {
            if (chart.config.type !== 'doughnut') return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;

            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // Main bold metric
            const mainFontSize = Math.max(14, Math.min(26, Math.floor(chartArea.height / 7)));
            ctx.font = `800 ${mainFontSize}px "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif`;
            ctx.fillStyle = '#0f172a';
            ctx.fillText(centerTitle, centerX, centerY - (centerSubtitle ? 8 : 0));

            // Subtitle
            if (centerSubtitle) {
                const subFontSize = Math.max(9, Math.min(11, Math.floor(mainFontSize * 0.48)));
                ctx.font = `700 ${subFontSize}px "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif`;
                ctx.fillStyle = '#64748b';
                ctx.fillText(centerSubtitle.toUpperCase(), centerX, centerY + (mainFontSize * 0.58));
            }

            ctx.restore();
        }
    };

    const config = {
        type: options.type || 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverBorderColor: '#ffffff',
                hoverOffset: 6,
                spacing: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: cutout,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 750,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: {
                    display: false // We render a high-fidelity custom HTML legend
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(15, 23, 42, 0.92)',
                    titleColor: '#ffffff',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 4,
                    usePointStyle: true,
                    cornerRadius: 8,
                    callbacks: {
                        label(context) {
                            const val = Number(context.raw) || 0;
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return ` ${context.label}: ${val.toLocaleString()} (${pct}%)`;
                        }
                    }
                }
            }
        },
        plugins: [centerTextPlugin]
    };

    const chartInstance = new Chart(canvas, config);
    canvas._chartInstance = chartInstance;

    // Render HTML legend if container specified
    if (options.legendContainer) {
        renderCustomLegend(options.legendContainer, chartInstance, labels, data, backgroundColors, total);
    }

    return chartInstance;
}

function renderCustomLegend(containerOrId, chart, labels, data, colors, total) {
    const el = typeof containerOrId === 'string' ? document.getElementById(containerOrId) : containerOrId;
    if (!el) return;

    el.innerHTML = '';
    labels.forEach((label, i) => {
        const val = Number(data[i]) || 0;
        const color = colors[i] || '#64748b';
        const pct = total > 0 ? ((val / total) * 100).toFixed(1) : '0.0';

        const item = document.createElement('div');
        item.className = 'group flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition cursor-pointer text-xs';
        item.title = `Click to toggle ${label}`;

        item.innerHTML = `
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-3 h-3 rounded-full shrink-0 shadow-xs" style="background-color: ${color}"></span>
                <span class="font-semibold text-slate-700 truncate group-hover:text-slate-900">${label}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <span class="font-bold text-slate-900">${val.toLocaleString()}</span>
                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-600 group-hover:bg-indigo-50 group-hover:text-indigo-700 transition">
                    ${pct}%
                </span>
            </div>
        `;

        item.addEventListener('click', () => {
            const isVisible = chart.getDataVisibility(i);
            chart.toggleDataVisibility(i);
            chart.update();
            item.style.opacity = isVisible ? '0.35' : '1';
        });

        el.appendChild(item);
    });
}

// Backward-compatible wrappers for preexisting hooks
export function renderEnrollmentChart(ctx, data = {}) {
    return createPortalPieChart(ctx, {
        labels: ['CWTS', 'LTS', 'ROTC'],
        data: [data.cwts || 0, data.lts || 0, data.rotc || 0],
        colors: ['#4f46e5', '#10b981', '#f43f5e'],
        centerSubtitle: 'Enrolled'
    });
}

export function renderPassFailChart(ctx, data = {}) {
    return createPortalPieChart(ctx, {
        labels: ['Passed', 'Failed', 'Pending'],
        data: data.passFail || [0, 0, 0],
        colors: ['#10b981', '#f43f5e', '#f59e0b'],
        centerSubtitle: 'Students'
    });
}

window.createPortalPieChart = createPortalPieChart;
window.renderEnrollmentChart = renderEnrollmentChart;
window.renderPassFailChart = renderPassFailChart;
