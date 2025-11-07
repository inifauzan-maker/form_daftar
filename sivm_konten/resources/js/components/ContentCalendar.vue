<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-navy-900 mb-6">Kalender Konten</h2>
      
      <!-- Calendar Controls -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-4">
          <button @click="previousMonth" class="p-2 text-navy-900 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
          </button>
          <h3 class="text-xl font-semibold text-navy-900">{{ currentMonthYear }}</h3>
          <button @click="nextMonth" class="p-2 text-navy-900 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </button>
        </div>
        
        <div class="flex items-center space-x-4">
          <button @click="viewMode = 'month'" :class="{ 'bg-navy-900 text-white': viewMode === 'month' }" class="px-4 py-2 rounded-lg border">
            Bulanan
          </button>
          <button @click="viewMode = 'week'" :class="{ 'bg-navy-900 text-white': viewMode === 'week' }" class="px-4 py-2 rounded-lg border">
            Mingguan
          </button>
          <button @click="showAddModal = true" class="bg-yellow-400 text-navy-900 px-4 py-2 rounded-lg font-medium hover:bg-yellow-500">
            + Tambah Konten
          </button>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 bg-gray-50 border-b">
          <div v-for="day in dayHeaders" :key="day" class="p-4 text-center text-sm font-medium text-gray-700">
            {{ day }}
          </div>
        </div>

        <!-- Calendar Days -->
        <div class="grid grid-cols-7">
          <div v-for="(day, index) in calendarDays" :key="index" 
               :class="{ 'bg-gray-50': !day.isCurrentMonth, 'border-yellow-400 border-2': day.isToday }"
               class="min-h-32 border-r border-b border-gray-200 p-2">
            
            <!-- Day Number -->
            <div class="flex justify-between items-center mb-2">
              <span :class="{ 'text-gray-400': !day.isCurrentMonth, 'font-bold text-navy-900': day.isToday }" class="text-sm">
                {{ day.date }}
              </span>
            </div>

            <!-- Content Items -->
            <div class="space-y-1">
              <div v-for="content in day.content" :key="content.id" 
                   :class="getContentStatusClass(content.status)"
                   class="text-xs p-1 rounded cursor-pointer truncate"
                   @click="showContentDetails(content)">
                {{ content.title }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="mt-4 flex flex-wrap items-center space-x-6">
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-red-500 rounded"></div>
          <span class="text-sm text-gray-600">Perlu Persetujuan</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-yellow-500 rounded"></div>
          <span class="text-sm text-gray-600">Dijadwalkan</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-green-500 rounded"></div>
          <span class="text-sm text-gray-600">Sudah Terbit</span>
        </div>
        <div class="flex items-center space-x-2">
          <div class="w-4 h-4 bg-blue-500 rounded"></div>
          <span class="text-sm text-gray-600">Draft</span>
        </div>
      </div>
    </div>

    <!-- Add Content Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-2xl mx-4">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">Tambah Konten Baru</h3>
        
        <form @submit.prevent="saveContent">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Judul Konten</label>
              <input v-model="newContent.title" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Konten</label>
              <select v-model="newContent.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">Pilih Tipe</option>
                <option value="Reels">Reels</option>
                <option value="Carousel">Carousel</option>
                <option value="Video">Video</option>
                <option value="Artikel">Artikel</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
              <select v-model="newContent.platform" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">Pilih Platform</option>
                <option value="Instagram">Instagram</option>
                <option value="TikTok">TikTok</option>
                <option value="Website">Website</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Posting</label>
              <input v-model="newContent.scheduledDate" type="datetime-local" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi/Caption</label>
            <textarea v-model="newContent.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400"></textarea>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Hashtags</label>
            <input v-model="newContent.hashtags" type="text" placeholder="#villamerah #properti" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>

          <!-- File Upload, tampil jika tipe konten Reels, Carousel, Video -->
          <div class="mb-4" v-if="['Reels','Carousel','Video'].includes(newContent.type)">
            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Konten (JPG, MP4, MOV)</label>
            <input ref="fileInput" type="file" accept=".jpg,.jpeg,.mp4,.mov" @change="handleFileUpload" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <div v-if="newContent.fileName" class="text-xs text-gray-500 mt-1">File: {{ newContent.fileName }}</div>
          </div>

          <div class="flex justify-end space-x-4">
            <button @click="showAddModal = false" type="button" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-navy-900 text-white rounded-lg hover:bg-navy-800">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Content Details Modal -->
    <div v-if="selectedContent" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-lg mx-4">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">Detail Konten</h3>
        
        <div class="space-y-3">
          <div>
            <span class="text-sm text-gray-600">Judul:</span>
            <p class="font-medium">{{ selectedContent.title }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Platform:</span>
            <p class="font-medium">{{ selectedContent.platform }}</p>
          </div>
          <div>
            <span class="text-sm text-gray-600">Status:</span>
            <span :class="getContentStatusClass(selectedContent.status)" class="px-2 py-1 rounded text-xs">
              {{ selectedContent.status }}
            </span>
          </div>
          <div>
            <span class="text-sm text-gray-600">Tanggal:</span>
            <p class="font-medium">{{ formatDate(selectedContent.scheduledDate) }}</p>
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
  name: 'ContentCalendar',
  data() {
    return {
      viewMode: 'month',
      currentDate: new Date(),
      showAddModal: false,
      selectedContent: null,
      dayHeaders: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
      newContent: {
        title: '',
        type: '',
        platform: '',
        scheduledDate: '',
        description: '',
        hashtags: '',
        file: null,
        fileName: ''
      },
      sampleContent: []
    };
  },
  computed: {
    currentMonthYear() {
      return this.currentDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
    },
    calendarDays() {
      const year = this.currentDate.getFullYear();
      const month = this.currentDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDate = new Date(firstDay);
      startDate.setDate(startDate.getDate() - firstDay.getDay());
      
      const days = [];
      const today = new Date();
      
      for (let i = 0; i < 42; i++) {
        const currentDay = new Date(startDate);
        currentDay.setDate(startDate.getDate() + i);
        
        const dayContent = this.sampleContent.filter(content => {
          const contentDate = new Date(content.scheduledDate);
          return contentDate.toDateString() === currentDay.toDateString();
        });
        
        days.push({
          date: currentDay.getDate(),
          fullDate: currentDay,
          isCurrentMonth: currentDay.getMonth() === month,
          isToday: currentDay.toDateString() === today.toDateString(),
          content: dayContent
        });
      }
      
      return days;
    }
  },
  methods: {
    previousMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() - 1);
      this.currentDate = new Date(this.currentDate);
    },
    nextMonth() {
      this.currentDate.setMonth(this.currentDate.getMonth() + 1);
      this.currentDate = new Date(this.currentDate);
    },
    getContentStatusClass(status) {
      const classes = {
        'Needs Approval': 'bg-red-500 text-white',
        'Scheduled': 'bg-yellow-500 text-white',
        'Published': 'bg-green-500 text-white',
        'Draft': 'bg-blue-500 text-white'
      };
      return classes[status] || 'bg-gray-500 text-white';
    },
    showContentDetails(content) {
      this.selectedContent = content;
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    saveContent() {
      // Kirim data ke backend Laravel
      const formData = new FormData();
      formData.append('title', this.newContent.title);
      formData.append('type', this.newContent.type);
      formData.append('platform', this.newContent.platform);
      formData.append('scheduledDate', this.newContent.scheduledDate);
      formData.append('description', this.newContent.description);
      formData.append('hashtags', this.newContent.hashtags);
      if (this.newContent.file) {
        formData.append('file', this.newContent.file);
      }
      
      fetch('/api/upload-konten', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            // Tambahkan ke sampleContent untuk demo
            this.sampleContent.push({
              id: data.data.id_konten,
              title: data.data.judul_konten,
              platform: data.data.platform,
              status: data.data.status,
              scheduledDate: data.data.tanggal_posting,
              type: data.data.tipe_konten,
              file: data.data.file
            });
            
            // Reset form
            this.newContent = {
              title: '',
              type: '',
              platform: '',
              scheduledDate: '',
              description: '',
              hashtags: '',
              file: null,
              fileName: ''
            };
            
            this.showAddModal = false;
            alert('Konten berhasil disimpan!');
          } else {
            alert('Gagal menyimpan konten!');
          }
        })
        .catch(() => alert('Gagal upload konten!'));
    },
    handleFileUpload(e) {
      const file = e.target.files[0];
      if (file) {
        const allowed = ['image/jpeg','image/jpg','video/mp4','video/quicktime'];
        if (!allowed.includes(file.type)) {
          alert('File harus JPG, MP4, atau MOV');
          this.$refs.fileInput.value = '';
          return;
        }
        this.newContent.file = file;
        this.newContent.fileName = file.name;
      } else {
        this.newContent.file = null;
        this.newContent.fileName = '';
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

.border-navy-800 {
  border-color: #1e40af;
}

.hover\:bg-navy-800:hover {
  background-color: #1e40af;
}
</style>