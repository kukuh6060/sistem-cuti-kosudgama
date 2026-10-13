<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function indexUnit()
    {
        $kepalaUnit = Auth::user();
        $unit = $kepalaUnit->unit;

        if (!$unit) {
            $karyawan = collect();
        } else {
            $karyawan = User::where('role', UserRole::KARYAWAN)
                ->where('unit', $unit)
                ->orderBy('name', 'asc')
                ->get();
        }

        $namaUnit = $unit
            ? str_replace('_', ' ', $unit->name)
            : 'Anda';

        return view('management.unit_index', compact(
            'karyawan',
            'namaUnit'
        ));
    }

    public function indexAll()
    {
        $tahun = now()->year;

        $karyawan = User::where('role', UserRole::KARYAWAN)
            ->with(['leaveQuotas' => function ($query) use ($tahun) {
                $query->where('year', $tahun);
            }])
            ->orderBy('unit', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return view('management.all_index', compact('karyawan'));
    }

    /**
     * Form tambah akun karyawan.
     */
    public function create()
    {
        $units = UserUnit::cases();

        return view('management.users.create', compact('units'));
    }

    /**
     * Simpan akun karyawan baru.
     */
    public function store(Request $request)
    {
        $unitValues = array_map(
            fn(UserUnit $unit) => $unit->value,
            UserUnit::cases()
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'unit' => [
                'required',
                Rule::in($unitValues),
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'unit' => $validated['unit'],
            'role' => UserRole::KARYAWAN,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()
            ->route('management.all.index')
            ->with('success', 'Akun karyawan berhasil dibuat.');
    }

    /**
     * Form edit akun karyawan.
     */
    public function edit(User $user)
    {
        abort_unless(
            $user->role === UserRole::KARYAWAN,
            404
        );

        $units = UserUnit::cases();

        return view('management.users.edit', compact(
            'user',
            'units'
        ));
    }

    /**
     * Perbarui data akun karyawan.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(
            $user->role === UserRole::KARYAWAN,
            404
        );

        $unitValues = array_map(
            fn(UserUnit $unit) => $unit->value,
            UserUnit::cases()
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'unit' => [
                'required',
                Rule::in($unitValues),
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->unit = $validated['unit'];

        // Email akun yang dikelola SDM dianggap sudah terverifikasi.
        $user->email_verified_at = $user->email_verified_at ?? now();

        $user->save();

        return redirect()
            ->route('management.all.index')
            ->with('success', 'Data akun karyawan berhasil diperbarui.');
    }

    /**
     * Atur ulang kata sandi karyawan.
     */
    public function resetPassword(Request $request, User $user)
    {
        abort_unless(
            $user->role === UserRole::KARYAWAN,
            404
        );

        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('management.all.index')
            ->with('success', 'Kata sandi karyawan berhasil diatur ulang.');
    }
    public function updateStatus(Request $request, User $user)
    {
        abort_unless($user->role === UserRole::KARYAWAN, 404);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user->is_active = $validated['is_active'];
        $user->save();

        $pesan = $user->is_active
            ? 'Akun karyawan berhasil diaktifkan kembali.'
            : 'Akun karyawan berhasil dinonaktifkan.';

        return redirect()
            ->route('management.all.index')
            ->with('success', $pesan);
    }
    /**
     * Daftar akun Kepala Unit, SDM, dan Pengurus.
     */
    public function indexManagers()
    {
        $roles = [
            UserRole::KEPALA_UNIT,
            UserRole::SDM,
            UserRole::PENGURUS,
        ];

        $pengelola = User::whereIn(
            'role',
            array_map(fn($role) => $role->value, $roles)
        )
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return view('management.pengelola.index', compact('pengelola'));
    }

    /**
     * Form tambah akun pengelola.
     */
    public function createManager()
    {
        $roles = [
            UserRole::KEPALA_UNIT,
            UserRole::SDM,
            UserRole::PENGURUS,
        ];

        $units = UserUnit::cases();

        return view('management.pengelola.create', compact('roles', 'units'));
    }

    /**
     * Simpan akun pengelola baru.
     */
    public function storeManager(Request $request)
    {
        $roleValues = [
            UserRole::KEPALA_UNIT->value,
            UserRole::SDM->value,
            UserRole::PENGURUS->value,
        ];

        $unitValues = array_map(
            fn($unit) => $unit->value,
            UserUnit::cases()
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($roleValues)],
            'unit' => ['required', Rule::in($unitValues)],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'unit' => $validated['unit'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return redirect()
            ->route('management.managers.index')
            ->with('success', 'Akun pengelola berhasil ditambahkan.');
    }

    /**
     * Form edit akun pengelola.
     */
    public function editManager(User $user)
    {
        abort_unless(
            in_array($user->role, [
                UserRole::KEPALA_UNIT,
                UserRole::SDM,
                UserRole::PENGURUS,
            ], true),
            404
        );

        $roles = [
            UserRole::KEPALA_UNIT,
            UserRole::SDM,
            UserRole::PENGURUS,
        ];

        $units = UserUnit::cases();

        return view(
            'management.pengelola.edit',
            compact('user', 'roles', 'units')
        );
    }

    /**
     * Simpan perubahan akun pengelola.
     */
    public function updateManager(Request $request, User $user)
    {
        abort_unless(
            in_array($user->role, [
                UserRole::KEPALA_UNIT,
                UserRole::SDM,
                UserRole::PENGURUS,
            ], true),
            404
        );

        $roleValues = [
            UserRole::KEPALA_UNIT->value,
            UserRole::SDM->value,
            UserRole::PENGURUS->value,
        ];

        $unitValues = array_map(
            fn($unit) => $unit->value,
            UserUnit::cases()
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in($roleValues)],
            'unit' => ['required', Rule::in($unitValues)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->unit = $validated['unit'];
        $user->email_verified_at = $user->email_verified_at ?? now();

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('management.managers.index')
            ->with('success', 'Akun pengelola berhasil diperbarui.');
    }
}
