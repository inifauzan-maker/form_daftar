@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-navy-900 via-blue-800 to-navy-900">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto h-20 w-20 bg-yellow-400 rounded-lg flex items-center justify-center mb-6">
                <span class="text-navy-900 font-bold text-2xl">VM</span>
            </div>
            <h2 class="text-3xl font-bold text-white">
                Sistem Informasi Villa Merah
            </h2>
            <p class="mt-2 text-lg text-blue-100">
                Konten Marketing Dashboard
            </p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-xl shadow-2xl p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            required 
                            value="{{ old('email') }}"
                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 @error('email') border-red-500 @enderror"
                            placeholder="Masukkan email Anda"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 @error('password') border-red-500 @enderror"
                            placeholder="Masukkan password Anda"
                        >
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input 
                            id="remember" 
                            name="remember" 
                            type="checkbox" 
                            class="h-4 w-4 text-yellow-400 focus:ring-yellow-400 border-gray-300 rounded"
                            {{ old('remember') ? 'checked' : '' }}
                        >
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                            Remember me
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-navy-900 hover:text-navy-800 underline">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full bg-navy-900 text-white py-3 px-4 rounded-lg font-medium hover:bg-navy-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition-colors duration-200"
                    >
                        Sign In
                    </button>
                </div>

                <!-- Register Link -->
                @if (Route::has('register'))
                    <div class="text-center">
                        <span class="text-sm text-gray-600">Don't have an account? </span>
                        <a href="{{ route('register') }}" class="text-sm font-medium text-navy-900 hover:text-navy-800 underline">
                            Register here
                        </a>
                    </div>
                @endif
            </form>
        </div>


        <!-- Demo Accounts with Autofill -->
        <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 text-white">
            <h3 class="text-lg font-semibold mb-3 text-center">Demo Accounts</h3>
            <div class="space-y-2 text-sm">
                <button type="button" onclick="fillDemo('admin@villamerah.com')" class="w-full flex justify-between items-center bg-yellow-500/20 hover:bg-yellow-500/40 text-yellow-100 px-3 py-2 rounded transition-colors">
                    <span>Kadiv Marketing</span>
                    <span class="font-mono">admin@villamerah.com</span>
                </button>
                <button type="button" onclick="fillDemo('socmed@villamerah.com')" class="w-full flex justify-between items-center bg-yellow-500/20 hover:bg-yellow-500/40 text-yellow-100 px-3 py-2 rounded transition-colors">
                    <span>Social Media</span>
                    <span class="font-mono">socmed@villamerah.com</span>
                </button>
                <button type="button" onclick="fillDemo('creator@villamerah.com')" class="w-full flex justify-between items-center bg-yellow-500/20 hover:bg-yellow-500/40 text-yellow-100 px-3 py-2 rounded transition-colors">
                    <span>Content Creator</span>
                    <span class="font-mono">creator@villamerah.com</span>
                </button>
                <div class="text-center mt-3 text-xs text-blue-200">
                    Password: <span class="font-mono">password123</span>
                </div>
            </div>
            <div class="text-center mt-2 text-xs text-blue-100">Klik salah satu untuk autofill login demo</div>
        </div>

        <!-- Footer -->
        <div class="text-center text-blue-100 text-sm">
            <p>&copy; 2025 Villa Merah. All rights reserved.</p>
        </div>
    </div>
</div>
<script>
    function fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
        document.getElementById('email').focus();
    }
</script>
@endsection