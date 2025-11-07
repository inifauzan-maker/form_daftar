@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
                <p class="text-gray-600">Kelola informasi profil dan foto profil Anda</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Profile Photo Section -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Foto Profil</h3>
                
                <div class="text-center">
                    @if(auth()->user()->profile_photo)
                        <img src="{{ asset('storage/profile_photos/' . auth()->user()->profile_photo) }}" 
                             alt="Profile Photo" 
                             class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-gray-200">
                    @else
                        <div class="w-32 h-32 rounded-full bg-blue-500 flex items-center justify-center mx-auto mb-4 border-4 border-gray-200">
                            <span class="text-4xl text-white font-bold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <a href="/profile/photo" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-camera mr-2"></i>
                            {{ auth()->user()->profile_photo ? 'Ganti Foto' : 'Upload Foto' }}
                        </a>
                        
                        @if(auth()->user()->profile_photo)
                            <form action="/profile/photo/delete" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="block bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors w-full mt-2">
                                    <i class="fas fa-trash mr-2"></i>
                                    Hapus Foto
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Profile Information Section -->
            <div class="md:col-span-2 bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-4">Informasi Profil</h3>
                
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" id="name" 
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email" 
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru (Opsional)</label>
                            <input type="password" name="password" id="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Kosongkan jika tidak ingin mengubah password">
                            @error('password')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Profile Statistics -->
        <div class="mt-6 bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Statistik Profil</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ auth()->user()->created_at->format('d M Y') }}</div>
                    <div class="text-sm text-gray-600">Bergabung Sejak</div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600">{{ auth()->user()->updated_at->diffForHumans() }}</div>
                    <div class="text-sm text-gray-600">Terakhir Update</div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ auth()->user()->profile_photo ? 'Ada' : 'Belum' }}
                    </div>
                    <div class="text-sm text-gray-600">Foto Profil</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
