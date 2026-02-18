<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')
            ->whereNotIn('role', ['su'])
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $current = Auth::user();

        if (!in_array($current->role, ['su', 'admin'])) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak untuk menambah pengguna.');
        }

        return view('users.create');
    }

    public function store(Request $request)
    {
        $current = Auth::user();

        if (!in_array($current->role, ['su', 'admin'])) {
            return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak untuk menambah pengguna.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,operator,tamu',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'role.required'      => 'Pilih peran pengguna.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($current->role === 'admin' && $validated['role'] === 'admin') {
            return back()->withInput()->with('error', 'Admin tidak dapat menambahkan akun Admin lain.');
        }

        if ($validated['role'] === 'su' && $current->role !== 'su') {
            return back()->withInput()->with('error', 'Anda tidak dapat membuat akun Superuser.');
        }

        try {
            User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'role'     => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('users.index')->with('success', 'Pengguna baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $current = Auth::user();

        if ($current->id !== $user->id) {
            if (!in_array($current->role, ['su', 'admin'])) {
                return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak akses.');
            }

            if ($current->role === 'admin' && in_array($user->role, ['admin', 'su'])) {
                return redirect()->route('users.index')->with('error', 'Admin tidak boleh mengubah akun sesama level atau di atasnya.');
            }
        }

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $current = Auth::user();

        try {
            $isSelf  = $current->id === $user->id;
            $isAdmin = in_array($current->role, ['su', 'admin']);

            if (!$isSelf && !$isAdmin) {
                return redirect()->route('users.index')->with('error', 'Anda tidak memiliki hak untuk mengubah pengguna ini.');
            }

            if ($current->role === 'admin' && in_array($user->role, ['admin', 'su']) && !$isSelf) {
                return redirect()->route('users.index')->with('error', 'Anda tidak dapat mengubah akun sesama level atau di atas Anda.');
            }

            $rules = [
                'name'     => 'required|string|max:100',
                'password' => 'nullable|min:6|confirmed',
            ];

            if ($isAdmin && !$isSelf) {
                $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($user->id)];
                $rules['role']  = 'required|in:admin,operator,tamu';
            }

            $validated = $request->validate($rules);

            $user->name = $validated['name'];

            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            if (isset($validated['role'])) {
                $user->role = $validated['role'];
            }

            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            if (!in_array(Auth::user()->role, ['su', 'admin'])) {
                return redirect()->route('users.index')->with('error', 'Akses ditolak.');
            }

            if ($user->id === Auth::id()) {
                return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }

            $user->delete();

            return back()->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pengguna.');
        }
    }
}
