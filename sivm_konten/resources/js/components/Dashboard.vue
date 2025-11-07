<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-navy-900 text-white shadow-lg">
      <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center space-x-4">
          <!-- Logo -->
          <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center">
            <span class="text-navy-900 font-bold text-lg">VM</span>
          </div>
          <h1 class="text-xl font-semibold">SISTEM INFORMASI VILLAMERAH – KONTEN MARKETING</h1>
        </div>
        
        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <span class="text-sm">{{ currentUser.name }}</span>
          <button @click="logout" class="text-yellow-400 hover:text-yellow-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
          </button>
        </div>
      </div>
    </header>

    <div class="flex">
      <!-- Sidebar -->
      <aside :class="{ 'w-64': !sidebarCollapsed, 'w-16': sidebarCollapsed }" class="bg-white shadow-lg min-h-screen transition-all duration-300">
        <!-- Sidebar Toggle -->
        <div class="p-4">
          <button @click="toggleSidebar" class="w-full flex justify-center text-navy-900 hover:text-yellow-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-4">
          <div v-for="item in menuItems" :key="item.name" class="px-4 py-2">
            <a 
              @click="setActiveMenu(item.name)"
              :class="{ 'bg-yellow-400 text-navy-900': activeMenu === item.name }"
              class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-yellow-400 hover:text-navy-900 cursor-pointer transition-colors"
            >
              <svg class="w-5 h-5" :class="{ 'text-navy-900': activeMenu === item.name }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
              </svg>
              <span v-if="!sidebarCollapsed" class="font-medium">{{ item.label }}</span>
            </a>
          </div>
        </nav>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-6">
        <!-- Dashboard Overview -->
        <div v-if="activeMenu === 'ringkasan'">
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-navy-900 mb-6">Ringkasan Konten</h2>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                  <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm text-gray-600">Perencanaan</p>
                    <p class="text-2xl font-bold text-navy-900">{{ stats.planning }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                  <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm text-gray-600">Pembuatan</p>
                    <p class="text-2xl font-bold text-navy-900">{{ stats.creation }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-400">
                <div class="flex items-center">
                  <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm text-gray-600">Persetujuan</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ stats.approval }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center">
                  <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm text-gray-600">Jadwal Publish</p>
                    <p class="text-2xl font-bold text-navy-900">{{ stats.scheduled }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-navy-900 mb-4">Konten per Platform</h3>
                <canvas ref="platformChart"></canvas>
              </div>

              <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-lg font-semibold text-navy-900 mb-4">Trend Konten Bulanan</h3>
                <canvas ref="trendChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Riset Content -->
        <div v-else-if="activeMenu === 'riset'">
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-navy-900 mb-6">Riset Konten</h2>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="col-span-2">
                  <h3 class="text-lg font-semibold text-navy-900 mb-4">Referensi Konten</h3>
                  <div class="space-y-4">
                    <div class="p-4 border border-gray-200 rounded-lg">
                      <p class="text-sm text-gray-600">Trend Hashtag Instagram</p>
                      <p class="font-medium">#villamerah #properti #investasi</p>
                    </div>
                    <div class="p-4 border border-gray-200 rounded-lg">
                      <p class="text-sm text-gray-600">Konten TikTok Viral</p>
                      <p class="font-medium">Tips investasi properti untuk pemula</p>
                    </div>
                  </div>
                </div>
                
                <div>
                  <h3 class="text-lg font-semibold text-navy-900 mb-4">AI Assistant</h3>
                  <button class="w-full bg-yellow-400 text-navy-900 px-4 py-2 rounded-lg font-medium hover:bg-yellow-500">
                    Generate Caption
                  </button>
                  <button class="w-full mt-2 bg-navy-900 text-white px-4 py-2 rounded-lg font-medium hover:bg-navy-800">
                    Generate Hashtags
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Other menu content will be loaded dynamically -->
        <content-calendar v-else-if="activeMenu === 'kalender'" />
        <content-archive v-else-if="activeMenu === 'arsip'" />
        <content-analytics v-else-if="activeMenu === 'analitik'" />
        <user-management v-else-if="activeMenu === 'user'" />

        <!-- Monitoring Ads API Key Input -->
        <div v-else-if="activeMenu === 'monitoring'">
          <div class="max-w-xl mx-auto bg-white rounded-lg shadow-lg p-8 mt-8">
            <h2 class="text-2xl font-bold text-navy-900 mb-6">Monitoring Ads - API Key</h2>
            <form @submit.prevent="saveApiKeys" class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meta Ads API Key</label>
                <input v-model="apiKeys.meta" type="text" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-yellow-400" placeholder="Masukkan API Key Meta Ads">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TikTok Ads API Key</label>
                <input v-model="apiKeys.tiktok" type="text" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-yellow-400" placeholder="Masukkan API Key TikTok Ads">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Google Ads API Key</label>
                <input v-model="apiKeys.google" type="text" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-yellow-400" placeholder="Masukkan API Key Google Ads">
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-navy-900 text-white px-6 py-2 rounded hover:bg-navy-800">Simpan API Key</button>
              </div>
              <div v-if="apiKeySaved" class="mt-4 text-green-600 font-semibold">API Key berhasil disimpan!</div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'Dashboard',
  data() {
    return {
      sidebarCollapsed: false,
      activeMenu: 'ringkasan',
      menuItems: [
        {
          name: 'ringkasan',
          label: 'Ringkasan',
          icon: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2H3z'
        },
        {
          name: 'riset',
          label: 'Riset',
          icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'
        },
        {
          name: 'kalender',
          label: 'Kalender Konten',
          icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
        },
        {
          name: 'arsip',
          label: 'Arsip Konten',
          icon: 'M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293L8 5.586A1 1 0 008.586 6H19a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8z'
        },
        {
          name: 'analitik',
          label: 'Analitik Konten',
          icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
        },
        {
          name: 'monitoring',
          label: 'Monitoring Ads',
          icon: 'M13 16h-1v-4h-1m4 0h-1v4h-1m-4 0h1v-4h1m-4 0h1v4h1M4 6h16M4 6a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2H4z'
        },
        {
          name: 'user',
          label: 'Manajemen User',
          icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
        }
      ],
      apiKeys: {
        meta: '',
        tiktok: '',
        google: ''
      },
      apiKeySaved: false
    };
  },
  methods: {
    saveApiKeys() {
      // Simpan API Key ke backend (implementasi backend diperlukan)
      // Sementara, tampilkan notifikasi sukses
      this.apiKeySaved = true;
      setTimeout(() => { this.apiKeySaved = false }, 2000);
    },
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed;
    },
    setActiveMenu(menu) {
      this.activeMenu = menu;
    },
    logout() {
      // Handle logout
      window.location.href = '/logout';
    },
    initCharts() {
      // Platform Chart
      const platformCtx = this.$refs.platformChart?.getContext('2d');
      if (platformCtx) {
        new Chart(platformCtx, {
          type: 'doughnut',
          data: {
            labels: ['Instagram', 'TikTok', 'Website'],
            datasets: [{
              data: [45, 35, 20],
              backgroundColor: ['#1e3a8a', '#f59e0b', '#6b7280'],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                position: 'bottom'
              }
            }
          }
        });
      }

      // Trend Chart
      const trendCtx = this.$refs.trendChart?.getContext('2d');
      if (trendCtx) {
        new Chart(trendCtx, {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
              label: 'Konten Published',
              data: [12, 19, 15, 25, 22, 30],
              borderColor: '#1e3a8a',
              backgroundColor: 'rgba(30, 58, 138, 0.1)',
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: false
              }
            },
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });
      }
    }
  }
}
</script>

<style scoped>
.bg-navy-900 {
  background-color: #1e3a8a;
}

.text-navy-900 {
  color: #1e3a8a;
}
</style>