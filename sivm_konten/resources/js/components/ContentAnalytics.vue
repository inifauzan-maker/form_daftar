<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-navy-900 mb-6">Analitik Konten</h2>
      
      <!-- Analytics Overview Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Engagement Rate</p>
              <p class="text-2xl font-bold text-navy-900">{{ overallStats.totalEngagementRate }}%</p>
              <p class="text-xs text-green-600">↗ +2.3% dari bulan lalu</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Views</p>
              <p class="text-2xl font-bold text-navy-900">{{ formatNumber(overallStats.totalViews) }}</p>
              <p class="text-xs text-blue-600">↗ +15.7% dari bulan lalu</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Best Performing Topic</p>
              <p class="text-lg font-bold text-navy-900">Investasi Properti</p>
              <p class="text-xs text-yellow-600">18.5% engagement rate</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Avg. Engagement</p>
              <p class="text-2xl font-bold text-navy-900">{{ overallStats.avgEngagement }}%</p>
              <p class="text-xs text-purple-600">Per konten</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Engagement Trend Chart -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Trend Engagement Rate</h3>
          <canvas ref="engagementTrendChart"></canvas>
        </div>

        <!-- Platform Performance Chart -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Performance per Platform</h3>
          <canvas ref="platformPerformanceChart"></canvas>
        </div>
      </div>

      <!-- Performance Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Best Performing Content -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
            </svg>
            Konten Terbaik (Top 5)
          </h3>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Konten</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Engagement</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(content, index) in topPerformingContent" :key="content.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="flex items-center">
                      <div :class="getRankClass(index + 1)" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white mr-2">
                        {{ index + 1 }}
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ content.judul_konten }}</div>
                    <div class="text-xs text-gray-500">{{ content.platform }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm font-bold text-green-600">{{ content.engagement_rate }}%</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">{{ formatNumber(content.views) }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Worst Performing Content -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
            </svg>
            Konten Perlu Perbaikan
          </h3>
          
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Konten</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Engagement</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Issue</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="content in worstPerformingContent" :key="content.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ content.judul_konten }}</div>
                    <div class="text-xs text-gray-500">{{ content.platform }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm font-bold text-red-600">{{ content.engagement_rate }}%</div>
                  </td>
                  <td class="px-4 py-3">
                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">{{ content.issue }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Content Type Analysis -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Content Type Performance -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Performance per Tipe Konten</h3>
          <canvas ref="contentTypeChart"></canvas>
        </div>

        <!-- Hashtag Performance -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Top Performing Hashtags</h3>
          <div class="space-y-3">
            <div v-for="hashtag in topHashtags" :key="hashtag.name" class="flex items-center justify-between">
              <span class="text-sm font-medium text-navy-900">{{ hashtag.name }}</span>
              <div class="flex items-center space-x-2">
                <div class="w-20 bg-gray-200 rounded-full h-2">
                  <div class="bg-yellow-400 h-2 rounded-full" :style="{ width: hashtag.performance + '%' }"></div>
                </div>
                <span class="text-xs text-gray-600">{{ hashtag.performance }}%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Posting Time Analysis -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Best Posting Times</h3>
          <div class="space-y-3">
            <div v-for="time in bestPostingTimes" :key="time.time" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <p class="text-sm font-medium text-navy-900">{{ time.time }}</p>
                <p class="text-xs text-gray-600">{{ time.platform }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-green-600">{{ time.engagement }}%</p>
                <p class="text-xs text-gray-600">engagement</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-6 rounded-lg shadow-lg">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">🚀 Rekomendasi Optimasi</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white bg-opacity-80 p-4 rounded-lg">
            <h4 class="font-medium text-navy-900 mb-2">📈 Tingkatkan Engagement</h4>
            <p class="text-sm text-navy-800">Fokus pada konten bertema "Investasi Properti" yang memiliki engagement rate tertinggi (18.5%)</p>
          </div>
          <div class="bg-white bg-opacity-80 p-4 rounded-lg">
            <h4 class="font-medium text-navy-900 mb-2">⏰ Waktu Posting Optimal</h4>
            <p class="text-sm text-navy-800">Posting di Instagram pada pukul 19:00-21:00 untuk engagement maksimal</p>
          </div>
          <div class="bg-white bg-opacity-80 p-4 rounded-lg">
            <h4 class="font-medium text-navy-900 mb-2">🎯 Platform Focus</h4>
            <p class="text-sm text-navy-800">TikTok menunjukkan performa terbaik. Alokasikan lebih banyak konten video</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'ContentAnalytics',
  data() {
    return {
      overallStats: {
        totalEngagementRate: 12.8,
        totalViews: 462500,
        avgEngagement: 10.2
      },
      topPerformingContent: [
        {
          id: 1,
          judul_konten: 'Testimoni Customer Villa Merah',
          platform: 'TikTok',
          engagement_rate: 18.5,
          views: 125000
        },
        {
          id: 2,
          judul_konten: 'Tips Investasi Properti Pemula',
          platform: 'Instagram',
          engagement_rate: 16.2,
          views: 98000
        },
        {
          id: 3,
          judul_konten: 'Tour Villa Merah Terbaru',
          platform: 'TikTok',
          engagement_rate: 14.8,
          views: 87000
        },
        {
          id: 4,
          judul_konten: 'Lokasi Strategis Project',
          platform: 'Instagram',
          engagement_rate: 13.5,
          views: 65000
        },
        {
          id: 5,
          judul_konten: 'Review Fasilitas Premium',
          platform: 'Instagram',
          engagement_rate: 12.9,
          views: 54000
        }
      ],
      worstPerformingContent: [
        {
          id: 6,
          judul_konten: 'Update Harga Properti',
          platform: 'Website',
          engagement_rate: 3.2,
          issue: 'Low Engagement'
        },
        {
          id: 7,
          judul_konten: 'Jadwal Open House',
          platform: 'Instagram',
          engagement_rate: 4.1,
          issue: 'Poor Timing'
        },
        {
          id: 8,
          judul_konten: 'Info Pembayaran KPR',
          platform: 'Website',
          engagement_rate: 4.8,
          issue: 'Complex Content'
        }
      ],
      topHashtags: [
        { name: '#villamerah', performance: 85 },
        { name: '#investasiproperti', performance: 78 },
        { name: '#propertipremium', performance: 72 },
        { name: '#hunianstrategis', performance: 68 },
        { name: '#kprmudah', performance: 65 }
      ],
      bestPostingTimes: [
        { time: '19:00 - 21:00', platform: 'Instagram', engagement: 16.8 },
        { time: '20:00 - 22:00', platform: 'TikTok', engagement: 18.2 },
        { time: '09:00 - 11:00', platform: 'Website', engagement: 8.5 }
      ]
    }
  },
  mounted() {
    this.initCharts();
  },
  methods: {
    initCharts() {
      // Engagement Trend Chart
      const engagementCtx = this.$refs.engagementTrendChart?.getContext('2d');
      if (engagementCtx) {
        new Chart(engagementCtx, {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
              label: 'Engagement Rate (%)',
              data: [8.5, 9.2, 10.1, 11.3, 12.8, 11.9, 13.2, 12.4, 14.1, 12.8],
              borderColor: '#1e3a8a',
              backgroundColor: 'rgba(30, 58, 138, 0.1)',
              tension: 0.4,
              fill: true
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
                beginAtZero: true,
                max: 20
              }
            }
          }
        });
      }

      // Platform Performance Chart
      const platformCtx = this.$refs.platformPerformanceChart?.getContext('2d');
      if (platformCtx) {
        new Chart(platformCtx, {
          type: 'bar',
          data: {
            labels: ['Instagram', 'TikTok', 'Website'],
            datasets: [
              {
                label: 'Engagement Rate (%)',
                data: [14.2, 16.8, 6.5],
                backgroundColor: ['#f59e0b', '#8b5cf6', '#1e3a8a'],
                borderWidth: 0
              }
            ]
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
                beginAtZero: true,
                max: 20
              }
            }
          }
        });
      }

      // Content Type Chart
      const typeCtx = this.$refs.contentTypeChart?.getContext('2d');
      if (typeCtx) {
        new Chart(typeCtx, {
          type: 'doughnut',
          data: {
            labels: ['Reels', 'Video', 'Carousel', 'Artikel'],
            datasets: [{
              data: [35, 28, 22, 15],
              backgroundColor: ['#f59e0b', '#8b5cf6', '#1e3a8a', '#6b7280'],
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
    },
    formatNumber(num) {
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      return num.toString();
    },
    getRankClass(rank) {
      if (rank === 1) return 'bg-yellow-500';
      if (rank === 2) return 'bg-gray-400';
      if (rank === 3) return 'bg-yellow-600';
      return 'bg-gray-500';
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

.text-navy-800 {
  color: #1e40af;
}
</style>