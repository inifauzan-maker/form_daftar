# SISTEM INFORMASI VILLAMERAH - KONTEN MARKETING

Aplikasi manajemen konten marketing untuk media sosial dan website yang dilengkapi dengan sistem distribusi dan alat untuk mengukur performa konten.

## 🚀 Fitur Utama

### 📊 Dashboard & Analytics
- **Ringkasan Konten**: Tracking konten di setiap tahap (Perencanaan, Pembuatan, Persetujuan, Jadwal Publish)
- **Analytics Dashboard**: Analisis performa konten dengan metrics lengkap
- **Chart & Visualisasi**: Dashboard dengan Chart.js untuk visualisasi data

### 📅 Manajemen Konten
- **Kalender Konten**: Tampilan kalender bulanan/mingguan dengan color-coding status
- **Arsip Konten**: Tabel filterable/searchable untuk konten published
- **CRUD Konten**: Create, Read, Update, Delete konten marketing

### 🔍 Riset & AI Features
- **Content Research**: Integrasi API Instagram dan TikTok untuk riset konten
- **AI Content Generator**: Auto generate caption & hashtag
- **Anti Duplicate**: Deteksi konten duplikat otomatis
- **Trend Analysis**: Analisis hashtag dan topik trending

### 👥 User Management & RBAC
- **Role-Based Access Control**: 6 role berbeda dengan permission khusus
- **User Management**: Interface untuk mengelola user dan role
- **Activity Tracking**: Log aktivitas dan last login user

### 🎨 Design & UX
- **Minimalist Design**: Clean, data-centric interface
- **Navy & Yellow Theme**: Skema warna profesional
- **Responsive Layout**: Optimized untuk desktop dan mobile
- **Collapsible Sidebar**: Navigation yang flexible

## 🛠 Tech Stack

### Backend
- **Framework**: Laravel 12
- **Database**: MySQL/SQLite
- **Authentication**: Laravel built-in auth
- **API Integration**: Guzzle HTTP Client

### Frontend
- **Framework**: Vue.js 3
- **CSS Framework**: Tailwind CSS 4
- **Charts**: Chart.js + Vue-ChartJS
- **UI Components**: Headless UI + Heroicons
- **Build Tool**: Vite

## 🚀 Installation & Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Setup
```bash
php artisan migrate:fresh --seed
```

### 4. Build Assets & Start Server
```bash
npm run build
php artisan serve
```

## 👤 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Kadiv Marketing | admin@villamerah.com | password123 |
| Social Media Specialist | socmed@villamerah.com | password123 |
| Content Creator | creator@villamerah.com | password123 |
| Ads Specialist | ads@villamerah.com | password123 |
| Data Analyst | analyst@villamerah.com | password123 |

## 📊 User Roles & Permissions

### Kadiv Marketing
- Full access ke semua fitur
- Approve konten
- Manage users
- View all analytics & reports

### Social Media Specialist
- Manage konten social media
- Create & edit konten
- Publish konten
- View analytics untuk konten sendiri

### Content Creator
- Create & edit konten
- Upload media files
- Submit untuk approval
- Basic analytics access

### Ads Specialist
- Manage iklan berbayar
- View campaign analytics
- Access to ROI reports

### Sales Team & Data Analyst
- View analytics dan reports
- Export data capabilities

## 📱 Fitur Lengkap

✅ **RBAC (Role-Based Access Control)**
✅ **Notifikasi & Pengumuman** 
✅ **Pencarian Global**
✅ **Integrasi API Instagram dan TikTok**
✅ **CRUD Operations**
✅ **User Profile & Status Aktif**
✅ **Pengaturan Aplikasi**
✅ **Laporan PDF/Excel**
✅ **AI Features (Caption & Hashtag Generator)**
✅ **Live Chat System**
✅ **Backup & Restore**

## 🎯 System Architecture

- **MVC Pattern**: Clean separation of concerns
- **API-First Design**: RESTful API endpoints
- **Component-Based UI**: Reusable Vue.js components
- **Database Optimization**: Proper indexing and relationships
- **Caching Strategy**: Performance optimization
- **Security Measures**: CSRF, XSS protection, input validation

---

**Dikembangkan dengan ❤️ oleh Tim Villa Merah menggunakan Laravel & Vue.js**

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
