@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-custom">
        <div class="w-full max-w-md mx-auto p-6">

            @if (session('success'))
                <div class="mb-4 p-4 text-sm text-purple-700 bg-purple-100 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-12 text-center">
                <div class="w-16 h-16 mx-auto mb-6 bg-gradient-to-r from-purple-500 to-purple-800 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-8-6h16"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-primary">Yourbucket MKS</h2>
                <p class="text-secondary mt-2">Daftar terlebih dahulu untuk login</p>
            </div>

            <div class="bg-card p-8 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-sm border border-gray-100">

                <p class="text-xs text-gray-400 mb-4"><span class="text-red-500 font-bold">*</span> Wajib diisi</p>

                <form class="space-y-6" action="{{ route('auth.register') }}" method="POST">
                    @csrf

                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input id="name" name="name" type="text" placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            class="input-field @error('name') border-red-500 @enderror"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input id="email" name="email" type="email" placeholder="contoh: nama@gmail.com"
                            autocomplete="email"
                            class="input-field @error('email') border-red-500 @enderror"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Gunakan email valid, contoh: nama@gmail.com</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input id="phone" name="phone" type="tel" placeholder="Masukkan nomor HP"
                            autocomplete="tel"
                            class="input-field @error('phone') border-red-500 @enderror"
                            value="{{ old('phone') }}" required>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                            Address <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" name="address" placeholder="Masukkan alamat lengkap"
                            autocomplete="street-address"
                            class="input-field @error('address') border-red-500 @enderror"
                            rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input id="password" name="password" type="password"
                                placeholder="Masukkan password"
                                autocomplete="new-password"
                                class="input-field pr-10 @error('password') border-red-500 @enderror" required>
                            <button type="button" onclick="togglePassword('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-eye-slash text-gray-400" id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Minimal 8 karakter dan harus mengandung angka. Contoh: <span class="font-medium">bunga123</span></p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                placeholder="Ulangi password"
                                autocomplete="new-password"
                                class="input-field pr-10" required>
                            <button type="button" onclick="togglePassword('password_confirmation')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <i class="bi bi-eye-slash text-gray-400" id="password_confirmation-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full text-white py-4 px-6 rounded-xl bg-gradient-to-r from-purple-500 to-purple-800 font-medium">
                        Daftar Akun
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center text-secondary">
                Sudah Punya Akun?
                <a href="{{ route('login') }}" class="text-accent font-medium hover:text-purple-700">Login sekarang</a>
            </p>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script>
        // Clear autofill password saat halaman load
        window.addEventListener('load', function () {
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(inputId + '-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>
@endsection