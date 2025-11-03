# SI VMI Pendaftaran

Formulir pendaftaran bimbingan belajar berbasis PHP (mini MVC) dengan integrasi Tom Select dan API alamat Indonesia.

## Arsitektur Singkat
- `public/` sebagai document root (gunakan `public/index.php`).
- `app/Core` menyimpan kelas inti (Router, Request, Response, Database, View).
- `app/Controllers` menyimpan controller web serta API (`/api/schools`, `/api/programs`, `/api/registrations`, `/dashboard`).
- `app/Models` menyimpan akses database berbasis PDO.
- `app/Views` menyimpan template (form pendaftaran & dashboard).
- `database/schema.sql` berisi struktur dan seed MySQL (`si.vmi_pendaftaran`).

## Prasyarat
- PHP 8.1+ dengan ekstensi PDO MySQL aktif.
- MySQL 5.7+ / MariaDB 10.4+.
- Composer opsional (struktur autoloader manual, tidak wajib).
- Server web (Laragon, XAMPP, dsb.) yang mengarah ke `public/`.

## Langkah Instalasi
1. Salin `.env.example` menjadi `.env` lalu sesuaikan kredensial database.
2. Import `database/schema.sql` ke MySQL:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. Arahkan virtual host / document root ke folder `public/`.
4. Buka `http://si-vmi.test` (atau host lokal Anda) untuk melihat form.

## Alur Fitur
- **Asal Sekolah** memakai Tom Select dengan auto suggest berbasis AJAX (`/api/schools`). Dataset favorit Bandung & Jabodetabek tersimpan di tabel `schools`.
- **Kelas** akan aktif setelah sekolah dipilih, menyesuaikan `level_group` (SD, SMP, SMA).
- **Program Bimbel** dimuat via `/api/programs?classLevel=...` dan menampilkan kode sesuai requirement.
- **Alamat** menggunakan API publik `emsifa.com` untuk memuat provinsi > kota/kabupaten > kecamatan > kelurahan dan mengisi kode pos otomatis (fallback ke `kodepos.vercel.app`).
- **Nomor HP** divalidasi agar diawali `62` dengan 11-15 digit.
- Submisi form dikirim sebagai JSON ke `/api/registrations` dan disimpan di tabel `registrations` dengan status awal `student_status=pending` dan `payment_status=unpaid`.
- **Dashboard Admin** tersedia di `/dashboard` untuk memantau data pendaftar, memperbarui status siswa, serta proses pembayaran melalui endpoint `GET /api/registrations` dan `POST /api/registrations/status`.
- Dari dashboard, admin dapat mengekspor CSV (`/dashboard/export`) atau mengunduh invoice PDF per pendaftar (`/dashboard/invoice?id=ID`).

## Catatan Pengembangan
- Untuk menjalankan dalam mode debug, set `APP_DEBUG=true` di `.env`.
- Validasi server-side mengembalikan HTTP 422 dengan daftar error (JSON).
- Semua respons API menggunakan JSON UTF-8.
- Jika ingin menambah sekolah/program, silakan masukkan data baru ke tabel terkait.
