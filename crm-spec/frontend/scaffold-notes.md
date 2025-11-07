
# Frontend (Vue 3 + Vite + Tailwind + Chart.js)
1. `npm create vite@latest crm-frontend -- --template vue`
2. `npm i pinia vue-router chart.js vue-chartjs @tanstack/vue-table xlsx`
3. Struktur halaman:
   - Layout: Header (judul), Sidebar collapsible, Main 3-panel
   - Views: Dashboard, CRM/Leads (List + Detail), Reporting/KPI, News, Tim Admin, Profil User
4. Tabel:
   - Sorting, multi-filter (by status, stage, channel, admin), column visibility, export XLSX (client-side untuk subset data)
5. Form:
   - vee-validate untuk validasi real-time (no WA, required, email)
6. Chart:
   - Line chart (tren 6 bulan), bar/stacked untuk channel, funnel (library sederhana atau custom)
7. Tema:
   - Navy base, Yellow accent (utility classes Tailwind)
