@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center">
                <a href="/profile" class="text-gray-600 hover:text-gray-900 mr-4">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ganti Foto Profil</h1>
                    <p class="text-gray-600">Upload foto profil baru Anda</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- Current Photo -->
            <div class="text-center mb-8">
                <h3 class="text-lg font-semibold mb-4">Foto Profil Saat Ini</h3>
                @if(auth()->user()->profile_photo)
                    <img src="{{ asset('storage/profile_photos/' . auth()->user()->profile_photo) }}" 
                         alt="Current Profile Photo" 
                         class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-gray-200">
                @else
                    <div class="w-32 h-32 rounded-full bg-blue-500 flex items-center justify-center mx-auto border-4 border-gray-200">
                        <span class="text-4xl text-white font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Upload Form -->
            <form action="/profile/photo" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- File Upload Area -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Foto Baru
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="profile_photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Pilih foto</span>
                                    <input id="profile_photo" name="profile_photo" type="file" class="sr-only" accept="image/*" required onchange="previewImage(this)">
                                </label>
                                <p class="pl-1">atau drag & drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PNG, JPG, GIF hingga 2MB
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Image Preview -->
                <div id="imagePreview" class="hidden text-center">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Preview:</h4>
                    <img id="preview" class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-blue-200" alt="Preview">
                </div>

                <!-- Upload Guidelines -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Panduan Upload Foto Profil:
                    </h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li>• Format yang didukung: JPEG, PNG, JPG, GIF</li>
                        <li>• Ukuran maksimal: 2MB</li>
                        <li>• Resolusi yang disarankan: 400x400 pixel atau lebih</li>
                        <li>• Foto akan otomatis dipotong menjadi lingkaran</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <a href="/profile" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.classList.remove('hidden');
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection