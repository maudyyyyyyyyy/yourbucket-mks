<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:255'],
                'email'    => [
                    'required',
                    'string',
                    'email:rfc,dns',   // ✅ validasi format email ketat + cek DNS domain
                    'max:255',
                    'unique:users'
                ],
                'phone'    => ['required', 'string', 'max:20'],
                'address'  => ['required', 'string'],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)          // ✅ minimal 8 karakter
                        ->numbers()           // ✅ harus ada angka
                        ->uncompromised(),    // ✅ cek apakah password pernah bocor
                ],
            ], [
                'email.unique'       => 'Email sudah terdaftar.',
                'email.email'        => 'Format email tidak valid. Contoh: nama@gmail.com',
                'password.min'       => 'Password minimal 8 karakter.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);

            DB::beginTransaction();

            try {
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'phone'    => $validated['phone'],
                    'address'  => $validated['address'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'user',
                ]);

                event(new Registered($user));

                Auth::login($user);

                DB::commit();

                return redirect()->route('home')
                    ->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $user->name);

            } catch (QueryException $e) {
                DB::rollBack();
                Log::error('Database error during registration: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'Gagal membuat akun. Silakan coba lagi.')
                    ->withInput($request->except('password', 'password_confirmation'));
            }

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->except('password', 'password_confirmation'));

        } catch (Exception $e) {
            Log::error('Error during registration: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.')
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                $user       = Auth::user();
                $redirectTo = $user->isAdmin() ? route('admin.dashboard') : route('home');

                return redirect()->intended($redirectTo)
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
            }

            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->except('password'));

        } catch (Exception $e) {
            Log::error('Error during login: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan. Silakan coba lagi.')
                ->withInput($request->except('password'));
        }
    }

    public function logout(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')
                ->with('success', 'Anda berhasil logout.');

        } catch (Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal logout. Silakan coba lagi.');
        }
    }
}