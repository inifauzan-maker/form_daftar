document.addEventListener('DOMContentLoaded', () => {
    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const numberFormatter = new Intl.NumberFormat('id-ID', {
        maximumFractionDigits: 0,
    });
    const currencyFormatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });
    const compactCurrencyFormatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        notation: 'compact',
        maximumFractionDigits: 1,
    });

    const refreshButton = document.getElementById('refresh-button');
    const analyticsFilters = {
        year: document.getElementById('filter-year'),
        month: document.getElementById('filter-month'),
        program: document.getElementById('filter-program'),
        branch: document.getElementById('filter-branch'),
    };
    const summaryElements = {
        studentsTotal: document.getElementById('summary-students-total'),
        studentsPaid: document.getElementById('summary-students-paid'),
        studentsTarget: document.getElementById('summary-students-target'),
        studentsProgressBar: document.getElementById('summary-students-progress'),
        studentsProgressLabel: document.getElementById('summary-students-progress-label'),
        revenueActual: document.getElementById('summary-revenue-actual'),
        revenueExpected: document.getElementById('summary-revenue-expected'),
        revenueAverage: document.getElementById('summary-revenue-average'),
        revenueTarget: document.getElementById('summary-revenue-target'),
        revenueProgressBar: document.getElementById('summary-revenue-progress'),
        revenueProgressLabel: document.getElementById('summary-revenue-progress-label'),
        discountTotal: document.getElementById('summary-discount-total'),
        revenueDifference: document.getElementById('summary-revenue-difference'),
    };
    const chartCaptionEl = document.getElementById('chart-monthly-caption');
    const forecastElements = {
        year: document.getElementById('forecast-year'),
        studentsCurrent: document.getElementById('forecast-students-current'),
        studentsProjected: document.getElementById('forecast-students-projected'),
        studentsGrowth: document.getElementById('forecast-students-growth'),
        revenueCurrent: document.getElementById('forecast-revenue-current'),
        revenueProjected: document.getElementById('forecast-revenue-projected'),
        revenueGrowth: document.getElementById('forecast-revenue-growth'),
    };

    if (!summaryElements.studentsTotal || !forecastElements.year) {
        return;
    }

    const analyticsState = {
        charts: {},
        options: {
            years: [],
            months: [],
            programs: [],
            branches: [],
        },
        loading: false,
    };

    bindEvents();
    initCharts();
    loadMetrics(true, 'initial').catch(() => {
        showToast('Gagal memuat data dashboard.', 'error');
    });

    function bindEvents() {
        if (refreshButton) {
            refreshButton.addEventListener('click', () => {
                loadMetrics(false, 'refresh').catch(() => {
                    showToast('Gagal memuat ulang data dashboard.', 'error');
                });
            });
        }

        Object.values(analyticsFilters).forEach((select) => {
            if (select) {
                select.addEventListener('change', () => {
                    loadMetrics(false, 'filter').catch(() => {
                        showToast('Gagal memperbarui data dashboard.', 'error');
                    });
                });
            }
        });
    }

    function initCharts() {
        const monthlyCtx = document.getElementById('chart-monthly');
        const yearlyCtx = document.getElementById('chart-yearly');
        const branchCtx = document.getElementById('chart-branch');
        const programCtx = document.getElementById('chart-program');

        lockCanvasSize(monthlyCtx, 340);
        if (monthlyCtx && window.Chart) {
            analyticsState.charts.monthly = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Siswa kumulatif',
                            data: [],
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            yAxisID: 'students',
                        },
                        {
                            label: 'Omzet kumulatif',
                            data: [],
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.12)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            yAxisID: 'revenue',
                        },
                    ],
                },
                options: {
                    ...chartOptionsDualAxis(),
                    animation: { duration: 0 },
                },
            });
        }

        lockCanvasSize(yearlyCtx, 300);
        if (yearlyCtx && window.Chart) {
            analyticsState.charts.yearly = new Chart(yearlyCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Siswa',
                            data: [],
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderRadius: 6,
                            yAxisID: 'students',
                        },
                        {
                            label: 'Omzet',
                            data: [],
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderRadius: 6,
                            yAxisID: 'revenue',
                        },
                    ],
                },
                options: {
                    ...chartOptionsBar(),
                    animation: { duration: 0 },
                },
            });
        }

        lockCanvasSize(branchCtx, 300);
        if (branchCtx && window.Chart) {
            analyticsState.charts.branch = new Chart(branchCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Siswa',
                            data: [],
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderRadius: 6,
                            xAxisID: 'students',
                        },
                        {
                            label: 'Omzet',
                            data: [],
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderRadius: 6,
                            xAxisID: 'revenue',
                        },
                    ],
                },
                options: {
                    ...chartOptionsHorizontal(),
                    animation: { duration: 0 },
                },
            });
        }

        lockCanvasSize(programCtx, 300);
        if (programCtx && window.Chart) {
            analyticsState.charts.program = new Chart(programCtx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Siswa',
                            data: [],
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderRadius: 6,
                            xAxisID: 'students',
                        },
                        {
                            label: 'Omzet',
                            data: [],
                            backgroundColor: 'rgba(22, 163, 74, 0.7)',
                            borderRadius: 6,
                            xAxisID: 'revenue',
                        },
                    ],
                },
                options: {
                    ...chartOptionsHorizontal(),
                    animation: { duration: 0 },
                },
            });
        }
    }

function chartOptionsDualAxis() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                students: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatNumber(value),
                        color: '#0f172a',
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)',
                    },
                },
                revenue: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatCompactCurrency(value),
                        color: '#0f172a',
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
                x: {
                    ticks: {
                        color: '#0f172a',
                    },
                },
            },
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const axis = context.dataset.yAxisID;
                            const value = context.parsed.y;
                            if (axis === 'revenue') {
                                return `${context.dataset.label}: ${formatCurrency(value)}`;
                            }

                            return `${context.dataset.label}: ${formatNumber(value)}`;
                        },
                    },
                },
            },
        };
    }

    function chartOptionsBar() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                students: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatNumber(value),
                        color: '#0f172a',
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)',
                    },
                },
                revenue: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatCompactCurrency(value),
                        color: '#0f172a',
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
                x: {
                    ticks: {
                        color: '#0f172a',
                    },
                },
            },
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const axis = context.dataset.yAxisID;
                            const value = context.parsed.y;
                            if (axis === 'revenue') {
                                return `${context.dataset.label}: ${formatCurrency(value)}`;
                            }

                            return `${context.dataset.label}: ${formatNumber(value)}`;
                        },
                    },
                },
            },
        };
    }

    function chartOptionsHorizontal() {
        return {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                students: {
                    position: 'top',
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatNumber(value),
                        color: '#0f172a',
                    },
                    grid: {
                        color: 'rgba(148, 163, 184, 0.2)',
                    },
                },
                revenue: {
                    position: 'bottom',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: (value) => formatCompactCurrency(value),
                        color: '#0f172a',
                    },
                },
                y: {
                    ticks: {
                        color: '#0f172a',
                    },
                },
            },
            plugins: {
                legend: {
                    labels: {
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const axis = context.dataset.xAxisID;
                            const value = context.parsed.x;
                            if (axis === 'revenue') {
                                return `${context.dataset.label}: ${formatCurrency(value)}`;
                            }

                            return `${context.dataset.label}: ${formatNumber(value)}`;
                        },
                    },
                },
            },
        };
    }

    function loadMetrics(initial = false, trigger = 'auto') {
        if (analyticsState.loading) {
            return Promise.resolve();
        }

        analyticsState.loading = true;
        setFiltersDisabled(true);

        return fetch(withBase(`/api/dashboard/metrics${buildMetricsQuery()}`))
            .then(async (response) => {
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error('Failed to load metrics');
                }

                updateFilterOptions(payload.filters);
                updateSummary(payload.summary, payload.targets);
                updateCharts(payload);
                updateForecast(payload.forecast);

                if (!initial && trigger === 'refresh') {
                    showToast('Data dashboard diperbarui.');
                }
            })
            .catch((error) => {
                console.error('Failed to load dashboard metrics', error);
                showToast('Gagal memuat data dashboard.', 'error');
                throw error;
            })
            .finally(() => {
                analyticsState.loading = false;
                setFiltersDisabled(false);
            });
    }

    function setFiltersDisabled(isDisabled) {
        Object.values(analyticsFilters).forEach((select) => {
            if (select) {
                select.disabled = isDisabled;
            }
        });
    }

    function buildMetricsQuery() {
        const params = new URLSearchParams();

        const yearValue = analyticsFilters.year?.value;
        const monthValue = analyticsFilters.month?.value;
        const programValue = analyticsFilters.program?.value;
        const branchValue = analyticsFilters.branch?.value;

        if (yearValue) {
            params.append('year', yearValue);
        }
        if (monthValue) {
            params.append('month', monthValue);
        }
        if (programValue) {
            params.append('program_id', programValue);
        }
        if (branchValue) {
            params.append('branch', branchValue);
        }

        const query = params.toString();
        return query ? `?${query}` : '';
    }

    function updateFilterOptions(filterPayload) {
        if (!filterPayload) {
            return;
        }

        const { options = {}, selected = {} } = filterPayload;
        analyticsState.options.years = Array.isArray(options.years) ? options.years : [];
        analyticsState.options.months = Array.isArray(options.months) ? options.months : [];
        analyticsState.options.programs = Array.isArray(options.programs) ? options.programs : [];
        analyticsState.options.branches = Array.isArray(options.branches) ? options.branches : [];

        populateSelect(
            analyticsFilters.year,
            analyticsState.options.years.map((year) => ({ value: year, label: String(year) })),
            'Semua Tahun'
        );
        populateSelect(analyticsFilters.month, analyticsState.options.months, 'Semua Bulan');
        populateSelect(analyticsFilters.program, analyticsState.options.programs, 'Semua Program');
        populateSelect(analyticsFilters.branch, analyticsState.options.branches, 'Semua Cabang');

        setSelectValue(analyticsFilters.year, selected.year);
        setSelectValue(analyticsFilters.month, selected.month);
        setSelectValue(analyticsFilters.program, selected.program_id);
        setSelectValue(analyticsFilters.branch, selected.branch);
    }

    function populateSelect(select, options, placeholderText) {
        if (!select) {
            return;
        }

        const currentValue = select.value;
        select.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = placeholderText;
        select.appendChild(placeholder);

        options.forEach((option) => {
            const opt = document.createElement('option');
            const value = option.value ?? option;
            const label = option.label ?? option;
            opt.value = String(value);
            opt.textContent = String(label);
            select.appendChild(opt);
        });

        if (options.some((option) => (option.value ?? option) === currentValue)) {
            select.value = currentValue;
        }
    }

    function setSelectValue(select, value) {
        if (!select) {
            return;
        }

        select.value = value ?? '';
    }

    function updateSummary(summary, targets = {}) {
        if (!summary) {
            return;
        }

        const studentsSummary = summary.students || {};
        const revenueSummary = summary.revenue || {};

        const totalStudents = Number(studentsSummary.total ?? 0);
        const paidStudents = Number(studentsSummary.paid ?? 0);
        const studentTarget = Number(studentsSummary.target ?? targets.students ?? 0);
        const studentProgress = Number.isFinite(studentsSummary.progress) ? studentsSummary.progress : null;

        const revenueActual = Number(revenueSummary.actual ?? 0);
        const revenueExpected = Number(revenueSummary.expected ?? 0);
        const revenueAverage = Number(revenueSummary.average_per_student ?? 0);
        const revenueTarget = Number(revenueSummary.target ?? targets.revenue ?? 0);
        const revenueProgress = Number.isFinite(revenueSummary.progress) ? revenueSummary.progress : null;
        const discountTotal = Number(revenueSummary.discount ?? 0);

        if (summaryElements.studentsTotal) {
            summaryElements.studentsTotal.textContent = formatNumber(totalStudents);
        }
        if (summaryElements.studentsPaid) {
            summaryElements.studentsPaid.textContent = `${formatNumber(paidStudents)} siswa telah membayar`;
        }
        if (summaryElements.studentsTarget) {
            summaryElements.studentsTarget.textContent = studentTarget > 0
                ? `Target: ${formatNumber(studentTarget)} siswa`
                : 'Target belum ditetapkan';
        }
        if (summaryElements.studentsProgressLabel) {
            summaryElements.studentsProgressLabel.textContent = studentProgress !== null
                ? `${formatPercentage(studentProgress)} dari target`
                : 'Target belum ditetapkan';
        }
        if (summaryElements.studentsProgressBar) {
            const width = studentProgress !== null ? Math.min(studentProgress * 100, 120) : 0;
            summaryElements.studentsProgressBar.style.width = `${width}%`;
            summaryElements.studentsProgressBar.parentElement?.classList.toggle('is-disabled', studentProgress === null);
        }

        if (summaryElements.revenueActual) {
            summaryElements.revenueActual.textContent = formatCurrency(revenueActual);
        }
        if (summaryElements.revenueExpected) {
            summaryElements.revenueExpected.textContent = `Tagihan: ${formatCurrency(revenueExpected)}`;
        }
        if (summaryElements.revenueAverage) {
            summaryElements.revenueAverage.textContent = totalStudents > 0
                ? `Rata-rata: ${formatCurrency(revenueAverage)} / siswa`
                : 'Rata-rata: -';
        }
        if (summaryElements.revenueTarget) {
            summaryElements.revenueTarget.textContent = revenueTarget > 0
                ? `Target: ${formatCurrency(revenueTarget)}`
                : 'Target belum ditetapkan';
        }
        if (summaryElements.revenueProgressLabel) {
            summaryElements.revenueProgressLabel.textContent = revenueProgress !== null
                ? `${formatPercentage(revenueProgress)} dari target`
                : 'Target belum ditetapkan';
        }
        if (summaryElements.revenueProgressBar) {
            const width = revenueProgress !== null ? Math.min(revenueProgress * 100, 120) : 0;
            summaryElements.revenueProgressBar.style.width = `${width}%`;
            summaryElements.revenueProgressBar.parentElement?.classList.toggle('is-disabled', revenueProgress === null);
        }

        if (summaryElements.discountTotal) {
            summaryElements.discountTotal.textContent = formatCurrency(discountTotal);
        }

        if (summaryElements.revenueDifference) {
            const diff = revenueTarget - revenueActual;
            if (revenueTarget === 0) {
                summaryElements.revenueDifference.textContent = 'Target omzet belum ditetapkan';
            } else if (diff > 0) {
                summaryElements.revenueDifference.textContent = `Kekurangan terhadap target: ${formatCurrency(diff)}`;
            } else if (diff < 0) {
                summaryElements.revenueDifference.textContent = `Melebihi target: ${formatCurrency(Math.abs(diff))}`;
            } else {
                summaryElements.revenueDifference.textContent = 'Target omzet telah tercapai';
            }
        }
    }

    function updateCharts(payload) {
        const monthlyData = Array.isArray(payload.monthly) ? payload.monthly : [];
        const yearlyData = Array.isArray(payload.yearly) ? payload.yearly : [];
        const branchData = Array.isArray(payload.by_branch) ? payload.by_branch : [];
        const programData = Array.isArray(payload.by_program) ? payload.by_program : [];
        const selectedFilters = payload.filters?.selected ?? {};

        updateMonthlyChart(monthlyData, selectedFilters);
        updateYearlyChart(yearlyData);
        updateBranchChart(branchData);
        updateProgramChart(programData);
        updateMonthlyCaption(selectedFilters);
    }

    function updateMonthlyChart(rows, selectedFilters) {
        const chart = analyticsState.charts.monthly;
        if (!chart) {
            return;
        }

        const selectedYear = selectedFilters.year ? Number(selectedFilters.year) : null;
        const filteredRows = rows.filter((row) => {
            if (!selectedYear) {
                return true;
            }
            return Number(row.year) === selectedYear;
        });

        const labels = filteredRows.map((row) => monthLabel(Number(row.month)));
        const studentsData = filteredRows.map((row) =>
            Number(row.students_cumulative ?? row.students ?? 0)
        );
        const revenueData = filteredRows.map((row) =>
            Number(row.revenue_cumulative ?? row.revenue ?? 0)
        );

        chart.data.labels = labels;
        chart.data.datasets[0].data = studentsData;
        chart.data.datasets[1].data = revenueData;
        chart.update();
    }

    function updateYearlyChart(rows) {
        const chart = analyticsState.charts.yearly;
        if (!chart) {
            return;
        }

        const labels = rows.map((row) => row.year ?? '-');
        const studentsData = rows.map((row) => Number(row.students ?? 0));
        const revenueData = rows.map((row) => Number(row.revenue ?? 0));

        chart.data.labels = labels;
        chart.data.datasets[0].data = studentsData;
        chart.data.datasets[1].data = revenueData;
        chart.update();
    }

    function updateBranchChart(rows) {
        const chart = analyticsState.charts.branch;
        if (!chart) {
            return;
        }

        chart.data.labels = rows.map((row) => row.label ?? row.branch ?? row.province ?? '-');
        chart.data.datasets[0].data = rows.map((row) => Number(row.students ?? 0));
        chart.data.datasets[1].data = rows.map((row) => Number(row.revenue ?? 0));
        chart.update();
    }

    function updateProgramChart(rows) {
        const chart = analyticsState.charts.program;
        if (!chart) {
            return;
        }

        const sorted = [...rows].sort((a, b) => {
            const revenueDiff = Number(b.revenue ?? 0) - Number(a.revenue ?? 0);
            if (revenueDiff !== 0) {
                return revenueDiff;
            }
            return Number(b.students ?? 0) - Number(a.students ?? 0);
        });

        const limited = sorted.slice(0, 8);
        chart.data.labels = limited.map((row) => row.label ?? row.program_name ?? '-');
        chart.data.datasets[0].data = limited.map((row) => Number(row.students ?? 0));
        chart.data.datasets[1].data = limited.map((row) => Number(row.revenue ?? 0));
        chart.update();
    }

    function updateMonthlyCaption(selectedFilters) {
        if (!chartCaptionEl) {
            return;
        }

        const parts = [];

        parts.push(selectedFilters.year ? `Tahun ${selectedFilters.year}` : 'Semua tahun');

        if (selectedFilters.month) {
            parts.push(`s.d. ${monthLabel(Number(selectedFilters.month))}`);
        }

        parts.push(selectedFilters.branch ? `Cabang ${selectedFilters.branch}` : 'Semua cabang');
        parts.push(selectedFilters.program_id ? `Program ${selectedFilters.program_id}` : 'Semua program');

        chartCaptionEl.textContent = parts.join(' • ');
    }

    function updateForecast(forecast) {
        if (!forecast) {
            return;
        }

        const students = forecast.students || {};
        const revenue = forecast.revenue || {};

        if (forecastElements.year) {
            forecastElements.year.textContent = `Tahun ${forecast.year ?? '-'}`;
        }
        if (forecastElements.studentsCurrent) {
            forecastElements.studentsCurrent.textContent = formatNumber(students.current ?? 0);
        }
        if (forecastElements.studentsProjected) {
            forecastElements.studentsProjected.textContent = formatNumber(students.projected ?? 0);
        }
        if (forecastElements.revenueCurrent) {
            forecastElements.revenueCurrent.textContent = formatCurrency(revenue.current ?? 0);
        }
        if (forecastElements.revenueProjected) {
            forecastElements.revenueProjected.textContent = formatCurrency(revenue.projected ?? 0);
        }

        updateGrowthBadge(forecastElements.studentsGrowth, students.growth_rate ?? 0);
        updateGrowthBadge(forecastElements.revenueGrowth, revenue.growth_rate ?? 0);
    }

    function updateGrowthBadge(element, growth) {
        if (!element) {
            return;
        }

        const value = Number.isFinite(growth) ? growth : 0;
        element.textContent = formatPercentage(value);
        element.classList.remove('is-positive', 'is-negative', 'is-neutral');

        if (value > 0.005) {
            element.classList.add('is-positive');
        } else if (value < -0.005) {
            element.classList.add('is-negative');
        } else {
            element.classList.add('is-neutral');
        }
    }

    function monthLabel(month) {
        const months = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];

        return months[month] || `Bulan ${month}`;
    }

    function formatNumber(value) {
        return numberFormatter.format(Number(value) || 0);
    }

    function formatCurrency(value) {
        return currencyFormatter.format(Number(value) || 0);
    }

    function formatCompactCurrency(value) {
        return compactCurrencyFormatter.format(Number(value) || 0);
    }

    function formatPercentage(value) {
        if (!Number.isFinite(value)) {
            return '0%';
        }

        const percent = value * 100;
        const formatted = percent.toLocaleString('id-ID', {
            maximumFractionDigits: Math.abs(percent) < 10 ? 1 : 0,
        });
        const prefix = percent > 0 ? '+' : '';

        return `${prefix}${formatted}%`;
    }

    function showToast(message, type = 'success') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        container.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.add('is-visible');
        });

        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    function lockCanvasSize(canvas, height) {
        if (!canvas) {
            return;
        }

        const targetHeight = height || canvas.parentElement?.clientHeight || 300;

        if (canvas.parentElement && height) {
            canvas.parentElement.style.height = `${height}px`;
        }

        canvas.style.height = `${targetHeight}px`;
        canvas.height = targetHeight;
    }
});
