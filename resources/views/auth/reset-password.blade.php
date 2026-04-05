@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow">

        <h2 class="text-2xl font-bold mb-6 text-center">Buat Password Baru</h2>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <input type="email" name="email"
                class="w-full p-3 border rounded-lg mb-4"
                placeholder="Email" required>

            <input type="password" name="password"
                class="w-full p-3 border rounded-lg mb-4"
                placeholder="Password baru" required>

            <input type="password" name="password_confirmation"
                class="w-full p-3 border rounded-lg mb-4"
                placeholder="Konfirmasi password" required>

            <button type="submit"
                class="w-full bg-purple-600 text-white p-3 rounded-lg">
                Reset Password
            </button>
        </form>

    </div>
</div>
@endsection
