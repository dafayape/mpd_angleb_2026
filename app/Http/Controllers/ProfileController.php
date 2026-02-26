<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman edit profile
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update data profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:' . config('auth.password_min_length', 6) . '|confirmed',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];

        $validated = $request->validate($rules, [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah digunakan pengguna lain.',
            'password.min'       => 'Password minimal ' . config('auth.password_min_length', 6) . ' karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        try {
            $user->name  = $validated['name'];
            $user->email = $validated['email'];

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            // Catat log aktivitas
            ActivityLog::log('Update Profile', $user->name, 'Success', 'User memperbarui profilnya sendiri');

            return back()->with('success', 'Profil berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }
}
