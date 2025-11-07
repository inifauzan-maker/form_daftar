document.addEventListener('DOMContentLoaded', () => {
    const mapContainer = document.getElementById('registrant-map');
    if (!mapContainer) {
        return;
    }

    const BASE_PATH = window.APP_BASE_PATH || '';
    const withBase = (path) => `${BASE_PATH}${path}`;

    const provinceSelect = document.getElementById('map-filter-province');
    const citySelect = document.getElementById('map-filter-city');
    const summaryContainer = document.getElementById('map-summary');

    const mapState = {
        map: null,
        layer: null,
        loading: false,
    };

    initMap();
    bindEvents();
    loadGeography();

    function initMap() {
        if (!window.L) {
            if (summaryContainer) {
                summaryContainer.textContent = 'Library peta tidak tersedia.';
            }
            return;
        }

        mapState.map = L.map(mapContainer, {
            center: [-2.5489, 118.0149],
            zoom: 5,
            scrollWheelZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            minZoom: 3,
        }).addTo(mapState.map);

        mapState.layer = L.layerGroup().addTo(mapState.map);
    }

    function bindEvents() {
        if (provinceSelect) {
            provinceSelect.addEventListener('change', () => {
                if (citySelect) {
                    citySelect.value = '';
                }
                loadGeography(true);
            });
        }

        if (citySelect) {
            citySelect.addEventListener('change', () => {
                loadGeography(false);
            });
        }
    }

    function loadGeography(resetCity = false) {
        if (mapState.loading) {
            return;
        }

        mapState.loading = true;
        setFiltersDisabled(true);

        const params = new URLSearchParams();
        if (provinceSelect && provinceSelect.value) {
            params.append('province', provinceSelect.value);
        }
        if (!resetCity && citySelect && citySelect.value) {
            params.append('city', citySelect.value);
        }

        const query = params.toString() ? `?${params}` : '';

        fetch(withBase(`/api/dashboard/geography${query}`))
            .then(async (response) => {
                const payload = await response.json();
                if (!response.ok) {
                    throw new Error('Failed to load map data');
                }

                updateMapFilters(payload.filters);
                renderMarkers(payload.markers || []);
                renderMapSummary(payload.markers || []);
            })
            .catch(() => {
                showToast('Gagal memuat peta sebaran.', 'error');
            })
            .finally(() => {
                mapState.loading = false;
                setFiltersDisabled(false);
            });
    }

    function updateMapFilters(filtersPayload) {
        if (!filtersPayload) {
            return;
        }

        const selected = filtersPayload.selected || {};
        const options = filtersPayload.options || {};

        populateSelect(provinceSelect, options.provinces || [], 'Semua Provinsi');
        setSelectValue(provinceSelect, selected.province || '');

        populateSelect(citySelect, options.cities || [], 'Semua Kabupaten/Kota');
        if (options.cities && options.cities.length) {
            citySelect.disabled = false;
        } else if (citySelect) {
            citySelect.disabled = true;
            citySelect.value = '';
        }
        setSelectValue(citySelect, selected.city || '');
    }

    function populateSelect(select, items, placeholder) {
        if (!select) {
            return;
        }

        const currentValue = select.value;
        select.innerHTML = '';

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = placeholder;
        select.appendChild(placeholderOption);

        items.forEach((item) => {
            const option = document.createElement('option');
            if (typeof item === 'string') {
                option.value = item;
                option.textContent = item;
            } else {
                option.value = item.value ?? '';
                option.textContent = item.label ?? item.value ?? '';
            }
            select.appendChild(option);
        });

        if (items.some((item) => (typeof item === 'string' ? item : item.value) === currentValue)) {
            select.value = currentValue;
        }
    }

    function setSelectValue(select, value) {
        if (!select) {
            return;
        }

        select.value = value ?? '';
    }

    function renderMarkers(rows) {
        if (!mapState.layer) {
            return;
        }

        mapState.layer.clearLayers();
        const bounds = [];

        rows.forEach((row) => {
            const coords = row.coordinates || {};
            const lat = Number(coords.lat);
            const lng = Number(coords.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return;
            }

            const marker = L.circleMarker([lat, lng], {
                radius: Math.min(20, 6 + row.total),
                color: '#2563eb',
                fillColor: '#2563eb',
                fillOpacity: 0.65,
                weight: 1,
            });

            marker.bindPopup(
                `<strong>${escapeHtml(row.city || row.province || 'Tidak diketahui')}</strong><br>
                Total: ${formatNumber(row.total)} siswa<br>
                Lunas: ${formatNumber(row.paid)} | Cicilan: ${formatNumber(row.partial)} | Belum bayar: ${formatNumber(row.unpaid)}`
            );

            marker.addTo(mapState.layer);
            bounds.push([lat, lng]);
        });

        if (mapState.map && bounds.length) {
            mapState.map.fitBounds(bounds, { padding: [30, 30] });
        } else if (mapState.map) {
            mapState.map.setView([-2.5489, 118.0149], 5);
        }
    }

    function renderMapSummary(rows) {
        if (!summaryContainer) {
            return;
        }

        if (!rows.length) {
            summaryContainer.innerHTML = '<p class="map-summary__empty">Belum ada data pendaftar untuk filter ini.</p>';
            return;
        }

        const fragments = rows
            .map((row) => {
                const label = row.city || row.province || 'Tidak diketahui';
                return `
                    <article class="map-summary__item">
                        <header>
                            <strong>${escapeHtml(label)}</strong>
                            <span>${escapeHtml(row.province || '')}</span>
                        </header>
                        <div class="map-summary__figures">
                            <div>
                                <span>Total</span>
                                <strong>${formatNumber(row.total)}</strong>
                            </div>
                            <div>
                                <span>Lunas</span>
                                <strong>${formatNumber(row.paid)}</strong>
                            </div>
                            <div>
                                <span>Cicilan</span>
                                <strong>${formatNumber(row.partial)}</strong>
                            </div>
                            <div>
                                <span>Belum</span>
                                <strong>${formatNumber(row.unpaid)}</strong>
                            </div>
                        </div>
                    </article>
                `;
            })
            .join('');

        summaryContainer.innerHTML = fragments;
    }

    function setFiltersDisabled(isDisabled) {
        if (provinceSelect) {
            provinceSelect.disabled = isDisabled;
        }
        if (citySelect) {
            citySelect.disabled = isDisabled || !citySelect.options.length;
        }
    }

    function formatNumber(value) {
        return numberFormatter.format(Number(value) || 0);
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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
});
