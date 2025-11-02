<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName ?? 'Form Pendaftaran', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset('assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <div class="page">
        <header class="hero">
            <div class="hero__tagline">
                <span class="badge">SI VMI</span>
                <h1>Formulir Pendaftaran</h1>
                <p>Isi data dengan lengkap untuk mengamankan kursimu di program unggulan Kami. Kami bantu memilih program terbaik sesuai jenjang belajar.</p>
            </div>
            
        </header>

        <main class="layout">
            <section class="card form-card">
                <div class="stepper" id="form-progress">
                    <div class="step" data-step="identity">
                        <div class="step__icon">1</div>
                        <div>
                            <h3>Data Diri</h3>
                            <p>Nama, sekolah, dan kontak.</p>
                        </div>
                    </div>
                    <div class="step" data-step="address">
                        <div class="step__icon">2</div>
                        <div>
                            <h3>Alamat</h3>
                            <p>Lengkapi domisili.</p>
                        </div>
                    </div>
                    <div class="step" data-step="program">
                        <div class="step__icon">3</div>
                        <div>
                            <h3>Program</h3>
                            <p>Pilih jalur belajar.</p>
                        </div>
                    </div>
                </div>

                <div id="alert-success" class="alert alert-success"></div>
                <div id="alert-error" class="alert alert-error"></div>

                <form id="registration-form" class="form" autocomplete="off">
                    <section class="form-section" data-section="identity">
                        <div class="section-header">
                            <h2>Data Diri</h2>
                            <p>Kami akan menggunakan data ini untuk proses onboarding bimbingan.</p>
                        </div>
                        <div class="form-grid">
                            <label class="input-field">
                                <span>Nama Lengkap</span>
                                <input type="text" id="full-name" name="full_name" placeholder="Nama lengkap sesuai KTP" required>
                            </label>
                            <label class="input-field full">
                                <span>Asal Sekolah</span>
                                <input type="text" id="school-selector" placeholder="Contoh: SMAN 3 Bandung" required>
                                <input type="hidden" id="school-id" name="school_id">
                                <input type="hidden" id="school-name" name="school_name">
                                <small class="text-muted">Ketik SMAN/SMAS/SMK/MA untuk memunculkan daftar sekolah favorit Bandung dan Jabodetabek.</small>
                            </label>
                            <label class="input-field">
                                <span>Kelas</span>
                                <select id="class-level" name="class_level" required disabled>
                                    <option value="">Pilih kelas</option>
                                </select>
                            </label>
                            <label class="input-field">
                                <span>Nomor HP (diawali 62)</span>
                                <input type="tel" id="phone-number" name="phone_number" placeholder="62xxxxxxxxxxx" required>
                            </label>
                        </div>
                    </section>

                    <section class="form-section" data-section="address">
                        <div class="section-header">
                            <h2>Alamat Domisili</h2>
                            <p>isi sesuai dengan domisili kamu </p>
                        </div>
                        <div class="form-grid">
                            <label class="input-field">
                                <span>Provinsi</span>
                                <select id="province" name="province" required>
                                    <option value="">Memuat provinsi...</option>
                                </select>
                            </label>
                            <label class="input-field">
                                <span>Kota / Kabupaten</span>
                                <select id="city" name="city" required disabled>
                                    <option value="">Pilih provinsi terlebih dahulu</option>
                                </select>
                            </label>
                            <label class="input-field">
                                <span>Kecamatan</span>
                                <select id="district" name="district" required disabled>
                                    <option value="">Pilih kota/kabupaten terlebih dahulu</option>
                                </select>
                            </label>
                            <label class="input-field">
                                <span>Kelurahan</span>
                                <select id="subdistrict" name="subdistrict" required disabled>
                                    <option value="">Pilih kecamatan terlebih dahulu</option>
                                </select>
                            </label>
                            <label class="input-field">
                                <span>Kode Pos</span>
                                <input type="text" id="postal-code" name="postal_code" placeholder="Kode pos terisi otomatis" inputmode="numeric" required>
                            </label>
                            <label class="input-field full">
                                <span>Detail Alamat (Opsional)</span>
                                <textarea id="address-detail" name="address_detail" rows="2" placeholder="Nama jalan, nomor rumah, RT/RW"></textarea>
                            </label>
                        </div>
                    </section>

                    <section class="form-section" data-section="program">
                        <div class="section-header">
                            <h2>Pilihan Program</h2>
                            <p>Pilih program yang paling cocok dengan target akademikmu.</p>
                        </div>
                        <label class="input-field full">
                            <span>Program Bimbel</span>
                            <select id="program" name="program_id" required disabled>
                                <option value="">Pilih program sesuai jenjang kelas</option>
                            </select>
                            <small class="text-muted">Program otomatis menyesuaikan jenjang kelas yang dipilih.</small>
                        </label>
                    </section>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Kirim Pendaftaran</button>
                        <p class="form-disclaimer">Dengan mengirim formulir, kamu menyetujui kebijakan privasi SI VMI.</p>
                    </div>
                </form>
            </section>

            <aside class="card sidecard">
                <div class="sidecard__header">
                    <h2>Ringkasan Pendaftaran</h2>
                    <p>Status ter-update sesuai data yang kamu isi.</p>
                </div>
                <ul class="summary-list">
                    <li>
                        <span class="summary-label">Nama</span>
                        <span class="summary-value" data-summary="full_name">-</span>
                    </li>
                    <li>
                        <span class="summary-label">Sekolah</span>
                        <span class="summary-value" data-summary="school_name">-</span>
                    </li>
                    <li>
                        <span class="summary-label">Kelas</span>
                        <span class="summary-value" data-summary="class_level">-</span>
                    </li>
                    <li>
                        <span class="summary-label">Nomor HP</span>
                        <span class="summary-value" data-summary="phone_number">-</span>
                    </li>
                    <li>
                        <span class="summary-label">Domisili</span>
                        <span class="summary-value" data-summary="address">-</span>
                    </li>
                    <li>
                        <span class="summary-label">Program</span>
                        <span class="summary-value" data-summary="program">-</span>
                    </li>
                </ul>
                <div class="sidecard__footer">
                    <h3>Tahap selanjutnya?</h3>
                    <ul class="benefits">
                        <li>Admin akan menghubungimu untuk konfirmasi pendaftaran.</li>
                        <li>Biaya pendaftaran dan pendidikan.</li>
                        <li>No rekening Resmi.</li>
                    </ul>
                </div>
            </aside>
        </main>
    </div>

    <script>
        window.APP_BASE_PATH = '<?= htmlspecialchars(base_path(), ENT_QUOTES, 'UTF-8') ?>';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="<?= htmlspecialchars(asset('assets/js/registration.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
