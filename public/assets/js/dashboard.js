document.addEventListener('DOMContentLoaded', () => {
    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const tableBody = document.getElementById('registration-table');
    const filterStudent = document.getElementById('filter-student');
    const filterPayment = document.getElementById('filter-payment');
    const searchInput = document.getElementById('search-input');
    const refreshButton = document.getElementById('refresh-button');

    const statTotal = document.getElementById('stat-total');
    const statActive = document.getElementById('stat-active');
    const statPaid = document.getElementById('stat-paid');
    const statUnpaid = document.getElementById('stat-unpaid');

    const drawer = document.getElementById('drawer');
    const drawerBody = document.getElementById('drawer-body');
    const drawerTitle = document.getElementById('drawer-title');
    const drawerClose = document.getElementById('drawer-close');
    const drawerSave = document.getElementById('drawer-save');
    const drawerCancel = document.getElementById('drawer-cancel');

    let registrations = [];
    let selectedRegistration = null;

    refreshButton.addEventListener('click', () => loadRegistrations(true));
    filterStudent.addEventListener('change', applyFilters);
    filterPayment.addEventListener('change', applyFilters);
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
        const keyword = searchInput.value.trim().toLowerCase();

        const filtered = registrations.filter((item) => {
            const matchesStudent = student ? item.student_status === student : true;
            const matchesPayment = payment ? item.payment_status === payment : true;
            const haystack = [
                item.full_name,
                item.school_name,
                item.program_name,
                item.program_code,
                item.phone_number,
            ]
                .join(' ')
                .toLowerCase();
            const matchesSearch = keyword ? haystack.includes(keyword) : true;

            return matchesStudent && matchesPayment && matchesSearch;
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
                    <span>${escapeHtml(item.payment_notes ?? '-')}</span>
                </td>
                <td>
                    <span>${formatDate(item.created_at)}</span>
                </td>
            `;
            tr.classList.add('clickable-row');
            tr.addEventListener('click', () => openDrawer(item));
            fragment.appendChild(tr);
        });

        tableBody.appendChild(fragment);
    }

    function renderEmptyState(message) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="empty-state">${escapeHtml(message)}</td>
            </tr>
        `;
    }

    function openDrawer(registration) {
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
                <h3>Status Pembayaran</h3>
                <label class="input-field">
                    <span>Pilih status pembayaran</span>
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
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        selectedRegistration = null;
    }

    function handleSaveStatus() {
        if (!selectedRegistration) {
            return;
        }

        const studentStatus = document.getElementById('student-status-input').value;
        const paymentStatus = document.getElementById('payment-status-input').value;
        const paymentNotes = document.getElementById('payment-notes-input').value.trim();

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

        statTotal.textContent = total;
        statActive.textContent = active;
        statPaid.textContent = paid;
        statUnpaid.textContent = unpaid;
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

