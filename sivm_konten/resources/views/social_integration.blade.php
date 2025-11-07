@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Integrasi Social Media</h2>
    
    <!-- Instagram Integration -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-purple-400 via-pink-400 to-yellow-400 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">Instagram</h3>
                    <p class="text-sm text-gray-600">Hubungkan akun Instagram untuk publish otomatis</p>
                </div>
            </div>
            <div id="instagram-status" class="flex items-center space-x-3">
                @if(session('instagram_connected'))
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Terhubung</span>
                    <button onclick="disconnectInstagram()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Putuskan Koneksi
                    </button>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">Belum Terhubung</span>
                    <button onclick="connectInstagram()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600">
                        Hubungkan Instagram
                    </button>
                @endif
            </div>
        </div>
        
        @if(session('instagram_connected'))
        <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Informasi Akun</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Username:</span>
                    <span class="ml-2 font-medium">{{ session('instagram_username', 'Loading...') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Followers:</span>
                    <span class="ml-2 font-medium">{{ session('instagram_followers', 'Loading...') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- TikTok Integration -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-black rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.321 5.562a5.124 5.124 0 0 1-.443-.258 6.228 6.228 0 0 1-1.137-.966c-.849-.936-1.343-2.178-1.343-3.338h-2.923v11.586c0 2.623-1.834 4.414-4.32 4.414-2.485 0-4.319-1.791-4.319-4.414s1.834-4.414 4.319-4.414c.3 0 .592.033.871.095v-3.049a7.645 7.645 0 0 0-.871-.051C5.963 5.167 2.4 8.73 2.4 13.122s3.563 7.955 7.755 7.955 7.755-3.563 7.755-7.955V9.004a9.69 9.69 0 0 0 5.646 1.781V7.862a6.234 6.234 0 0 1-4.235-2.3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">TikTok</h3>
                    <p class="text-sm text-gray-600">Hubungkan akun TikTok untuk publish otomatis</p>
                </div>
            </div>
            <div id="tiktok-status" class="flex items-center space-x-3">
                @if(session('tiktok_connected'))
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">Terhubung</span>
                    <button onclick="disconnectTikTok()" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Putuskan Koneksi
                    </button>
                @else
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">Belum Terhubung</span>
                    <button onclick="connectTikTok()" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800">
                        Hubungkan TikTok
                    </button>
                @endif
            </div>
        </div>
        
        @if(session('tiktok_connected'))
        <div class="border-t pt-4">
            <h4 class="font-medium mb-2">Informasi Akun</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Username:</span>
                    <span class="ml-2 font-medium">{{ session('tiktok_username', 'Loading...') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Followers:</span>
                    <span class="ml-2 font-medium">{{ session('tiktok_followers', 'Loading...') }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- API Settings -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Pengaturan API</h3>
        <p class="text-sm text-gray-600 mb-4">Konfigurasi API keys untuk integrasi social media. Pastikan Anda memiliki akses developer ke platform tersebut.</p>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instagram Access Token</label>
                <input type="password" id="instagram_token" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Masukkan Instagram Access Token">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">TikTok Client Key</label>
                <input type="password" id="tiktok_key" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Masukkan TikTok Client Key">
            </div>
            <button onclick="saveApiSettings()" class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800">
                Simpan Pengaturan
            </button>
        </div>
    </div>
</div>

<script>
function connectInstagram() {
    // Redirect to Instagram OAuth
    const clientId = 'YOUR_INSTAGRAM_CLIENT_ID';
    const redirectUri = encodeURIComponent('{{ route("social.callback", "instagram") }}');
    const scope = 'user_profile,user_media';
    const url = `https://api.instagram.com/oauth/authorize?client_id=${clientId}&redirect_uri=${redirectUri}&scope=${scope}&response_type=code`;
    window.location.href = url;
}

function connectTikTok() {
    // Redirect to TikTok OAuth
    const clientKey = 'YOUR_TIKTOK_CLIENT_KEY';
    const redirectUri = encodeURIComponent('{{ route("social.callback", "tiktok") }}');
    const scope = 'user.info.basic,video.list';
    const url = `https://www.tiktok.com/auth/authorize/?client_key=${clientKey}&response_type=code&scope=${scope}&redirect_uri=${redirectUri}`;
    window.location.href = url;
}

function disconnectInstagram() {
    if(confirm('Yakin ingin memutuskan koneksi Instagram?')) {
        fetch('{{ route("social.disconnect", "instagram") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }
}

function disconnectTikTok() {
    if(confirm('Yakin ingin memutuskan koneksi TikTok?')) {
        fetch('{{ route("social.disconnect", "tiktok") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    }
}

function saveApiSettings() {
    const instagramToken = document.getElementById('instagram_token').value;
    const tiktokKey = document.getElementById('tiktok_key').value;
    
    fetch('{{ route("social.settings") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            instagram_token: instagramToken,
            tiktok_key: tiktokKey
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Pengaturan berhasil disimpan!');
        }
    });
}
</script>
@endsection