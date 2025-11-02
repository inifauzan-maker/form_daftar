document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registration-form');
    const successAlert = document.getElementById('alert-success');
    const errorAlert = document.getElementById('alert-error');
    const schoolIdInput = document.getElementById('school-id');
    const schoolNameInput = document.getElementById('school-name');
    const classSelect = document.getElementById('class-level');
    const programSelect = document.getElementById('program');
    const phoneInput = document.getElementById('phone-number');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const subdistrictSelect = document.getElementById('subdistrict');
    const postalCodeInput = document.getElementById('postal-code');

    const summaryElements = {
        full_name: document.querySelector('[data-summary="full_name"]'),
        school_name: document.querySelector('[data-summary="school_name"]'),
        class_level: document.querySelector('[data-summary="class_level"]'),
        phone_number: document.querySelector('[data-summary="phone_number"]'),
        address: document.querySelector('[data-summary="address"]'),
        program: document.querySelector('[data-summary="program"]'),
    };
    const steps = Array.from(document.querySelectorAll('#form-progress .step'));
    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const classOptionsMap = {
        SD: ['I', 'II', 'III', 'IV', 'V', 'VI'],
        SMP: ['VII', 'VIII', 'IX'],
        SMA: ['X', 'XI', 'XII'],
    };

    const tomSelect = new TomSelect('#school-selector', {
        valueField: 'id',
        labelField: 'label',
        searchField: ['name', 'city', 'province'],
        maxOptions: 50,
        loadThrottle: 400,
        placeholder: 'Cari SMAN/SMAS/SMK/MA favorit...',
        create: false,
        render: {
            option(data, escape) {
                return `
                    <div class="option">
                        <div><strong>${escape(data.name)}</strong></div>
                        <div class="text-muted">${escape(data.type)} - ${escape(data.city)}, ${escape(data.province)}</div>
                    </div>
                `;
            },
            item(data, escape) {
                return `<div>${escape(data.name)} (${escape(data.city)})</div>`;
            },
        },
        load(query, callback) {
            const url = withBase(`/api/schools?q=${encodeURIComponent(query)}`);

            fetch(url)
                .then((response) => response.json())
                .then((payload) => callback(payload.data ?? []))
                .catch(() => callback());
        },
        onChange(value) {
            const option = value ? tomSelect.options[value] : null;
            schoolIdInput.value = value || '';
            schoolNameInput.value = option ? option.name : '';

            updateClassLevels(option ? option.level_group : null);
            resetPrograms();
            refreshUi();
        },
    });

    updateClassLevels(null);
    resetPrograms();
    refreshUi();

    function updateClassLevels(levelGroup) {
        classSelect.innerHTML = '';

        if (!levelGroup || !classOptionsMap[levelGroup]) {
            classSelect.disabled = true;
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Pilih kelas';
            classSelect.appendChild(option);
            return;
        }

        const options = classOptionsMap[levelGroup];
        classSelect.disabled = false;
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Pilih kelas';
        classSelect.appendChild(placeholder);

        options.forEach((level) => {
            const opt = document.createElement('option');
            opt.value = level;
            opt.textContent = level;
            classSelect.appendChild(opt);
        });

        refreshUi();
    }

    classSelect.addEventListener('change', () => {
        const classLevel = classSelect.value;
        resetPrograms();

        if (classLevel) {
            loadPrograms(classLevel);
        }

        refreshUi();
    });

    function loadPrograms(classLevel) {
        programSelect.disabled = true;
        programSelect.innerHTML = '';

        fetch(withBase(`/api/programs?classLevel=${encodeURIComponent(classLevel)}`))
            .then((response) => response.json())
            .then((payload) => {
                const programs = payload.data ?? [];

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = programs.length ? 'Pilih program bimbel' : 'Program tidak tersedia';
                programSelect.appendChild(placeholder);

                programs.forEach((program) => {
                    const option = document.createElement('option');
                    option.value = program.id;
                    option.textContent = `${program.name} (${program.code})`;
                    programSelect.appendChild(option);
                });

                programSelect.disabled = programs.length === 0;
                refreshUi();
            })
            .catch(() => {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Program tidak dapat dimuat';
                programSelect.appendChild(option);
                programSelect.disabled = true;
                refreshUi();
            });
    }

    function resetPrograms() {
        programSelect.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Pilih program bimbel';
        programSelect.appendChild(option);
        programSelect.disabled = true;
    }

    phoneInput.addEventListener('blur', () => {
        const raw = phoneInput.value.trim();
        if (raw && !raw.startsWith('62')) {
            phoneInput.value = `62${raw.replace(/^0+/, '')}`;
        }

        refreshUi();
    });

    loadProvinces();

    function loadProvinces() {
        fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json')
            .then((response) => response.json())
            .then((provinces) => {
                fillSelect(provinceSelect, provinces, 'Pilih provinsi');
                refreshUi();
            })
            .catch(() => {
                fillSelect(provinceSelect, [], 'Gagal memuat provinsi');
                refreshUi();
            });
    }

    provinceSelect.addEventListener('change', () => {
        const provinceId = provinceSelect.value;
        fillSelect(citySelect, [], 'Memuat kota/kabupaten...');
        fillSelect(districtSelect, [], 'Pilih kecamatan');
        fillSelect(subdistrictSelect, [], 'Pilih kelurahan');
        postalCodeInput.value = '';

        refreshUi();

        if (!provinceId) {
            return;
        }

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinceId}.json`)
            .then((response) => response.json())
            .then((cities) => {
                fillSelect(citySelect, cities, 'Pilih kota/kabupaten');
                refreshUi();
            })
            .catch(() => {
                fillSelect(citySelect, [], 'Gagal memuat kota/kabupaten');
                refreshUi();
            });
    });

    citySelect.addEventListener('change', () => {
        const cityId = citySelect.value;
        fillSelect(districtSelect, [], 'Memuat kecamatan...');
        fillSelect(subdistrictSelect, [], 'Pilih kelurahan');
        postalCodeInput.value = '';

        refreshUi();

        if (!cityId) {
            return;
        }

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${cityId}.json`)
            .then((response) => response.json())
            .then((districts) => {
                fillSelect(districtSelect, districts, 'Pilih kecamatan');
                refreshUi();
            })
            .catch(() => {
                fillSelect(districtSelect, [], 'Gagal memuat kecamatan');
                refreshUi();
            });
    });

    districtSelect.addEventListener('change', () => {
        const districtId = districtSelect.value;
        fillSelect(subdistrictSelect, [], 'Memuat kelurahan...');
        postalCodeInput.value = '';

        refreshUi();

        if (!districtId) {
            return;
        }

        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`)
            .then((response) => response.json())
            .then((villages) => {
                fillSelect(subdistrictSelect, villages, 'Pilih kelurahan', true);
                refreshUi();
            })
            .catch(() => {
                fillSelect(subdistrictSelect, [], 'Gagal memuat kelurahan');
                refreshUi();
            });
    });

    subdistrictSelect.addEventListener('change', () => {
        const selected = subdistrictSelect.selectedOptions[0];
        if (selected) {
            postalCodeInput.value = selected.dataset.postal ?? '';
            if (!postalCodeInput.value) {
                searchPostalCode(
                    selected.textContent,
                    getSelectedText(districtSelect),
                    getSelectedText(citySelect)
                );
            }
        }

        refreshUi();
    });

    function fillSelect(select, items, placeholder, includePostal = false) {
        select.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        select.appendChild(option);

        items.forEach((item) => {
            const opt = document.createElement('option');
            opt.value = item.id ?? item.code;
            opt.textContent = item.name;
            if (includePostal && item.postal_code) {
                opt.dataset.postal = item.postal_code;
            }
            select.appendChild(opt);
        });

        select.disabled = items.length === 0;
    }

    function searchPostalCode(subdistrictName, districtName, cityName) {
        if (!subdistrictName) {
            return;
        }

        const queryParts = [subdistrictName, districtName, cityName].filter(Boolean).join(' ');
        const url = `https://kodepos.vercel.app/search?q=${encodeURIComponent(queryParts)}`;

        fetch(url)
            .then((response) => response.json())
            .then((payload) => {
                const collection = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload?.data)
                    ? payload.data
                    : [];
                const postal = collection.length ? collection[0]?.postalcode : '';
                if (postal) {
                    postalCodeInput.value = postal;
                    refreshUi();
                }
            })
            .catch(() => {
                // Silence network errors, user can fill manually.
            });
    }

    programSelect.addEventListener('change', refreshUi);
    form.addEventListener('input', refreshUi);
    form.addEventListener('change', refreshUi);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        hideAlert(successAlert);
        hideAlert(errorAlert);

        const payload = {
            full_name: form.full_name.value.trim(),
            school_id: schoolIdInput.value,
            school_name: schoolNameInput.value.trim(),
            class_level: classSelect.value,
            phone_number: phoneInput.value.trim(),
            province: provinceSelect.selectedOptions[0]?.textContent ?? '',
            city: citySelect.selectedOptions[0]?.textContent ?? '',
            district: districtSelect.selectedOptions[0]?.textContent ?? '',
            subdistrict: subdistrictSelect.selectedOptions[0]?.textContent ?? '',
            postal_code: postalCodeInput.value.trim(),
            address_detail: form.address_detail.value.trim(),
            program_id: programSelect.value,
        };

        fetch(withBase('/api/registrations'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload),
        })
            .then(async (response) => {
                const data = await response.json();

                if (!response.ok) {
                    const messages = formatErrors(data.errors ?? {});
                    showAlert(errorAlert, messages);
                    return;
                }

                form.reset();
                tomSelect.clear();
                resetPrograms();
                updateClassLevels(null);
                postalCodeInput.value = '';
                fillSelect(citySelect, [], 'Pilih provinsi terlebih dahulu');
                fillSelect(districtSelect, [], 'Pilih kota/kabupaten terlebih dahulu');
                fillSelect(subdistrictSelect, [], 'Pilih kecamatan terlebih dahulu');
                showAlert(successAlert, data.message ?? 'Pendaftaran berhasil.');
                refreshUi();
            })
            .catch(() => {
                showAlert(errorAlert, 'Terjadi kesalahan jaringan. Silakan coba lagi.');
            });
    });

    function formatErrors(errors) {
        return Object.values(errors)
            .flat()
            .join('<br>');
    }

    function showAlert(element, message) {
        element.innerHTML = message;
        element.style.display = 'block';
    }

    function hideAlert(element) {
        element.style.display = 'none';
        element.innerHTML = '';
    }

    function refreshUi() {
        refreshSummary();
        updateProgress();
    }

    function refreshSummary() {
        setSummary('full_name', form.full_name.value.trim());
        setSummary('school_name', schoolNameInput.value.trim());
        setSummary('class_level', classSelect.value);
        setSummary('phone_number', formatPhone(phoneInput.value.trim()));
        setSummary('program', getSelectedText(programSelect));

        const addressParts = [
            getSelectedText(provinceSelect),
            getSelectedText(citySelect),
            getSelectedText(districtSelect),
            getSelectedText(subdistrictSelect),
        ].filter(Boolean);

        const postal = postalCodeInput.value.trim();
        const address = addressParts.length ? `${addressParts.join(', ')}${postal ? ` (${postal})` : ''}` : '';
        setSummary('address', address);
    }

    function setSummary(key, value) {
        const element = summaryElements[key];
        if (!element) {
            return;
        }

        element.textContent = value || '-';
    }

    function getSelectedText(select) {
        return select?.selectedOptions?.[0]?.value ? select.selectedOptions[0].textContent.trim() : '';
    }

    function updateProgress() {
        let firstIncomplete = null;

        steps.forEach((step) => {
            const key = step.dataset.step;
            const complete = isSectionComplete(key);

            step.classList.toggle('is-complete', complete);
            step.classList.remove('is-active');

            if (!complete && !firstIncomplete) {
                firstIncomplete = step;
            }
        });

        const activeStep = firstIncomplete ?? steps[steps.length - 1];
        if (activeStep) {
            activeStep.classList.add('is-active');
        }
    }

    function isSectionComplete(section) {
        switch (section) {
            case 'identity':
                return (
                    form.full_name.value.trim().length >= 3 &&
                    schoolNameInput.value.trim() !== '' &&
                    classSelect.value !== '' &&
                    /^62\d{9,13}$/.test(phoneInput.value.trim())
                );
            case 'address':
                return (
                    provinceSelect.value !== '' &&
                    citySelect.value !== '' &&
                    districtSelect.value !== '' &&
                    subdistrictSelect.value !== '' &&
                    postalCodeInput.value.trim() !== ''
                );
            case 'program':
                return programSelect.value !== '';
            default:
                return false;
        }
    }

    function formatPhone(value) {
        if (!value) {
            return '';
        }

        const sanitized = value.replace(/\D/g, '');
        if (!sanitized) {
            return value;
        }

        return sanitized.replace(/(\d{2})(\d{3,4})(\d{3,4})(\d{0,4})/, (_, a, b, c, d) =>
            [a, b, c, d].filter(Boolean).join(' ')
        );
    }
});
