# Pendaftaran

Formulir pendaftaran bimbingan belajar berbasis PHP (mini MVC) dengan integrasi Tom Select dan API alamat Indonesia.

## Arsitektur Singkat
- `public/` sebagai document root (gunakan `public/index.php`).
- `app/Core` menyimpan kelas inti (Router, Request, Response, Database, View).
- `app/Controllers` menyimpan controller web serta API (`/api/schools`, `/api/programs`, `/api/registrations`, `/dashboard`) serta modul RBAC (`AuthController`, `UserController`, `RoleController`, `PermissionController`).
- `app/Models` menyimpan akses database berbasis PDO.
- `app/Views` menyimpan template (form pendaftaran, dashboard, login, manajemen pengguna, halaman error 403).
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
4. Buka `http://si-vmi.test` (atau host lokal Anda) untuk melihat form publik.
5. Masuk ke panel admin melalui `http://si-vmi.test/login` menggunakan akun bawaan
  

## Alur Fitur
- **Asal Sekolah** memakai Tom Select dengan auto suggest berbasis AJAX (`/api/schools`). Dataset favorit Bandung & Jabodetabek tersimpan di tabel `schools`.
- **Kelas** akan aktif setelah sekolah dipilih, menyesuaikan `level_group` (SD, SMP, SMA).
- **Program Bimbel** dimuat via `/api/programs?classLevel=...` dan menampilkan kode sesuai requirement.
- **Alamat** menggunakan API publik `emsifa.com` untuk memuat provinsi > kota/kabupaten > kecamatan > kelurahan dan mengisi kode pos otomatis (fallback ke `kodepos.vercel.app`).
- **Nomor HP** divalidasi agar diawali `62` dengan 11-15 digit.
- Submisi form dikirim sebagai JSON ke `/api/registrations` dan disimpan di tabel `registrations` dengan status awal `student_status=pending` dan `payment_status=unpaid`.
- **Dashboard Admin** tersedia di `/dashboard` untuk memantau data pendaftar, memperbarui status siswa, serta proses pembayaran melalui endpoint `GET /api/registrations` dan `POST /api/registrations/status`.
- Dari dashboard, admin dapat mengekspor CSV (`/dashboard/export`) atau mengunduh invoice PDF per pendaftar (`/dashboard/invoice?id=ID`).
- **Program Bimbel** tersedia di menu baru `/programs` untuk mencatat biaya pendaftaran, biaya pendidikan, target siswa, target omzet, serta poster program. Data ini bisa diunggah beserta gambar sehingga mengikuti spreadsheet peraihan internal.

## Dummy Data
Butuh contoh data siswa dari provinsi Jawa Barat dan DKI Jakarta dengan status pembayaran lunas/cicil? Impor skrip berikut setelah `schema.sql`:

```bash
mysql -u root -p si.vmi_pendaftaran < database/dummy_registrations.sql
```

Berkas tersebut menambahkan 6 pendaftar fiktif (Bandung, Cimahi, Jakarta Selatan, Jakarta Timur) yang tersebar pada beberapa program bimbel. Sesuaikan kode program pada skrip agar sesuai dengan data di tabel `programs`.

## Autentikasi & RBAC
- Akses ke dashboard, invoice, dan API administrasi kini dilindungi login. Pengunjung publik tetap dapat mengisi formulir pendaftaran tanpa autentikasi.
- Sistem role-based access control (RBAC) terdiri dari tabel `users`, `roles`, `permissions`, `role_user`, `permission_role`, dan `permission_user`.
- Tiga peran awal disediakan: `Administrator` (akses penuh), `Staff` (kelola pendaftar & pembayaran), `Viewer` (hanya lihat dashboard). Admin bawaan otomatis diberi peran `Administrator`.
- Halaman `/users` menampilkan UI manajemen pengguna, peran, dan izin:
  - Tambah / ubah / nonaktifkan pengguna, sekaligus atur peran dan izin langsung.
  - Tambah / ubah / hapus peran beserta daftar izinnya (khusus pengguna dengan izin `manage_roles`).
  - Tambah / ubah / hapus izin granular (khusus `manage_permissions`).
- Setiap perubahan peran/izin langsung sinkron dengan sesi pengguna aktif yang sedang diedit.
- Ekspor CSV, pembaruan status pendaftar, serta akses invoice hanya tampil bila pengguna memiliki izin terkait.

## Catatan Pengembangan
- Untuk menjalankan dalam mode debug, set `APP_DEBUG=true` di `.env`.
- Validasi server-side mengembalikan HTTP 422 dengan daftar error (JSON).
- Semua respons API menggunakan JSON UTF-8.
- Jika ingin menambah sekolah/program, silakan masukkan data baru ke tabel terkait. Tabel `programs` kini menyimpan kolom biaya & target (`registration_fee`, `tuition_fee`, `target_students`, `target_revenue`, `description`, `image_path`) sehingga menu Program Bimbel bisa langsung menggunakan data yang sama.
- Kredensial admin bawaan dapat diubah dari halaman `/users` atau langsung pada tabel `users`. Pastikan mengganti kata sandi default di lingkungan produksi.
- Untuk menambahkan izin baru, cukup sisipkan baris ke tabel `permissions` lalu kaitkan ke peran melalui UI atau tabel pivot.
