
# Spesifikasi Teknis — CRM Monitoring Prospek/Leads

## 1. Kerangka Kerja
- **Backend**: PHP (disarankan Laravel 11 sebagai REST API)
  - Alasan: routing rapi, ORM (Eloquent), queue & scheduler (reminder follow-up), notifikasi (mail/FCM), policy/permission.
- **Frontend**: Vue 3 (Vite) + Tailwind CSS, Chart.js
  - State: Pinia
  - Router: Vue Router
  - Komponen tabel: TanStack Table (opsional) atau DataTables via wrapper Vue
- **Database**: MySQL 8.x
- **Auth**: JWT atau Laravel Sanctum
- **RBAC**: Multi-role (Owner, Manager, Admin, Sales/CS)
- **Time zone**: Asia/Jakarta

## 2. Gaya & Visual
- **Estetika**: Minimalis, bersih, data‑centric. Fokus keterbacaan angka & tabel.
- **Skema warna**: Biru tua (Navy) sebagai dasar, aksen Kuning Cerah.
- **Referensi**: Ant Design Dashboard (struktur grid & card)
- **Komponen kunci**:
  - Sidebar collapsible
  - Data table: sort, multi-filter, column visibility, pagination, export
  - Form validasi real‑time (Vuelidate / vee-validate)
  - Header judul: **"CRM – MARKETING"**
  - Main content 3 panel (Cards/Charts/Tables)

## 3. Entitas & Fitur
### 3.1 Reporting & KPI
- Target Omzet & Siswa; perbandingan Target vs Real per bulan
- Peraihan Program Bimbel
- Leads: jumlah, Target vs Real
- Performa Ads: TikTok Ads, Instagram Ads, Meta Ads (FB/IG)
- Grafik garis tren 6 bulan terakhir (Chart.js)

### 3.2 CRM (Leads)
- Funnel grafik: prospek → follow‑up → closing
- Database Leads: nama siswa, asal sekolah, no WA, saluran (channel), admin penanggungjawab, status follow‑up
- Persentase konversi per channel
- Notifikasi aktivitas follow‑up & update terbaru dari leads
- Input & tracking leads, pipeline visual
- Reminder otomatis follow‑up (scheduler + queue)
- Pencatatan aktivitas follow‑up (call/chat/meeting), next action & due date
- Sumber leads (organic/ads/referral/event/etc.)
- Proyeksi closing (forecast) berbasis probabilitas tahap pipeline
- Manajemen & monitoring performa Tim Admin
- Kalender jadwal follow‑up
- To‑do list & pengingat otomatis
- Integrasi WhatsApp (webhook/bridge) — pesan masuk ditautkan ke kontak/leads
- Tim admin (assignment, workload)

### 3.3 News
- Info dunia pendidikan/pengetahuan (CRUD sederhana)

### 3.4 Profil User
- Pengaturan profil, To‑Do, laporan personal

## 4. CRUD
- Seluruh entitas utama: create, read (list/detail), update, delete
- Soft delete (rekomendasi) pada entitas sensitif (leads, activities)

## 5. Export/Import & Laporan
- **Export**: semua data utama ke Excel (.xlsx)
- **Import**: impor massal Produk/Program melalui Excel template
- **Visualisasi laporan**: grafik garis tren 6 bulan terakhir

## 6. Fitur Tambahan
- Multi‑Role (RBAC)
- Notifikasi (in‑app, email, opsional WhatsApp API resmi)
- Scheduler untuk reminder follow‑up

## 7. Arsitektur Tingkat Tinggi
- SPA Vue mengonsumsi REST API (CORS diaktifkan).
- Laravel API + MySQL; job queue (Redis) untuk reminder/notifikasi.
- Webhook receiver untuk integrasi WhatsApp (penyimpanan ke `whatsapp_messages`).

## 8. Keamanan
- HTTPS, rate limiting, CSRF (jika SSR/Inertia), input validation (backend & frontend), audit log.
- Field PII (no WA) disimpan terenkripsi (Laravel cast: encrypted).

## 9. Deployment
- Env: Production/ Staging
- CI/CD: GitHub Actions (build lint test), database migrations, seed minimal.

## 10. Analytics & Pelacakan
- Event log untuk aktivitas user (view/export/import/login).
- UTM channel normalisasi ke tabel `channels`.

