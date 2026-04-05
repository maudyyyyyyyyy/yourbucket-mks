@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow">

        <h2 class="text-2xl font-bold mb-6 text-center">Reset Password</h2>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <input type="email" name="email"
                class="w-full p-3 border rounded-lg mb-4"
                placeholder="Masukkan email anda" required>

            <button type="submit"
                class="w-full bg-purple-600 text-white p-3 rounded-lg">
                Kirim Link Reset
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-purple-600">Kembali ke Login</a>
        </div>

    </div>
</div>
@endsection
