# 🌐 Panduan Akses Jaringan Lokal - Content Marketing System

## 📋 Overview
Panduan ini menjelaskan cara mengakses aplikasi Content Marketing Management System dari perangkat lain dalam jaringan lokal yang sama.

## 🔍 IP Address Saat Ini
**IP Lokal Server**: `172.16.1.77`

## 🚀 Metode Akses Jaringan Lokal

### 1. Menggunakan Laravel Development Server

#### Langkah 1: Jalankan Server dengan Host 0.0.0.0
```bash
cd C:\laragon\www\sivm_konten
php artisan serve --host=0.0.0.0 --port=8000
```

#### Langkah 2: Akses dari Perangkat Lain
- **URL Akses**: `http://172.16.1.77:8000`
- **Dashboard**: `http://172.16.1.77:8000/konten-marketing`
- **Social Media**: `http://172.16.1.77:8000/social-integration`

### 2. Menggunakan Laragon (Recommended)

#### Langkah 1: Konfigurasi Laragon untuk Network Access
1. Buka Laragon Control Panel
2. Klik kanan pada Laragon tray icon
3. Pilih "Preferences" atau "Settings"
4. Cari opsi "Network Access" atau "Allow External Access"
5. Centang/Enable opsi tersebut
6. Restart Laragon services

#### Langkah 2: Setup Virtual Host di Laragon
1. Buka folder: `C:\laragon\etc\hosts`
2. Edit file `hosts` dan tambahkan:
   ```
   172.16.1.77    sivm-konten.test
   ```

3. Buka folder: `C:\laragon\etc\apache2\sites-enabled`
4. Buat file `sivm-konten.test.conf`:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/laragon/www/sivm_konten/public"
       ServerName sivm-konten.test
       ServerAlias *.sivm-konten.test
       
       <Directory "C:/laragon/www/sivm_konten/public">
           AllowOverride All
           Require all granted
       </Directory>
       
       # Allow access from local network
       <Directory "C:/laragon/www/sivm_konten/public">
           Options Indexes FollowSymLinks MultiViews
           AllowOverride All
           Order allow,deny
           Allow from all
           Require all granted
       </Directory>
   </VirtualHost>
   ```

#### Langkah 3: Update .env untuk Network Access
```env
APP_URL=http://172.16.1.77:8000
# atau
APP_URL=http://sivm-konten.test
```

#### Langkah 4: Akses dari Perangkat Lain
- **URL**: `http://172.16.1.77/sivm_konten/public`
- **Atau**: `http://sivm-konten.test` (jika sudah setup virtual host)

### 3. Menggunakan XAMPP/Manual Apache Setup

#### Jika menggunakan XAMPP atau Apache manual:
1. Edit file `httpd.conf`:
   ```apache
   # Uncomment atau tambahkan line ini:
   Listen 0.0.0.0:80
   
   # Atau untuk port specific:
   Listen 172.16.1.77:80
   ```

2. Restart Apache service
3. Akses via: `http://172.16.1.77/sivm_konten/public`

## 🔧 Konfigurasi Firewall Windows

### Membuka Port untuk Akses Network

#### Menggunakan Windows Firewall GUI:
1. Buka "Windows Defender Firewall with Advanced Security"
2. Klik "Inbound Rules" → "New Rule"
3. Pilih "Port" → Next
4. Pilih "TCP" → Specific ports: `80,8000`
5. Pilih "Allow the connection"
6. Apply ke semua profiles (Domain, Private, Public)
7. Beri nama: "Laravel Development Server"

#### Menggunakan Command Line:
```cmd
# Buka Command Prompt sebagai Administrator
netsh advfirewall firewall add rule name="Laravel Dev Server" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="Apache HTTP" dir=in action=allow protocol=TCP localport=80
```

## 📱 Akses dari Perangkat Mobile

### 1. Pastikan dalam WiFi yang sama
- Server dan mobile device harus terhubung ke WiFi/network yang sama
- Contoh: WiFi "MyHome" atau jaringan kantor yang sama

### 2. Buka browser di mobile
- Ketik: `http://172.16.1.77:8000`
- Atau: `http://172.16.1.77/sivm_konten/public`

### 3. Untuk Social Media OAuth
⚠️ **Penting**: OAuth Instagram dan TikTok memerlukan HTTPS di production.
Untuk testing lokal, gunakan ngrok atau setup SSL certificate.

## 🔍 Troubleshooting

### Masalah Umum dan Solusi:

#### 1. "This site can't be reached"
```bash
# Cek apakah server berjalan
netstat -an | findstr :8000

# Atau cek dengan telnet
telnet 172.16.1.77 8000
```

#### 2. "Connection refused"
- Pastikan firewall tidak memblokir port
- Cek apakah antivirus memblokir koneksi
- Restart router/switch jika perlu

#### 3. "Access Denied" atau 403 Error
- Cek permission folder `public/`
- Pastikan Apache/Laragon berjalan dengan user yang tepat
- Cek konfigurasi virtual host

#### 4. CSS/JS tidak load
- Update `APP_URL` di `.env` dengan IP yang benar
- Jalankan `npm run build` untuk regenerate assets
- Clear browser cache di perangkat client

## 🚀 Quick Commands untuk Development

### Start Server untuk Network Access:
```bash
# Method 1: Laravel Artisan
php artisan serve --host=0.0.0.0 --port=8000

# Method 2: dengan IP specific
php artisan serve --host=172.16.1.77 --port=8000
```

### Cek Network Connectivity:
```bash
# Dari komputer client, test koneksi:
ping 172.16.1.77
telnet 172.16.1.77 8000
```

### Build Assets untuk Production:
```bash
npm run build
php artisan config:cache
php artisan route:cache
```

## 🔒 Security Considerations

### Development Mode:
- Gunakan `APP_DEBUG=true` hanya untuk development
- Set `APP_ENV=local` untuk testing jaringan lokal

### Production Mode (jika deploy):
- Set `APP_DEBUG=false`
- Set `APP_ENV=production`
- Gunakan HTTPS untuk OAuth social media
- Setup proper firewall rules

## 📊 Testing Network Access

### Test Checklist:
- [ ] Server dapat diakses dari `localhost:8000`
- [ ] Server dapat diakses dari `172.16.1.77:8000`
- [ ] Perangkat mobile dapat ping ke `172.16.1.77`
- [ ] Browser mobile dapat buka `http://172.16.1.77:8000`
- [ ] CSS dan JS assets ter-load dengan baik
- [ ] Form submission bekerja normal
- [ ] File upload berfungsi dari perangkat lain

## 🎯 URLs untuk Testing

Setelah setup, test URLs berikut dari perangkat lain:

```
# Main Application
http://172.16.1.77:8000

# Content Marketing Dashboard  
http://172.16.1.77:8000/konten-marketing

# Content Calendar
http://172.16.1.77:8000/content-calendar

# Social Media Integration
http://172.16.1.77:8000/social-integration

# API Test (jika ada)
http://172.16.1.77:8000/api/contents
```

---

**Note**: Ganti `172.16.1.77` dengan IP address aktual dari komputer server Anda jika berbeda.