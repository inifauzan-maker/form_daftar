document.addEventListener('DOMContentLoaded', () => {
    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const canManageRoles = String(window.APP_CAN_MANAGE_ROLES || '').toLowerCase() === 'true';
    const canManagePermissions = String(window.APP_CAN_MANAGE_PERMISSIONS || '').toLowerCase() === 'true';
    const currentUserId = typeof window.APP_CURRENT_USER_ID === 'number' ? window.APP_CURRENT_USER_ID : null;

    const state = {
        users: [],
        roles: [],
        permissions: [],
        selectedUserId: null,
        selectedRoleId: null,
        selectedPermissionId: null,
    };

    const userTableBody = document.querySelector('#user-table tbody');
    const userSearchInput = document.getElementById('user-search');
    const userForm = document.getElementById('user-form');
    const userFormTitle = document.getElementById('user-form-title');
    const userCreateNewButton = document.getElementById('user-create-new');
    const userResetButton = document.getElementById('user-reset');
    const userDeleteButton = document.getElementById('user-delete');
    const userPasswordInput = document.getElementById('user-password');

    const roleTableBody = document.querySelector('#role-table tbody');
    const roleForm = document.getElementById('role-form');
    const roleFormTitle = document.getElementById('role-form-title');
    const roleResetButton = document.getElementById('role-reset');
    const roleDeleteButton = document.getElementById('role-delete');

    const permissionTableBody = document.querySelector('#permission-table tbody');
    const permissionForm = document.getElementById('permission-form');
    const permissionFormTitle = document.getElementById('permission-form-title');
    const permissionResetButton = document.getElementById('permission-reset');
    const permissionDeleteButton = document.getElementById('permission-delete');

    const userRolesContainer = document.getElementById('user-roles');
    const userPermissionsContainer = document.getElementById('user-permissions');
    const rolePermissionsContainer = document.getElementById('role-permissions');

    init();

    function init() {
        bindEvents();
        loadInitialData();
    }

    function bindEvents() {
        if (userForm) {
            userForm.addEventListener('submit', handleUserSubmit);
        }

        if (userResetButton) {
            userResetButton.addEventListener('click', () => resetUserForm());
        }

        if (userCreateNewButton) {
            userCreateNewButton.addEventListener('click', () => resetUserForm());
        }

        if (userDeleteButton) {
            userDeleteButton.addEventListener('click', handleUserDelete);
        }

        if (userSearchInput) {
            userSearchInput.addEventListener('input', renderUserTable);
        }

        if (userTableBody) {
            userTableBody.addEventListener('click', (event) => {
                const row = event.target.closest('tr[data-id]');
                if (!row) {
                    return;
                }

                const userId = Number.parseInt(row.getAttribute('data-id'), 10);

                if (Number.isNaN(userId)) {
                    return;
                }

                const user = state.users.find((item) => item.id === userId);
                if (user) {
                    fillUserForm(user);
                }
            });
        }

        if (roleTableBody) {
            roleTableBody.addEventListener('click', (event) => {
                if (!canManageRoles) {
                    return;
                }

                const row = event.target.closest('tr[data-id]');
                if (!row) {
                    return;
                }

                const roleId = Number.parseInt(row.getAttribute('data-id'), 10);
                if (Number.isNaN(roleId)) {
                    return;
                }

                const role = state.roles.find((item) => item.id === roleId);
                if (role) {
                    fillRoleForm(role);
                }
            });
        }

        if (roleForm) {
            roleForm.addEventListener('submit', handleRoleSubmit);
        }

        if (roleResetButton) {
            roleResetButton.addEventListener('click', () => resetRoleForm());
        }

        if (roleDeleteButton) {
            roleDeleteButton.addEventListener('click', handleRoleDelete);
        }

        if (permissionTableBody) {
            permissionTableBody.addEventListener('click', (event) => {
                if (!canManagePermissions) {
                    return;
                }

                const row = event.target.closest('tr[data-id]');
                if (!row) {
                    return;
                }

                const permissionId = Number.parseInt(row.getAttribute('data-id'), 10);
                if (Number.isNaN(permissionId)) {
                    return;
                }

                const permission = state.permissions.find((item) => item.id === permissionId);
                if (permission) {
                    fillPermissionForm(permission);
                }
            });
        }

        if (permissionForm) {
            permissionForm.addEventListener('submit', handlePermissionSubmit);
        }

        if (permissionResetButton) {
            permissionResetButton.addEventListener('click', () => resetPermissionForm());
        }

        if (permissionDeleteButton) {
            permissionDeleteButton.addEventListener('click', handlePermissionDelete);
        }
    }

    function loadInitialData() {
        loadPermissions()
            .then(() => loadRoles())
            .then(() => {
                renderUserAssignments();
                renderRolePermissions();
            })
            .then(() => loadUsers())
            .catch((error) => {
                console.error(error);
                showToast('Gagal memuat data awal manajemen pengguna.', 'error');
            });
    }

    function loadUsers() {
        return fetchJson('/api/users')
            .then((response) => {
                state.users = Array.isArray(response.data) ? response.data : [];
                renderUserTable();
            })
            .catch((error) => {
                console.error(error);
                renderUserTableError('Gagal memuat data pengguna.');
            });
    }

    function loadRoles() {
        return fetchJson('/api/roles')
            .then((response) => {
                state.roles = Array.isArray(response.data) ? response.data : [];
                renderRoleTable();
                renderUserRoleOptions();
            })
            .catch((error) => {
                console.error(error);
                renderRoleTableError('Gagal memuat data peran.');
            });
    }

    function loadPermissions() {
        return fetchJson('/api/permissions')
            .then((response) => {
                state.permissions = Array.isArray(response.data) ? response.data : [];
                renderPermissionTable();
                renderUserPermissionOptions();
                renderRolePermissions();
            })
            .catch((error) => {
                console.error(error);
                renderPermissionTableError('Gagal memuat data izin.');
            });
    }

    /**
     * Users
     */

    function renderUserTable() {
        if (!userTableBody) {
            return;
        }

        userTableBody.innerHTML = '';

        const keyword = (userSearchInput?.value || '').trim().toLowerCase();

        const filtered = state.users.filter((user) => {
            if (!keyword) {
                return true;
            }

            const haystack = [
                user.name,
                user.email,
                ...(user.roles || []).map((role) => role.name),
            ]
                .join(' ')
                .toLowerCase();

            return haystack.includes(keyword);
        });

        if (!filtered.length) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="4" class="empty-state">Pengguna tidak ditemukan.</td>';
            userTableBody.appendChild(row);
            return;
        }

        filtered
            .sort((a, b) => a.name.localeCompare(b.name, 'id'))
            .forEach((user) => {
                const row = document.createElement('tr');
                row.dataset.id = String(user.id);
                if (state.selectedUserId === user.id) {
                    row.classList.add('is-selected');
                }
                row.innerHTML = `
                    <td>
                        <div class="cell-primary">
                            <strong>${escapeHtml(user.name)}</strong>
                            <span>${escapeHtml(user.email)}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${renderUserStatus(user.status)}</td>
                    <td>${renderRoleTags(user.roles)}</td>
                `;
                userTableBody.appendChild(row);
            });
    }

    function renderUserTableError(message) {
        if (!userTableBody) {
            return;
        }

        userTableBody.innerHTML = `<tr><td colspan="4" class="empty-state">${escapeHtml(message)}</td></tr>`;
    }

    function renderUserStatus(status) {
        const normalized = status === 'inactive' ? 'inactive' : 'active';
        const label = normalized === 'inactive' ? 'Nonaktif' : 'Aktif';
        return `<span class="status-badge status-${normalized}">${label}</span>`;
    }

    function renderRoleTags(roles) {
        if (!Array.isArray(roles) || !roles.length) {
            return '<span class="tag tag--muted">Tidak ada</span>';
        }

        return `<div class="tag-group">${roles
            .map((role) => `<span class="tag">${escapeHtml(role.name)}</span>`)
            .join('')}</div>`;
    }

    function fillUserForm(user) {
        if (!userForm) {
            return;
        }

        state.selectedUserId = user.id;
        userFormTitle.textContent = 'Edit Pengguna';
        userForm.querySelector('#user-id').value = user.id;
        userForm.querySelector('#user-name').value = user.name || '';
        userForm.querySelector('#user-email').value = user.email || '';
        userForm.querySelector('#user-status').value = user.status === 'inactive' ? 'inactive' : 'active';
        userPasswordInput.value = '';

        setCheckedValues(userRolesContainer, (user.roles || []).map((role) => role.id));
        setCheckedValues(userPermissionsContainer, (user.direct_permissions || []).map((permission) => permission.id));

        if (userDeleteButton) {
            const isSelf = currentUserId !== null && currentUserId === user.id;
            userDeleteButton.classList.toggle('is-hidden', isSelf);
            userDeleteButton.disabled = isSelf;
        }

        clearFormErrors(userForm);
        renderUserTable();
    }

    function resetUserForm() {
        if (!userForm) {
            return;
        }

        state.selectedUserId = null;
        userFormTitle.textContent = 'Tambah Pengguna';
        userForm.reset();
        userForm.querySelector('#user-id').value = '';
        userForm.querySelector('#user-status').value = 'active';
        setCheckedValues(userRolesContainer, []);
        setCheckedValues(userPermissionsContainer, []);

        if (userDeleteButton) {
            userDeleteButton.classList.add('is-hidden');
            userDeleteButton.disabled = false;
        }

        clearFormErrors(userForm);
        renderUserTable();
    }

    function handleUserSubmit(event) {
        event.preventDefault();

        clearFormErrors(userForm);

        const payload = {
            name: userForm.querySelector('#user-name').value.trim(),
            email: userForm.querySelector('#user-email').value.trim(),
            status: userForm.querySelector('#user-status').value,
            password: userPasswordInput.value,
            roles: getCheckedValues(userRolesContainer),
            permissions: getCheckedValues(userPermissionsContainer),
        };

        const isEditing = Boolean(state.selectedUserId);

        if (isEditing) {
            payload.id = state.selectedUserId;
        }

        const endpoint = isEditing ? '/api/users/update' : '/api/users';

        fetchJson(endpoint, {
            method: 'POST',
            body: payload,
        })
            .then((response) => {
                showToast(response.message || 'Perubahan tersimpan.');
                return loadUsers();
            })
            .then(() => {
                if (!state.selectedUserId) {
                    resetUserForm();
                } else {
                    const updated = state.users.find((item) => item.id === payload.id);
                    if (updated) {
                        fillUserForm(updated);
                    }
                }
            })
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(userForm, error.data.errors, {
                        name: 'name',
                        email: 'email',
                        password: 'password',
                        status: 'status',
                    });
                } else {
                    showToast(error.message || 'Gagal menyimpan pengguna.', 'error');
                }
            });
    }

    function handleUserDelete() {
        if (!state.selectedUserId) {
            return;
        }

        if (!window.confirm('Hapus pengguna ini? Tindakan tidak dapat dibatalkan.')) {
            return;
        }

        fetchJson('/api/users/delete', {
            method: 'POST',
            body: { id: state.selectedUserId },
        })
            .then((response) => {
                showToast(response.message || 'Pengguna dihapus.');
                return loadUsers();
            })
            .then(() => resetUserForm())
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(userForm, error.data.errors, { id: 'id' });
                } else {
                    showToast(error.message || 'Gagal menghapus pengguna.', 'error');
                }
            });
    }

    function renderUserRoleOptions() {
        renderCheckboxGroup(userRolesContainer, 'user-role', state.roles);
    }

    function renderUserPermissionOptions() {
        renderCheckboxGroup(userPermissionsContainer, 'user-permission', state.permissions);
    }

    function renderUserAssignments() {
        renderUserRoleOptions();
        renderUserPermissionOptions();
    }

    /**
     * Roles
     */

    function renderRoleTable() {
        if (!roleTableBody) {
            return;
        }

        roleTableBody.innerHTML = '';

        if (!Array.isArray(state.roles) || !state.roles.length) {
            roleTableBody.innerHTML = '<tr><td colspan="3" class="empty-state">Belum ada peran.</td></tr>';
            return;
        }

        state.roles
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name, 'id'))
            .forEach((role) => {
                const row = document.createElement('tr');
                row.dataset.id = String(role.id);
                if (state.selectedRoleId === role.id) {
                    row.classList.add('is-selected');
                }
                row.innerHTML = `
                    <td>
                        <div class="cell-primary">
                            <strong>${escapeHtml(role.name)}</strong>
                            <span>${escapeHtml(role.description || '')}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(role.slug)}</td>
                    <td>${renderPermissionTags(role.permissions)}</td>
                `;
                roleTableBody.appendChild(row);
            });
    }

    function renderRoleTableError(message) {
        if (!roleTableBody) {
            return;
        }

        roleTableBody.innerHTML = `<tr><td colspan="3" class="empty-state">${escapeHtml(message)}</td></tr>`;
    }

    function fillRoleForm(role) {
        if (!roleForm || !canManageRoles) {
            return;
        }

        state.selectedRoleId = role.id;
        roleFormTitle.textContent = 'Edit Peran';
        roleForm.querySelector('#role-id').value = role.id;
        roleForm.querySelector('#role-name').value = role.name || '';
        roleForm.querySelector('#role-slug').value = role.slug || '';
        roleForm.querySelector('#role-description').value = role.description || '';

        setCheckedValues(rolePermissionsContainer, (role.permissions || []).map((permission) => permission.id));

        const isProtected = role.slug === 'admin';
        if (roleDeleteButton) {
            roleDeleteButton.classList.toggle('is-hidden', isProtected);
            roleDeleteButton.disabled = isProtected;
        }

        clearFormErrors(roleForm);
        renderRoleTable();
    }

    function resetRoleForm() {
        if (!roleForm || !canManageRoles) {
            return;
        }

        state.selectedRoleId = null;
        roleFormTitle.textContent = 'Tambah Peran';
        roleForm.reset();
        roleForm.querySelector('#role-id').value = '';
        setCheckedValues(rolePermissionsContainer, []);

        if (roleDeleteButton) {
            roleDeleteButton.classList.add('is-hidden');
            roleDeleteButton.disabled = false;
        }

        clearFormErrors(roleForm);
        renderRoleTable();
    }

    function handleRoleSubmit(event) {
        if (!canManageRoles) {
            return;
        }

        event.preventDefault();
        clearFormErrors(roleForm);

        const payload = {
            name: roleForm.querySelector('#role-name').value.trim(),
            slug: roleForm.querySelector('#role-slug').value.trim(),
            description: roleForm.querySelector('#role-description').value.trim(),
            permissions: getCheckedValues(rolePermissionsContainer),
        };

        const isEditing = Boolean(state.selectedRoleId);
        if (isEditing) {
            payload.id = state.selectedRoleId;
        }

        const endpoint = isEditing ? '/api/roles/update' : '/api/roles';

        fetchJson(endpoint, {
            method: 'POST',
            body: payload,
        })
            .then((response) => {
                showToast(response.message || 'Peran tersimpan.');
                return loadRoles();
            })
            .then(() => {
                renderUserRoleOptions();
                return loadUsers();
            })
            .then(() => {
                if (!state.selectedRoleId) {
                    resetRoleForm();
                } else {
                    const updated = state.roles.find((item) => item.id === payload.id);
                    if (updated) {
                        fillRoleForm(updated);
                    }
                }
            })
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(roleForm, error.data.errors, {
                        name: 'role-name',
                        slug: 'role-slug',
                    });
                } else {
                    showToast(error.message || 'Gagal menyimpan peran.', 'error');
                }
            });
    }

    function handleRoleDelete() {
        if (!canManageRoles || !state.selectedRoleId) {
            return;
        }

        if (!window.confirm('Hapus peran ini? Tindakan tidak dapat dibatalkan.')) {
            return;
        }

        fetchJson('/api/roles/delete', {
            method: 'POST',
            body: { id: state.selectedRoleId },
        })
            .then((response) => {
                showToast(response.message || 'Peran dihapus.');
                return loadRoles();
            })
            .then(() => {
                renderUserRoleOptions();
                setCheckedValues(userRolesContainer, []);
                return loadUsers();
            })
            .then(() => resetRoleForm())
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(roleForm, error.data.errors, { id: 'role-id' });
                } else {
                    showToast(error.message || 'Gagal menghapus peran.', 'error');
                }
            });
    }

    function renderRolePermissions() {
        renderCheckboxGroup(rolePermissionsContainer, 'role-permission', state.permissions, !canManageRoles);
    }

    /**
     * Permissions
     */

    function renderPermissionTable() {
        if (!permissionTableBody) {
            return;
        }

        permissionTableBody.innerHTML = '';

        if (!Array.isArray(state.permissions) || !state.permissions.length) {
            permissionTableBody.innerHTML = '<tr><td colspan="3" class="empty-state">Belum ada izin.</td></tr>';
            return;
        }

        state.permissions
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name, 'id'))
            .forEach((permission) => {
                const row = document.createElement('tr');
                row.dataset.id = String(permission.id);
                if (state.selectedPermissionId === permission.id) {
                    row.classList.add('is-selected');
                }
                row.innerHTML = `
                    <td>
                        <div class="cell-primary">
                            <strong>${escapeHtml(permission.name)}</strong>
                            <span>${escapeHtml(permission.description || '')}</span>
                        </div>
                    </td>
                    <td>${escapeHtml(permission.slug)}</td>
                    <td>${escapeHtml(permission.description || '-')}</td>
                `;
                permissionTableBody.appendChild(row);
            });
    }

    function renderPermissionTableError(message) {
        if (!permissionTableBody) {
            return;
        }

        permissionTableBody.innerHTML = `<tr><td colspan="3" class="empty-state">${escapeHtml(message)}</td></tr>`;
    }

    function fillPermissionForm(permission) {
        if (!permissionForm || !canManagePermissions) {
            return;
        }

        state.selectedPermissionId = permission.id;
        permissionFormTitle.textContent = 'Edit Izin';
        permissionForm.querySelector('#permission-id').value = permission.id;
        permissionForm.querySelector('#permission-name').value = permission.name || '';
        permissionForm.querySelector('#permission-slug').value = permission.slug || '';
        permissionForm.querySelector('#permission-description').value = permission.description || '';

        clearFormErrors(permissionForm);
        renderPermissionTable();
    }

    function resetPermissionForm() {
        if (!permissionForm || !canManagePermissions) {
            return;
        }

        state.selectedPermissionId = null;
        permissionFormTitle.textContent = 'Tambah Izin';
        permissionForm.reset();
        permissionForm.querySelector('#permission-id').value = '';
        clearFormErrors(permissionForm);
        renderPermissionTable();
    }

    function handlePermissionSubmit(event) {
        if (!canManagePermissions) {
            return;
        }

        event.preventDefault();
        clearFormErrors(permissionForm);

        const payload = {
            name: permissionForm.querySelector('#permission-name').value.trim(),
            slug: permissionForm.querySelector('#permission-slug').value.trim(),
            description: permissionForm.querySelector('#permission-description').value.trim(),
        };

        const isEditing = Boolean(state.selectedPermissionId);
        if (isEditing) {
            payload.id = state.selectedPermissionId;
        }

        const endpoint = isEditing ? '/api/permissions/update' : '/api/permissions';

        fetchJson(endpoint, {
            method: 'POST',
            body: payload,
        })
            .then((response) => {
                showToast(response.message || 'Izin tersimpan.');
                return loadPermissions();
            })
            .then(() => {
                renderRolePermissions();
                renderUserPermissionOptions();
                return loadRoles();
            })
            .then(() => loadUsers())
            .then(() => {
                if (!state.selectedPermissionId) {
                    resetPermissionForm();
                } else {
                    const updated = state.permissions.find((item) => item.id === payload.id);
                    if (updated) {
                        fillPermissionForm(updated);
                    }
                }
            })
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(permissionForm, error.data.errors, {
                        name: 'permission-name',
                        slug: 'permission-slug',
                    });
                } else {
                    showToast(error.message || 'Gagal menyimpan izin.', 'error');
                }
            });
    }

    function handlePermissionDelete() {
        if (!canManagePermissions || !state.selectedPermissionId) {
            return;
        }

        if (!window.confirm('Hapus izin ini? Tindakan tidak dapat dibatalkan.')) {
            return;
        }

        fetchJson('/api/permissions/delete', {
            method: 'POST',
            body: { id: state.selectedPermissionId },
        })
            .then((response) => {
                showToast(response.message || 'Izin dihapus.');
                return loadPermissions();
            })
            .then(() => {
                renderRolePermissions();
                renderUserPermissionOptions();
                return loadRoles();
            })
            .then(() => loadUsers())
            .then(() => resetPermissionForm())
            .catch((error) => {
                if (error.data && error.data.errors) {
                    applyFormErrors(permissionForm, error.data.errors, { id: 'permission-id' });
                } else {
                    showToast(error.message || 'Gagal menghapus izin.', 'error');
                }
            });
    }

    /**
     * Rendering helpers
     */

    function renderCheckboxGroup(container, prefix, items, disabled = false) {
        if (!container) {
            return;
        }

        container.innerHTML = '';

        if (!Array.isArray(items) || !items.length) {
            container.innerHTML = '<span class="empty-state">Belum tersedia.</span>';
            return;
        }

        items.forEach((item) => {
            const id = `${prefix}-${item.id}`;
            const wrapper = document.createElement('label');
            wrapper.className = 'checkbox-pill';
            wrapper.innerHTML = `
                <input type="checkbox" id="${id}" value="${item.id}" ${disabled ? 'disabled' : ''}>
                <span>${escapeHtml(item.name)}<small>${escapeHtml(item.slug)}</small></span>
            `;
            container.appendChild(wrapper);
        });
    }

    function setCheckedValues(container, values) {
        if (!container) {
            return;
        }

        const set = new Set(values.map((value) => Number(value)));
        container.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            input.checked = set.has(Number(input.value));
        });
    }

    function getCheckedValues(container) {
        if (!container) {
            return [];
        }

        return Array.from(container.querySelectorAll('input[type="checkbox"]:checked')).map((input) =>
            Number(input.value)
        );
    }

    function renderPermissionTags(permissions) {
        if (!Array.isArray(permissions) || !permissions.length) {
            return '<span class="tag tag--muted">Tidak ada</span>';
        }

        return `<div class="tag-group">${permissions
            .map((permission) => `<span class="tag tag--soft">${escapeHtml(permission.slug)}</span>`)
            .join('')}</div>`;
    }

    /**
     * Network helper
     */

    function fetchJson(path, options = {}) {
        const opts = {
            method: 'GET',
            credentials: 'include',
            headers: {
                Accept: 'application/json',
            },
            ...options,
        };

        if (opts.body && typeof opts.body !== 'string') {
            opts.body = JSON.stringify(opts.body);
            opts.headers = {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(options.headers || {}),
            };
        }

        return fetch(withBase(path), opts).then(async (response) => {
            let data = {};
            try {
                data = await response.json();
            } catch (error) {
                // ignore parse error
            }

            if (!response.ok) {
                const error = new Error(data.message || 'Permintaan gagal diproses.');
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return data;
        });
    }

    /**
     * Form helpers
     */

    function applyFormErrors(form, errors, mapping) {
        if (!form || !errors) {
            return;
        }

        Object.entries(errors).forEach(([field, messages]) => {
            const target = mapping[field] || field;
            const errorTarget =
                form.querySelector(`[data-error-for="${target}"]`) ||
                form.querySelector(`[name="${target}"]`)?.closest('label')?.querySelector('.field-error');

            if (errorTarget) {
                errorTarget.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            }
        });
    }

    function clearFormErrors(form) {
        if (!form) {
            return;
        }

        form.querySelectorAll('.field-error').forEach((element) => {
            element.textContent = '';
        });
    }

    /**
     * UI helpers
     */

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

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});

