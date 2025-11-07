<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-navy-900 mb-6">Arsip Konten</h2>
      
      <!-- Filters and Search -->
      <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Konten</label>
            <div class="relative">
              <input v-model="searchTerm" type="text" placeholder="Cari judul, platform..." 
                     class="w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
            <select v-model="filters.platform" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <option value="">Semua Platform</option>
              <option value="Instagram">Instagram</option>
              <option value="TikTok">TikTok</option>
              <option value="Website">Website</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Konten</label>
            <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <option value="">Semua Tipe</option>
              <option value="Reels">Reels</option>
              <option value="Carousel">Carousel</option>
              <option value="Video">Video</option>
              <option value="Artikel">Artikel</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
            <select v-model="filters.month" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <option value="">Semua Bulan</option>
              <option value="2025-01">Januari 2025</option>
              <option value="2025-02">Februari 2025</option>
              <option value="2025-03">Maret 2025</option>
              <option value="2025-04">April 2025</option>
              <option value="2025-05">Mei 2025</option>
              <option value="2025-06">Juni 2025</option>
              <option value="2025-07">Juli 2025</option>
              <option value="2025-08">Agustus 2025</option>
              <option value="2025-09">September 2025</option>
              <option value="2025-10">Oktober 2025</option>
            </select>
          </div>
        </div>
        
        <div class="flex justify-between items-center">
          <button @click="resetFilters" class="text-gray-600 hover:text-gray-800">
            Reset Filter
          </button>
          
          <div class="flex space-x-2">
            <button @click="exportToExcel" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
              Export Excel
            </button>
            <button @click="exportToPDF" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
              Export PDF
            </button>
          </div>
        </div>
      </div>

      <!-- Content Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <!-- Table Header -->
            <thead class="bg-gray-50">
              <tr>
                <th @click="sortBy('judul_konten')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center space-x-1">
                    <span>Judul Konten</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                <th @click="sortBy('platform')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center space-x-1">
                    <span>Platform</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                <th @click="sortBy('tanggal_posting')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center space-x-1">
                    <span>Tanggal Posting</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </div>
                </th>
                <th @click="sortBy('engagement_rate')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center space-x-1">
                    <span>Engagement</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </div>
                </th>
                <th @click="sortBy('views')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                  <div class="flex items-center space-x-1">
                    <span>Views</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                    </svg>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            
            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="content in paginatedContent" :key="content.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ content.judul_konten }}</div>
                  <div class="text-sm text-gray-500 truncate max-w-xs">{{ content.deskripsi }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                    <svg v-if="content.tipe_konten === 'Video' || content.tipe_konten === 'Reels'" class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-7 0a9 9 0 018 0m-8 0V9a9 9 0 018 0v5z"></path>
                    </svg>
                    <svg v-else-if="content.tipe_konten === 'Artikel'" class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <svg v-else class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="getPlatformClass(content.platform)">
                    {{ content.platform }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ content.tipe_konten }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(content.tanggal_posting) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">{{ content.engagement_rate }}%</div>
                    <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                      <div class="bg-yellow-400 h-2 rounded-full" :style="{ width: content.engagement_rate + '%' }"></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatNumber(content.views) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewDetails(content)" class="text-blue-600 hover:text-blue-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button @click="editContent(content)" class="text-yellow-600 hover:text-yellow-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button @click="archiveContent(content)" class="text-red-600 hover:text-red-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h1.586a1 1 0 01.707.293L8 5.586A1 1 0 008.586 6H19a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center text-sm text-gray-700">
              <span>Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredContent.length) }} of {{ filteredContent.length }} entries</span>
            </div>
            <div class="flex items-center space-x-2">
              <button @click="previousPage" :disabled="currentPage === 1" 
                      class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                Previous
              </button>
              
              <button v-for="page in totalPages" :key="page" @click="currentPage = page"
                      :class="{ 'bg-navy-900 text-white': currentPage === page }"
                      class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50">
                {{ page }}
              </button>
              
              <button @click="nextPage" :disabled="currentPage === totalPages"
                      class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Details Modal -->
    <div v-if="selectedContent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-2xl mx-4 max-h-96 overflow-y-auto">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">Detail Konten</h3>
        
        <div class="grid grid-cols-2 gap-4">
          <div>
            <span class="text-sm text-gray-600">Judul:</span>
            <p class="font-medium">{{ selectedContent.judul_konten }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Platform:</span>
            <p class="font-medium">{{ selectedContent.platform }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Tipe:</span>
            <p class="font-medium">{{ selectedContent.tipe_konten }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Tanggal Posting:</span>
            <p class="font-medium">{{ formatDate(selectedContent.tanggal_posting) }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Views:</span>
            <p class="font-medium">{{ formatNumber(selectedContent.views) }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Engagement Rate:</span>
            <p class="font-medium">{{ selectedContent.engagement_rate }}%</p>
          </div>
          <div class="col-span-2">
            <span class="text-sm text-gray-600">Deskripsi:</span>
            <p class="font-medium">{{ selectedContent.deskripsi }}</p>
          </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6">
          <button @click="selectedContent = null" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
            Tutup
          </button>
          <button class="px-4 py-2 bg-yellow-400 text-navy-900 rounded-lg hover:bg-yellow-500">
            Edit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ContentArchive',
  data() {
    return {
      searchTerm: '',
      sortField: '',
      sortDirection: 'asc',
      currentPage: 1,
      itemsPerPage: 10,
      selectedContent: null,
      filters: {
        platform: '',
        type: '',
        month: ''
      },
      sampleContent: [
        {
          id: 1,
          judul_konten: 'Tips Investasi Properti untuk Pemula',
          deskripsi: 'Panduan lengkap investasi properti yang menguntungkan untuk pemula dengan budget terbatas',
          platform: 'Instagram',
          tipe_konten: 'Reels',
          tanggal_posting: '2025-10-01T10:00:00',
          engagement_rate: 8.5,
          views: 45000,
          likes: 3200,
          comments: 150,
          share_count: 89
        },
        {
          id: 2,
          judul_konten: 'Review Hunian Villa Merah Terbaru',
          deskripsi: 'Review lengkap hunian terbaru Villa Merah dengan fasilitas premium dan lokasi strategis',
          platform: 'TikTok',
          tipe_konten: 'Video',
          tanggal_posting: '2025-09-28T14:30:00',
          engagement_rate: 12.3,
          views: 78000,
          likes: 5600,
          comments: 340,
          share_count: 245
        },
        {
          id: 3,
          judul_konten: 'Panduan Lengkap KPR 2025',
          deskripsi: 'Artikel komprehensif tentang syarat dan cara mengajukan KPR di tahun 2025',
          platform: 'Website',
          tipe_konten: 'Artikel',
          tanggal_posting: '2025-09-25T09:15:00',
          engagement_rate: 6.8,
          views: 12500,
          likes: 890,
          comments: 67,
          share_count: 34
        },
        {
          id: 4,
          judul_konten: 'Lokasi Strategis Villa Merah',
          deskripsi: 'Carousel showcase lokasi-lokasi strategis Villa Merah di berbagai kota',
          platform: 'Instagram',
          tipe_konten: 'Carousel',
          tanggal_posting: '2025-09-20T16:45:00',
          engagement_rate: 9.7,
          views: 32000,
          likes: 2400,
          comments: 98,
          share_count: 156
        },
        {
          id: 5,
          judul_konten: 'Testimoni Customer Villa Merah',
          deskripsi: 'Video testimoni dari customer yang puas dengan pelayanan Villa Merah',
          platform: 'TikTok',
          tipe_konten: 'Video',
          tanggal_posting: '2025-09-18T11:20:00',
          engagement_rate: 15.2,
          views: 95000,
          likes: 7300,
          comments: 425,
          share_count: 380
        }
      ]
    }
  },
  computed: {
    filteredContent() {
      let filtered = [...this.sampleContent];
      
      // Search filter
      if (this.searchTerm) {
        filtered = filtered.filter(item => 
          item.judul_konten.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          item.platform.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          item.deskripsi.toLowerCase().includes(this.searchTerm.toLowerCase())
        );
      }
      
      // Platform filter
      if (this.filters.platform) {
        filtered = filtered.filter(item => item.platform === this.filters.platform);
      }
      
      // Type filter
      if (this.filters.type) {
        filtered = filtered.filter(item => item.tipe_konten === this.filters.type);
      }
      
      // Month filter
      if (this.filters.month) {
        filtered = filtered.filter(item => {
          const itemDate = new Date(item.tanggal_posting);
          const filterDate = this.filters.month + '-01';
          return itemDate.toISOString().substr(0, 7) === this.filters.month;
        });
      }
      
      // Sort
      if (this.sortField) {
        filtered.sort((a, b) => {
          let aVal = a[this.sortField];
          let bVal = b[this.sortField];
          
          if (this.sortField === 'tanggal_posting') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
          }
          
          if (this.sortDirection === 'asc') {
            return aVal > bVal ? 1 : -1;
          } else {
            return aVal < bVal ? 1 : -1;
          }
        });
      }
      
      return filtered;
    },
    paginatedContent() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredContent.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredContent.length / this.itemsPerPage);
    }
  },
  methods: {
    sortBy(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortField = field;
        this.sortDirection = 'asc';
      }
    },
    resetFilters() {
      this.searchTerm = '';
      this.filters = {
        platform: '',
        type: '',
        month: ''
      };
      this.sortField = '';
      this.sortDirection = 'asc';
      this.currentPage = 1;
    },
    getPlatformClass(platform) {
      const classes = {
        'Instagram': 'bg-pink-100 text-pink-800',
        'TikTok': 'bg-purple-100 text-purple-800',
        'Website': 'bg-blue-100 text-blue-800'
      };
      return classes[platform] || 'bg-gray-100 text-gray-800';
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    formatNumber(num) {
      if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
      } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
      }
      return num.toString();
    },
    viewDetails(content) {
      this.selectedContent = content;
    },
    editContent(content) {
      console.log('Edit content:', content);
      // Implement edit functionality
    },
    archiveContent(content) {
      console.log('Archive content:', content);
      // Implement archive functionality
    },
    exportToExcel() {
      console.log('Export to Excel');
      // Implement Excel export
    },
    exportToPDF() {
      console.log('Export to PDF');
      // Implement PDF export
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
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