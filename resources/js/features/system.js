import { createPortalPieChart } from '../utils/charts';

export function attachSystemEvents() {
    // Data Reset Methods
    window.triggerPortalDataReset = function() {
        if (!confirm("WARNING: This will reset all frontend data (approvals, tasks, and state) to their defaults. Are you sure?")) {
            return;
        }
        window.nstpResetFrontendData();
    };

    window.nstpResetFrontendData = function() {
        if (typeof S !== 'undefined') {
            localStorage.removeItem('NSTP_GLOBAL_STATE');
        }
        window.location.reload();
    };

    const dataResetBtn = document.getElementById('dataResetBtn');
    if (dataResetBtn) {
        dataResetBtn.addEventListener('click', () => {
            window.triggerPortalDataReset();
        });
    }

    // Initialize all active dashboard charts
    initDashboardCharts();
}

/**
 * Initializes and wires up interactive Pie / Doughnut charts across all dashboards
 */
export function initDashboardCharts() {
    initCoordinatorCharts();
    initInstructorCharts();
    initRotcCharts();
}

/**
 * Coordinator Dashboard Charts
 */
function initCoordinatorCharts() {
    const enrollmentCanvas = document.getElementById('coordinatorEnrollmentChart');
    const passFailCanvas = document.getElementById('coordinatorPassFailChart');

    if (!enrollmentCanvas && !passFailCanvas) return;

    const chartData = window.__COORDINATOR_CHART_DATA || null;

    if (enrollmentCanvas && chartData) {
        let currentEnrollmentView = 'components'; // 'components' or 'courses'

        function renderEnrollment() {
            if (currentEnrollmentView === 'components') {
                const comp = chartData.components || { labels: ['CWTS', 'LTS', 'ROTC'], data: [0, 0, 0], colors: ['#4f46e5', '#10b981', '#f43f5e'] };
                createPortalPieChart(enrollmentCanvas, {
                    labels: comp.labels,
                    data: comp.data,
                    colors: comp.colors,
                    legendContainer: 'coordinatorEnrollmentLegend',
                    centerTitle: (comp.total || comp.data.reduce((a, b) => a + b, 0)).toLocaleString(),
                    centerSubtitle: 'Students'
                });
            } else {
                const courses = chartData.courses || { labels: [], data: [], colors: [] };
                const totalCourse = courses.data.reduce((a, b) => a + b, 0);
                createPortalPieChart(enrollmentCanvas, {
                    labels: courses.labels,
                    data: courses.data,
                    colors: courses.colors,
                    legendContainer: 'coordinatorEnrollmentLegend',
                    centerTitle: totalCourse.toLocaleString(),
                    centerSubtitle: 'Top Courses'
                });
            }
        }

        renderEnrollment();

        // Wire up view toggles
        const compToggle = document.getElementById('enrollmentToggleComponents');
        const progToggle = document.getElementById('enrollmentTogglePrograms');

        if (compToggle && progToggle) {
            compToggle.addEventListener('click', () => {
                currentEnrollmentView = 'components';
                compToggle.className = 'px-2.5 py-1 rounded-md bg-white text-slate-800 shadow-xs font-semibold cursor-pointer transition';
                progToggle.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-slate-800 font-medium cursor-pointer transition';
                renderEnrollment();
            });

            progToggle.addEventListener('click', () => {
                currentEnrollmentView = 'courses';
                progToggle.className = 'px-2.5 py-1 rounded-md bg-white text-slate-800 shadow-xs font-semibold cursor-pointer transition';
                compToggle.className = 'px-2.5 py-1 rounded-md text-slate-500 hover:text-slate-800 font-medium cursor-pointer transition';
                renderEnrollment();
            });
        }
    }

    if (passFailCanvas && chartData && chartData.grades) {
        const grades = chartData.grades;
        createPortalPieChart(passFailCanvas, {
            labels: grades.labels,
            data: grades.data,
            colors: grades.colors,
            legendContainer: 'coordinatorPassFailLegend',
            centerTitle: grades.pass_rate || (grades.data[0] > 0 ? ((grades.data[0] / (grades.total || 1)) * 100).toFixed(1) + '%' : '0%'),
            centerSubtitle: 'Pass Rate'
        });
    }
}

/**
 * Instructor Dashboard Charts
 */
function initInstructorCharts() {
    const gradeCanvas = document.getElementById('instructorGradeChart');
    const sectionCanvas = document.getElementById('instructorSectionChart');

    if (!gradeCanvas && !sectionCanvas) return;

    const chartData = window.__INSTRUCTOR_CHART_DATA || null;

    if (gradeCanvas && chartData && chartData.grades) {
        const grades = chartData.grades;
        createPortalPieChart(gradeCanvas, {
            labels: grades.labels,
            data: grades.data,
            colors: grades.colors,
            legendContainer: 'instructorGradeLegend',
            centerTitle: (grades.total || grades.data.reduce((a, b) => a + b, 0)).toLocaleString(),
            centerSubtitle: 'Students'
        });
    }

    if (sectionCanvas && chartData && chartData.sections) {
        const sections = chartData.sections;
        createPortalPieChart(sectionCanvas, {
            labels: sections.labels,
            data: sections.data,
            colors: sections.colors,
            legendContainer: 'instructorSectionLegend',
            centerTitle: (sections.total || sections.data.reduce((a, b) => a + b, 0)).toLocaleString(),
            centerSubtitle: 'Enrolled'
        });
    }
}

/**
 * ROTC Command Dashboard Charts
 */
function initRotcCharts() {
    const cadetCanvas = document.getElementById('rotcCadetChart');
    const platoonCanvas = document.getElementById('rotcPlatoonChart');

    if (!cadetCanvas && !platoonCanvas) return;

    const chartData = window.__ROTC_CHART_DATA || null;

    if (cadetCanvas && chartData && chartData.cadet_grades) {
        const grades = chartData.cadet_grades;
        createPortalPieChart(cadetCanvas, {
            labels: grades.labels,
            data: grades.data,
            colors: grades.colors,
            legendContainer: 'rotcCadetLegend',
            centerTitle: (grades.total || grades.data.reduce((a, b) => a + b, 0)).toLocaleString(),
            centerSubtitle: 'Cadets'
        });
    }

    if (platoonCanvas && chartData && chartData.platoons) {
        const platoons = chartData.platoons;
        createPortalPieChart(platoonCanvas, {
            labels: platoons.labels,
            data: platoons.data,
            colors: platoons.colors,
            legendContainer: 'rotcPlatoonLegend',
            centerTitle: (platoons.total || platoons.data.reduce((a, b) => a + b, 0)).toLocaleString(),
            centerSubtitle: 'Total'
        });
    }
}

window.initDashboardCharts = initDashboardCharts;
