<template>
  <div>
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-navy-900 mb-6">Manajemen User</h2>
      
      <!-- Header Actions -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-4">
          <div class="relative">
            <input v-model="searchTerm" type="text" placeholder="Cari user..." 
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          
          <select v-model="filterRole" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <option value="">Semua Role</option>
            <option value="kadiv_marketing">Kadiv Marketing</option>
            <option value="social_media_specialist">Social Media Specialist</option>
            <option value="ads_specialist">Ads Specialist</option>
            <option value="content_creator">Content Creator</option>
            <option value="sales_team">Sales Team</option>
            <option value="data_analyst">Data Analyst</option>
          </select>

          <select v-model="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
          </select>
        </div>
        
        <button @click="showAddUserModal = true" class="bg-yellow-400 text-navy-900 px-6 py-2 rounded-lg font-medium hover:bg-yellow-500 flex items-center space-x-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Tambah User</span>
        </button>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Konten Dibuat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                <!-- User Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getUserInitials(user.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                
                <!-- Role -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getRoleClass(user.role)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getRoleDisplay(user.role) }}
                  </span>
                </td>
                
                <!-- Jabatan -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.jabatan }}
                </td>
                
                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div :class="user.is_active ? 'bg-green-400' : 'bg-red-400'" class="w-2 h-2 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-900">{{ user.is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                  </div>
                </td>
                
                <!-- Last Login -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.last_login_at) }}
                </td>
                
                <!-- Content Count -->
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center space-x-2">
                    <span class="font-medium">{{ user.content_count }}</span>
                    <span class="text-gray-500">konten</span>
                  </div>
                </td>
                
                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewUserDetails(user)" class="text-blue-600 hover:text-blue-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button @click="editUser(user)" class="text-yellow-600 hover:text-yellow-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button @click="toggleUserStatus(user)" :class="user.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                      <svg v-if="user.is_active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </button>
                    <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- User Statistics -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Statistik User</h3>
          <canvas ref="userStatsChart"></canvas>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Aktivitas User</h3>
          <div class="space-y-3">
            <div v-for="activity in userActivities" :key="activity.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <p class="text-sm font-medium text-navy-900">{{ activity.user }}</p>
                <p class="text-xs text-gray-600">{{ activity.action }}</p>
              </div>
              <p class="text-xs text-gray-500">{{ activity.time }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <h3 class="text-lg font-semibold text-navy-900 mb-4">Role Distribution</h3>
          <div class="space-y-3">
            <div v-for="role in roleStats" :key="role.name" class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-900">{{ role.display }}</span>
              <div class="flex items-center space-x-2">
                <div class="w-20 bg-gray-200 rounded-full h-2">
                  <div class="bg-yellow-400 h-2 rounded-full" :style="{ width: (role.count / totalUsers * 100) + '%' }"></div>
                </div>
                <span class="text-xs text-gray-600">{{ role.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-2xl mx-4">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">Tambah User Baru</h3>
        
        <form @submit.prevent="saveUser">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
              <input v-model="newUser.name" type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input v-model="newUser.email" type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
              <input v-model="newUser.password" type="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
              <select v-model="newUser.jabatan" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">Pilih Jabatan</option>
                <option value="Kadiv Marketing">Kadiv Marketing</option>
                <option value="Social Media Specialist">Social Media Specialist</option>
                <option value="Ads Specialist">Ads Specialist</option>
                <option value="Content Creator">Content Creator</option>
                <option value="Sales Team">Sales Team</option>
                <option value="Data Analyst">Data Analyst</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
              <select v-model="newUser.role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">Pilih Role</option>
                <option value="kadiv_marketing">Kadiv Marketing</option>
                <option value="social_media_specialist">Social Media Specialist</option>
                <option value="ads_specialist">Ads Specialist</option>
                <option value="content_creator">Content Creator</option>
                <option value="sales_team">Sales Team</option>
                <option value="data_analyst">Data Analyst</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end space-x-4">
            <button @click="showAddUserModal = false" type="button" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
              Batal
            </button>
            <button type="submit" class="px-4 py-2 bg-navy-900 text-white rounded-lg hover:bg-navy-800">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- User Details Modal -->
    <div v-if="selectedUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg w-full max-w-lg mx-4">
        <h3 class="text-lg font-semibold text-navy-900 mb-4">Detail User</h3>
        
        <div class="space-y-4">
          <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-xl font-medium text-gray-700">{{ getUserInitials(selectedUser.name) }}</span>
            </div>
            <div>
              <h4 class="text-lg font-medium text-navy-900">{{ selectedUser.name }}</h4>
              <p class="text-sm text-gray-600">{{ selectedUser.email }}</p>
              <span :class="getRoleClass(selectedUser.role)" class="px-2 py-1 text-xs font-semibold rounded-full">
                {{ getRoleDisplay(selectedUser.role) }}
              </span>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <span class="text-sm text-gray-600">Jabatan:</span>
              <p class="font-medium">{{ selectedUser.jabatan }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Status:</span>
              <p class="font-medium">{{ selectedUser.is_active ? 'Aktif' : 'Tidak Aktif' }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Last Login:</span>
              <p class="font-medium">{{ formatDate(selectedUser.last_login_at) }}</p>
            </div>
            <div>
              <span class="text-sm text-gray-600">Konten Dibuat:</span>
              <p class="font-medium">{{ selectedUser.content_count }} konten</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-4 mt-6">
          <button @click="selectedUser = null" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
            Tutup
          </button>
          <button @click="editUser(selectedUser)" class="px-4 py-2 bg-yellow-400 text-navy-900 rounded-lg hover:bg-yellow-500">
            Edit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  name: 'UserManagement',
  data() {
    return {
      searchTerm: '',
      filterRole: '',
      filterStatus: '',
      showAddUserModal: false,
      selectedUser: null,
      newUser: {
        name: '',
        email: '',
        password: '',
        jabatan: '',
        role: ''
      },
      users: [
        {
          id: 1,
          name: 'Ahmad Fauzi',
          email: 'ahmad.fauzi@villamerah.com',
          jabatan: 'Kadiv Marketing',
          role: 'kadiv_marketing',
          is_active: true,
          last_login_at: '2025-10-10T08:30:00',
          content_count: 25
        },
        {
          id: 2,
          name: 'Siti Nurhaliza',
          email: 'siti.nurhaliza@villamerah.com',
          jabatan: 'Social Media Specialist',
          role: 'social_media_specialist',
          is_active: true,
          last_login_at: '2025-10-09T14:45:00',
          content_count: 48
        },
        {
          id: 3,
          name: 'Budi Santoso',
          email: 'budi.santoso@villamerah.com',
          jabatan: 'Content Creator',
          role: 'content_creator',
          is_active: true,
          last_login_at: '2025-10-09T16:20:00',
          content_count: 32
        },
        {
          id: 4,
          name: 'Maria Gonzales',
          email: 'maria.gonzales@villamerah.com',
          jabatan: 'Ads Specialist',
          role: 'ads_specialist',
          is_active: false,
          last_login_at: '2025-10-05T10:15:00',
          content_count: 15
        },
        {
          id: 5,
          name: 'Rini Puspita',
          email: 'rini.puspita@villamerah.com',
          jabatan: 'Data Analyst',
          role: 'data_analyst',
          is_active: true,
          last_login_at: '2025-10-08T09:30:00',
          content_count: 8
        }
      ],
      userActivities: [
        { id: 1, user: 'Siti Nurhaliza', action: 'Membuat konten baru', time: '2 jam lalu' },
        { id: 2, user: 'Budi Santoso', action: 'Mengedit konten', time: '4 jam lalu' },
        { id: 3, user: 'Ahmad Fauzi', action: 'Approve konten', time: '6 jam lalu' },
        { id: 4, user: 'Rini Puspita', action: 'Melihat analytics', time: '8 jam lalu' }
      ]
    }
  },
  computed: {
    filteredUsers() {
      let filtered = [...this.users];
      
      if (this.searchTerm) {
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          user.email.toLowerCase().includes(this.searchTerm.toLowerCase())
        );
      }
      
      if (this.filterRole) {
        filtered = filtered.filter(user => user.role === this.filterRole);
      }
      
      if (this.filterStatus) {
        const isActive = this.filterStatus === 'active';
        filtered = filtered.filter(user => user.is_active === isActive);
      }
      
      return filtered;
    },
    totalUsers() {
      return this.users.length;
    },
    roleStats() {
      const stats = {};
      this.users.forEach(user => {
        stats[user.role] = (stats[user.role] || 0) + 1;
      });
      
      return Object.keys(stats).map(role => ({
        name: role,
        display: this.getRoleDisplay(role),
        count: stats[role]
      }));
    }
  },
  mounted() {
    this.initCharts();
  },
  methods: {
    initCharts() {
      const ctx = this.$refs.userStatsChart?.getContext('2d');
      if (ctx) {
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Aktif', 'Tidak Aktif'],
            datasets: [{
              data: [
                this.users.filter(u => u.is_active).length,
                this.users.filter(u => !u.is_active).length
              ],
              backgroundColor: ['#10b981', '#ef4444'],
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
    getUserInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase();
    },
    getRoleClass(role) {
      const classes = {
        'kadiv_marketing': 'bg-purple-100 text-purple-800',
        'social_media_specialist': 'bg-pink-100 text-pink-800',
        'ads_specialist': 'bg-blue-100 text-blue-800',
        'content_creator': 'bg-green-100 text-green-800',
        'sales_team': 'bg-yellow-100 text-yellow-800',
        'data_analyst': 'bg-gray-100 text-gray-800'
      };
      return classes[role] || 'bg-gray-100 text-gray-800';
    },
    getRoleDisplay(role) {
      const displays = {
        'kadiv_marketing': 'Kadiv Marketing',
        'social_media_specialist': 'Social Media Specialist',
        'ads_specialist': 'Ads Specialist',
        'content_creator': 'Content Creator',
        'sales_team': 'Sales Team',
        'data_analyst': 'Data Analyst'
      };
      return displays[role] || role;
    },
    formatDate(dateString) {
      if (!dateString) return 'Belum pernah login';
      return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    viewUserDetails(user) {
      this.selectedUser = user;
    },
    editUser(user) {
      this.selectedUser = null;
      // Implement edit functionality
      console.log('Edit user:', user);
    },
    toggleUserStatus(user) {
      user.is_active = !user.is_active;
      console.log('Toggle status for user:', user.name, 'to', user.is_active ? 'active' : 'inactive');
    },
    deleteUser(user) {
      if (confirm(`Apakah Anda yakin ingin menghapus user ${user.name}?`)) {
        this.users = this.users.filter(u => u.id !== user.id);
        console.log('Delete user:', user.name);
      }
    },
    saveUser() {
      // Add new user to the list
      const newId = Math.max(...this.users.map(u => u.id)) + 1;
      this.users.push({
        id: newId,
        name: this.newUser.name,
        email: this.newUser.email,
        jabatan: this.newUser.jabatan,
        role: this.newUser.role,
        is_active: true,
        last_login_at: null,
        content_count: 0
      });
      
      // Reset form
      this.newUser = {
        name: '',
        email: '',
        password: '',
        jabatan: '',
        role: ''
      };
      
      this.showAddUserModal = false;
      console.log('User saved successfully');
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

.hover\:bg-navy-800:hover {
  background-color: #1e40af;
}
</style>