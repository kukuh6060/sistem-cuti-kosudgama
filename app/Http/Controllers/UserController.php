<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Enums\UserUnit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Role yang dapat dikelola melalui Data Karyawan.
     */
    private function allowedRoles(): array
    {
        return [
            UserRole::KARYAWAN,
            UserRole::WAKIL_KEPALA_UNIT,
            UserRole::KEPALA_UNIT,
            UserRole::SDM,
        ];
    }

    /**
     * Daftar karyawan dalam unit Kepala/Wakil Kepala Unit.
     */
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

    /**
     * Menampilkan seluruh akun dalam satu halaman.
     */
    public function indexAll(Request $request)
    {
        $tahun = now()->year;

        $sort = $request->query('sort', 'unit');
        $direction = $request->query('direction', 'asc');

        $allowedSorts = ['name', 'unit', 'role', 'is_active'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'unit';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = User::whereIn('role', $this->allowedRoles())
            ->with(['leaveQuotas' => function ($query) use ($tahun) {
                $query->where('year', $tahun);
            }]);

        if ($sort === 'is_active') {
            $query->orderByRaw(
                'CASE WHEN is_active = 1 THEN 0 ELSE 1 END ' . $direction
            )->orderBy('name', 'asc');
        } else {
            $query->orderBy($sort, $direction)
                ->orderBy('name', 'asc');
        }

        $karyawan = $query->get();

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
     * Menyimpan akun karyawan baru.
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
            'is_active' => true,
        ]);

        return redirect()
            ->route('management.all.index')
            ->with('success', 'Akun karyawan berhasil dibuat.');
    }

    /**
     * Form edit akun.
     */
    public function edit(User $user)
    {
        abort_unless(
            in_array($user->role, $this->allowedRoles(), true),
            404
        );

        $units = UserUnit::cases();
        $roles = $this->allowedRoles();

        return view('management.users.edit', compact(
            'user',
            'units',
            'roles'
        ));
    }

    /**
     * Memperbarui data dan jabatan akun.
     */
    public function update(Request $request, User $user)
    {
        abort_unless(
            in_array($user->role, $this->allowedRoles(), true),
            404
        );

        $roleValues = array_map(
            fn(UserRole $role) => $role->value,
            $this->allowedRoles()
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
            'role' => [
                'required',
                Rule::in($roleValues),
            ],
            'unit' => [
                'required',
                Rule::in($unitValues),
            ],
        ]);

        // Lindungi akun SDM terakhir yang masih aktif.
        if (
            $user->role === UserRole::SDM &&
            $user->is_active &&
            $validated['role'] !== UserRole::SDM->value
        ) {
            $jumlahSdmAktif = User::where('role', UserRole::SDM->value)
                ->where('is_active', true)
                ->count();

            if ($jumlahSdmAktif <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'Role SDM tidak dapat diubah karena ini adalah akun SDM aktif terakhir.',
                ]);
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->unit = $validated['unit'];
        $user->role = $validated['role'];
        $user->email_verified_at = $user->email_verified_at ?? now();

        $user->save();

        return redirect()
            ->route('management.all.index')
            ->with('success', 'Data akun berhasil diperbarui.');
    }

    /**
     * Reset kata sandi akun.
     */
    public function resetPassword(Request $request, User $user)
    {
        abort_unless(
            in_array($user->role, $this->allowedRoles(), true),
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
            ->with('success', 'Kata sandi akun berhasil diatur ulang.');
    }

    /**
     * Mengaktifkan atau menonaktifkan akun.
     */
    public function updateStatus(Request $request, User $user)
    {
        abort_unless(
            in_array($user->role, $this->allowedRoles(), true),
            404
        );

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        // Lindungi akun SDM aktif terakhir.
        if (
            $user->role === UserRole::SDM &&
            $user->is_active &&
            !$validated['is_active']
        ) {
            $jumlahSdmAktif = User::where('role', UserRole::SDM->value)
                ->where('is_active', true)
                ->count();

            if ($jumlahSdmAktif <= 1) {
                throw ValidationException::withMessages([
                    'is_active' => 'Akun SDM aktif terakhir tidak dapat dinonaktifkan.',
                ]);
            }
        }

        $user->is_active = $validated['is_active'];
        $user->save();

        $pesan = $user->is_active
            ? 'Akun berhasil diaktifkan kembali.'
            : 'Akun berhasil dinonaktifkan.';

        return redirect()
            ->route('management.all.index')
            ->with('success', $pesan);
    }
}
