
# Backend (Laravel 11) — Catatan Scaffolding
1. `composer create-project laravel/laravel crm-api`
2. Paket disarankan:
   - spatie/laravel-permission (RBAC)
   - laravel/sanctum (auth SPA)
   - maatwebsite/excel (export/import XLSX)
   - laravel-notification-channels (opsional WhatsApp/SMS/email)
3. Model inti: User, Role/Permission, Lead, LeadActivity, Program, Channel, KPI(Target/Realization), AdsPerformance, Todo, CalendarEvent, News, Notification.
4. Job & Scheduler:
   - `php artisan make:command SendFollowupReminders`
   - Schedule tiap 10 menit cek `lead_activities.due_at` & `is_done=0` → kirim notifikasi.
5. Enkripsi no WA: gunakan cast `encrypted` di model User/Lead.
6. Webhook WhatsApp: endpoint `/whatsapp/webhook` simpan ke `whatsapp_messages` dan kaitkan ke lead by nomor.
7. Export/Import:
   - Import Leads: validasi kolom, normalisasi channel, assignment owner via email.
   - Export: gunakan queue untuk dataset besar.
