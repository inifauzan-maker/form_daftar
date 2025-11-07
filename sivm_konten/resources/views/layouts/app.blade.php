<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIVM Konten Marketing') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .bg-navy-900 { background-color: #1e3a8a; }
        .text-navy-900 { color: #1e3a8a; }
        .border-navy-900 { border-color: #1e3a8a; }
        .hover\:bg-navy-800:hover { background-color: #1e40af; }
        .hover\:border-navy-800:hover { border-color: #1e40af; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 font-sans antialiased">
    <div id="app">
        <nav class="bg-white shadow mb-6">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-6">
                    <a href="/dashboard" class="font-semibold text-navy-900 hover:text-blue-700">Dashboard</a>
                    <a href="/konten-marketing" class="font-semibold text-navy-900 hover:text-blue-700">Kalender Konten</a>
                    <a href="/arsip-konten" class="font-semibold text-navy-900 hover:text-blue-700">Arsip Konten</a>
                    <a href="/social-integration" class="font-semibold text-navy-900 hover:text-blue-700">Social Media</a>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Profile Photo & Dropdown -->
                    <div class="relative">
                        <button onclick="toggleProfileDropdown()" class="flex items-center space-x-2 focus:outline-none">
                            @if(auth()->user()->profile_photo)
                                <img src="{{ asset('storage/profile_photos/' . auth()->user()->profile_photo) }}" 
                                     alt="Profile Photo" 
                                     class="w-8 h-8 rounded-full object-cover border-2 border-gray-300">
                            @else
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-gray-700 font-medium">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                            <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profil Saya
                            </a>
                            <a href="/profile/photo" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-camera mr-2"></i> Ganti Foto Profil
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>
                    
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </nav>
        @yield('content')
        <live-chat></live-chat>
    </div>

    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const button = event.target.closest('button');
            
            if (!button || !button.onclick) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>