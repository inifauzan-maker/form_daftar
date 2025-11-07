document.addEventListener('DOMContentLoaded', () => {
    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const currencyFormatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    });

    const tableBody = document.getElementById('registration-table');
    const filterLocation = document.getElementById('filter-location');
    const filterStudent = document.getElementById('filter-student');
    const filterPayment = document.getElementById('filter-payment');
    const searchInput = document.getElementById('search-input');
    const refreshButton = document.getElementById('refresh-button');

    const statTotal = document.getElementById('stat-total');
    const statActive = document.getElementById('stat-active');
    const statPaid = document.getElementById('stat-paid');
    const statUnpaid = document.getElementById('stat-unpaid');
    const statProgramName = document.getElementById('stat-program-name');
    const statProgramCount = document.getElementById('stat-program-count');

    const drawer = document.getElementById('drawer');
    const drawerBody = document.getElementById('drawer-body');
    const drawerTitle = document.getElementById('drawer-title');
    const drawerClose = document.getElementById('drawer-close');
    const drawerSave = document.getElementById('drawer-save');
    const drawerCancel = document.getElementById('drawer-cancel');
    let drawerLastTrigger = null;

    let registrations = [];
    let selectedRegistration = null;

    refreshButton.addEventListener('click', () => loadRegistrations(true));
    filterStudent.addEventListener('change', applyFilters);
    filterPayment.addEventListener('change', applyFilters);
    filterLocation.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    drawerClose.addEventListener('click', closeDrawer);
    drawerCancel.addEventListener('click', closeDrawer);
    drawer.addEventListener('click', (event) => {
        if (event.target === drawer) {
            closeDrawer();
        }
    });
    drawerSave.addEventListener('click', handleSaveStatus);

    loadRegistrations();

    function loadRegistrations(showToastOnRefresh = false) {
        setLoadingState(true);
        fetch(withBase('/api/registrations'))
            .then((response) => response.json())
            .then((payload) => {
                registrations = Array.isArray(payload.data) ? payload.data : [];
                applyFilters();
                updateStats();
                if (showToastOnRefresh) {
                    showToast('Data pendaftar diperbarui.');
                }
            })
            .catch(() => {
                renderEmptyState('Gagal memuat data. Silakan coba lagi.');
                showToast('Gagal memuat data pendaftar.', 'error');
            })
            .finally(() => {
                setLoadingState(false);
            });
    }

    function setLoadingState(isLoading) {
        refreshButton.disabled = isLoading;
        refreshButton.textContent = isLoading ? 'Memuat...' : 'Muat ulang';
    }

    function applyFilters() {
        const student = filterStudent.value;
        const payment = filterPayment.value;
        const location = filterLocation.value;
        const keyword = searchInput.value.trim().toLowerCase();

        const filtered = registrations.filter((item) => {
            const matchesStudent = student ? item.student_status === student : true;
            const matchesPayment = payment ? item.payment_status === payment : true;
            const matchesLocation = location ? item.study_location === location : true;
            const haystack = [
                item.full_name,
                item.school_name,
                item.program_name,
                item.program_code,
                item.phone_number,
                item.study_location,
                item.registration_number,
                item.invoice_number
            ]
                .join(' ')
                .toLowerCase();
            const matchesSearch = keyword ? haystack.includes(keyword) : true;

            return matchesStudent && matchesPayment && matchesLocation && matchesSearch;
        });

        renderTable(filtered);
    }

    function renderTable(rows) {
        tableBody.innerHTML = '';

        if (!rows.length) {
            renderEmptyState('Belum ada pendaftar dengan filter ini.');
            return;
        }

        const fragment = document.createDocumentFragment();

        rows.forEach((item) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="cell-primary">
                        <strong>${escapeHtml(item.full_name)}</strong>
                        <span>${escapeHtml(formatPhone(item.phone_number))}</span>
                    </div>
                </td>
                <td>
                    <div class="cell-secondary">
                        <span>${escapeHtml(item.school_name)}</span>
                        <small>Kelas ${escapeHtml(item.class_level)}</small>
                    </div>
                </td>
                <td>
                    <div class="cell-secondary">
                        <span>${escapeHtml(item.program_name)}</span>
                        <small>${escapeHtml(item.program_code)}</small>
                    </div>
                </td>
                <td>
                    <span class="badge badge-${item.student_status}">${statusLabel(item.student_status)}</span>
                </td>
                <td>
                    <span class="badge badge-payment-${item.payment_status}">${paymentLabel(item.payment_status)}</span>
                </td>
                <td>
                    <span>${escapeHtml(item.study_location ?? '-')}</span>
                </td>
                <td>
                    <span>${escapeHtml(item.registration_number ?? '-')}</span>
                </td>
                <td>
                    <span>${escapeHtml(item.invoice_number ?? '-')}</span>
                </td>
                <td>
                    <span>${escapeHtml(item.payment_notes ?? '-')}</span>
                </td>
                <td>
                    <span>${formatDate(item.created_at)}</span>
                </td>
                <td>
                    <a href="${withBase(`/dashboard/invoice?id=${item.id}`)}" class="btn-link invoice-link" target="_blank" rel="noopener">Unduh</a>
                </td>
            `;
            tr.classList.add('clickable-row');
            tr.addEventListener('click', () => openDrawer(item));
            const invoiceLink = tr.querySelector('.invoice-link');
            if (invoiceLink) {
                invoiceLink.addEventListener('click', (event) => event.stopPropagation());
            }
            fragment.appendChild(tr);
        });

        tableBody.appendChild(fragment);
    }

    function renderEmptyState(message) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="empty-state">${escapeHtml(message)}</td>
            </tr>
        `;
    }

    function openDrawer(registration) {
        drawerLastTrigger = document.activeElement instanceof HTMLElement ? document.activeElement : null;
        selectedRegistration = registration;
        drawerTitle.textContent = registration.full_name;
        drawerBody.innerHTML = `
            <div class="drawer-section">
                <h3>Informasi Kontak</h3>
                <dl class="detail-grid">
                    <div>
                        <dt>Nama</dt>
                        <dd>${escapeHtml(registration.full_name)}</dd>
                    </div>
                    <div>
                        <dt>Program</dt>
                        <dd>${escapeHtml(registration.program_name)} (${escapeHtml(registration.program_code)})</dd>
                    </div>
                    <div>
                        <dt>Nomor HP</dt>
                        <dd>${escapeHtml(formatPhone(registration.phone_number))}</dd>
                    </div>
                    <div>
                        <dt>Alamat</dt>
                        <dd>${escapeHtml(
                            [
                                registration.subdistrict,
                                registration.district,
                                registration.city,
                                registration.province,
                                registration.postal_code,
                            ]
                                .filter(Boolean)
                                .join(', ')
                        )}</dd>
                    </div>
                </dl>
            </div>

            <div class="drawer-section">
                <h3>Status Siswa</h3>
                <label class="input-field">
                    <span>Pilih status siswa</span>
                    <select id="student-status-input">
                        ${studentStatusOptions()
                            .map(
                                (value) =>
                                    `<option value="${value}" ${
                                        registration.student_status === value ? 'selected' : ''
                                    }>${statusLabel(value)}</option>`
                            )
                            .join('')}
                    </select>
            </label>
        </div>

        <div class="drawer-section">
            <h3>Lokasi & Registrasi</h3>
            <div class="form-grid form-grid--payment">
                <label class="input-field">
                    <span>Lokasi Belajar</span>
                    <select id="study-location-input">
                        ${['Bandung', 'Jaksel', 'Jaktim']
                            .map(
                                (value) =>
                                    `<option value="${value}" ${
                                        registration.study_location === value ? 'selected' : ''
                                    }>${value}</option>`
                            )
                            .join('')}
                    </select>
                </label>
                <label class="input-field">
                    <span>Nomor Registrasi</span>
                    <input type="text" id="registration-number-input" value="${escapeHtml(registration.registration_number ?? '')}" placeholder="Akan dibuat saat simpan" readonly data-initial-value="${escapeHtml(registration.registration_number ?? '')}">
                </label>
                <label class="input-field">
                    <span>Nomor Invoice</span>
                    <input type="text" id="invoice-number-input" value="${escapeHtml(registration.invoice_number ?? '')}" placeholder="Akan dibuat saat simpan" readonly data-initial-value="${escapeHtml(registration.invoice_number ?? '')}">
                </label>
            </div>
        </div>

        <div class="drawer-section">
            <h3>Form Pembayaran</h3>
            <div class="form-grid form-grid--payment">
                    ${renderCurrencyField('program-fee-input', 'Biaya Program (Rp)', registration.program_fee)}
                    ${renderCurrencyField('registration-fee-input', 'Biaya Registrasi (Rp)', registration.registration_fee)}
                    ${renderCurrencyField('discount-amount-input', 'Diskon (Rp)', registration.discount_amount)}
                    ${renderCurrencyField('total-due-input', 'Total Tagihan (Rp)', registration.total_due, true)}
                    ${renderCurrencyField('amount-paid-input', 'Jumlah Dibayar (Rp)', registration.amount_paid)}
                    ${renderCurrencyField('balance-due-input', 'Sisa Tagihan (Rp)', registration.balance_due, true)}
                    <label class="input-field">
                        <span>Tanggal Pembayaran Terakhir</span>
                        <input type="date" id="last-payment-input" value="${registration.last_payment_at ?? ''}">
                    </label>
                    <label class="input-field">
                        <span>Status Pembayaran</span>
                        <select id="payment-status-input">
                            ${paymentStatusOptions()
                                .map(
                                    (value) =>
                                        `<option value="${value}" ${
                                            registration.payment_status === value ? 'selected' : ''
                                        }>${paymentLabel(value)}</option>`
                                )
                                .join('')}
                        </select>
                    </label>
                </div>
                <div class="payment-summary" id="payment-summary">
                    <h4>Ringkasan Pembayaran</h4>
                    <div class="payment-summary__grid">
                        <div>
                            <span>Total Tagihan</span>
                            <strong data-summary="total_due">${formatCurrency(registration.total_due)}</strong>
                        </div>
                        <div>
                            <span>Total Dibayar</span>
                            <strong data-summary="amount_paid">${formatCurrency(registration.amount_paid)}</strong>
                        </div>
                        <div>
                            <span>Sisa Tagihan</span>
                            <strong data-summary="balance_due">${formatCurrency(registration.balance_due)}</strong>
                        </div>
                    </div>
                    <div class="payment-summary__meta">
                        <div>
                            <span>Status</span>
                            <strong data-summary="payment_status">${paymentLabel(registration.payment_status)}</strong>
                        </div>
                        <div>
                            <span>Pembayaran Terakhir</span>
                            <strong data-summary="last_payment">${registration.last_payment_at ? formatDateShort(registration.last_payment_at) : '-'}</strong>
                        </div>
                    </div>
                </div>
                <label class="input-field">
                    <span>Catatan Pembayaran</span>
                    <textarea id="payment-notes-input" rows="3" placeholder="Masukkan catatan pembayaran (opsional)">${escapeHtml(
                        registration.payment_notes ?? ''
                    )}</textarea>
                </label>
            </div>
        `;

        drawer.setAttribute('aria-hidden', 'false');
        drawer.classList.add('is-open');
        setupDrawerForm(registration);
        if (drawerClose) {
            drawerClose.focus();
        }
    }

    function renderCurrencyField(id, label, value, readOnly = false) {
        return `
            <label class="input-field">
                <span>${label}</span>
                <div class="input-group">
                    <span class="input-group__prefix">Rp</span>
                    <input type="number" id="${id}" min="0" step="1000" value="${formatNumberInput(value)}" ${readOnly ? 'readonly' : ''}>
                </div>
            </label>
        `;
    }

    function setupDrawerForm(registration) {
        const locationSelect = document.getElementById('study-location-input');
        const registrationNumberInput = document.getElementById('registration-number-input');
        const invoiceNumberInput = document.getElementById('invoice-number-input');
        const initialLocation = locationSelect ? locationSelect.value : '';
        const initialRegistrationNumber = registrationNumberInput ? registrationNumberInput.value : '';
        const initialInvoiceNumber = invoiceNumberInput ? invoiceNumberInput.value : '';

        const programInput = document.getElementById('program-fee-input');
        const registrationFeeInput = document.getElementById('registration-fee-input');
        const discountInput = document.getElementById('discount-amount-input');
        const totalDueInput = document.getElementById('total-due-input');
        const amountPaidInput = document.getElementById('amount-paid-input');
        const balanceDueInput = document.getElementById('balance-due-input');
        const lastPaymentInput = document.getElementById('last-payment-input');
        const paymentStatusInput = document.getElementById('payment-status-input');
        const summary = document.getElementById('payment-summary');
        const summaryRefs = {
            total_due: summary.querySelector('[data-summary="total_due"]'),
            amount_paid: summary.querySelector('[data-summary="amount_paid"]'),
            balance_due: summary.querySelector('[data-summary="balance_due"]'),
            payment_status: summary.querySelector('[data-summary="payment_status"]'),
            last_payment: summary.querySelector('[data-summary="last_payment"]'),
        };

        const recalc = () => {
            const program = parseMoney(programInput.value);
            const regFee = parseMoney(registrationFeeInput.value);
            const discount = parseMoney(discountInput.value);
            const paid = parseMoney(amountPaidInput.value);
            const total = Math.max(0, program + regFee - discount);
            const balance = Math.max(0, total - paid);

            totalDueInput.value = formatNumberInput(total);
            balanceDueInput.value = formatNumberInput(balance);

            summaryRefs.total_due.textContent = formatCurrency(total);
            summaryRefs.amount_paid.textContent = formatCurrency(paid);
            summaryRefs.balance_due.textContent = formatCurrency(balance);
        };

        const updateStatusSummary = () => {
            summaryRefs.payment_status.textContent = paymentLabel(paymentStatusInput.value);
        };

        const updateLastPaymentSummary = () => {
            summaryRefs.last_payment.textContent = lastPaymentInput.value
                ? formatDateShort(lastPaymentInput.value)
                : '-';
        };

        if (locationSelect) {
            locationSelect.addEventListener('change', () => {
                if (locationSelect.value !== initialLocation) {
                    if (registrationNumberInput) {
                        registrationNumberInput.value = '';
                    }
                    if (invoiceNumberInput) {
                        invoiceNumberInput.value = '';
                    }
                } else {
                    if (registrationNumberInput) {
                        registrationNumberInput.value = initialRegistrationNumber;
                    }
                    if (invoiceNumberInput) {
                        invoiceNumberInput.value = initialInvoiceNumber;
                    }
                }
            });
        }

        [programInput, registrationFeeInput, discountInput, amountPaidInput].forEach((input) => {
            input.addEventListener('input', recalc);
        });

        paymentStatusInput.addEventListener('change', updateStatusSummary);
        lastPaymentInput.addEventListener('change', updateLastPaymentSummary);

        recalc();
        updateStatusSummary();
        updateLastPaymentSummary();
    }

    function closeDrawer() {
        if (drawer.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        selectedRegistration = null;
        if (drawerLastTrigger && typeof drawerLastTrigger.focus === 'function') {
            drawerLastTrigger.focus();
        }
        drawerLastTrigger = null;
    }

    function handleSaveStatus() {
        if (!selectedRegistration) {
            return;
        }

        const studentStatus = document.getElementById('student-status-input').value;
        const paymentStatus = document.getElementById('payment-status-input').value;
        const paymentNotes = document.getElementById('payment-notes-input').value.trim();
        const studyLocation = document.getElementById('study-location-input').value;
        const registrationNumber = document.getElementById('registration-number-input').value.trim();
        const invoiceNumber = document.getElementById('invoice-number-input').value.trim();
        const programFee = parseMoney(document.getElementById('program-fee-input').value);
        const registrationFee = parseMoney(document.getElementById('registration-fee-input').value);
        const discountAmount = parseMoney(document.getElementById('discount-amount-input').value);
        const totalDue = parseMoney(document.getElementById('total-due-input').value);
        const amountPaid = parseMoney(document.getElementById('amount-paid-input').value);
        const balanceDue = parseMoney(document.getElementById('balance-due-input').value);
        const lastPaymentAt = document.getElementById('last-payment-input').value;

        drawerSave.disabled = true;
        drawerSave.textContent = 'Menyimpan...';

        fetch(withBase('/api/registrations/status'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: selectedRegistration.id,
                student_status: studentStatus,
                payment_status: paymentStatus,
                payment_notes: paymentNotes,
                study_location: studyLocation,
                registration_number: registrationNumber,
                invoice_number: invoiceNumber,
                program_fee: programFee,
                registration_fee: registrationFee,
                discount_amount: discountAmount,
                total_due: totalDue,
                amount_paid: amountPaid,
                balance_due: balanceDue,
                last_payment_at: lastPaymentAt,
            }),
        })
            .then(async (response) => {
                const result = await response.json();

                if (!response.ok) {
                    const messages = Object.values(result.errors ?? {})
                        .flat()
                        .join('<br>');
                    showToast(messages || 'Gagal memperbarui status.', 'error');
                    return;
                }

                showToast('Status pendaftar diperbarui.');
                closeDrawer();
                loadRegistrations();
            })
            .catch(() => {
                showToast('Terjadi kesalahan jaringan.', 'error');
            })
            .finally(() => {
                drawerSave.disabled = false;
                drawerSave.textContent = 'Simpan Perubahan';
            });
    }

    function updateStats() {
        const total = registrations.length;
        const active = registrations.filter((item) => item.student_status === 'active').length;
        const paid = registrations.filter((item) => item.payment_status === 'paid').length;
        const unpaid = registrations.filter((item) => item.payment_status === 'unpaid').length;
        const programCounts = registrations.reduce((acc, item) => {
            const key = item.program_name || 'Tidak diketahui';
            acc[key] = (acc[key] || 0) + 1;
            return acc;
        }, {});
        let topProgramName = '-';
        let topProgramCount = 0;
        Object.entries(programCounts).forEach(([name, count]) => {
            if (count > topProgramCount) {
                topProgramName = name;
                topProgramCount = count;
            }
        });

        statTotal.textContent = total;
        statActive.textContent = active;
        statPaid.textContent = paid;
        statUnpaid.textContent = unpaid;
        if (statProgramName) {
            statProgramName.textContent = topProgramName;
        }
        if (statProgramCount) {
            statProgramCount.textContent = `${topProgramCount} pendaftar`;
        }
    }

    function parseMoney(value) {
        return Number.parseFloat(value) || 0;
    }

    function formatNumberInput(value) {
        return (Number.parseFloat(value) || 0).toFixed(0);
    }

    function formatCurrency(value) {
        return currencyFormatter.format(Number.parseFloat(value) || 0);
    }

    function formatDateShort(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
        }).format(date);
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    }

    function formatPhone(value) {
        const digits = (value ?? '').replace(/\D/g, '');
        if (!digits) {
            return value ?? '-';
        }

        return digits.replace(/(\d{2})(\d{3,4})(\d{3,4})(\d{0,4})/, (_, a, b, c, d) =>
            [a, b, c, d].filter(Boolean).join(' ')
        );
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function studentStatusOptions() {
        return ['pending', 'active', 'graduated', 'dropped'];
    }

    function paymentStatusOptions() {
        return ['unpaid', 'partial', 'paid'];
    }

    function statusLabel(key) {
        return (
            {
                pending: 'Pending',
                active: 'Aktif',
                graduated: 'Lulus',
                dropped: 'Berhenti',
            }[key] || key
        );
    }

    function paymentLabel(key) {
        return (
            {
                unpaid: 'Belum bayar',
                partial: 'Cicil',
                paid: 'Lunas',
            }[key] || key
        );
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
        toast.innerHTML = escapeHtml(message);
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('is-visible');
        }, 20);

        setTimeout(() => {
            toast.classList.remove('is-visible');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }
});



