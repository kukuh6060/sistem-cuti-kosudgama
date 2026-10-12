<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LeaveQuota;
use App\Enums\UserRole;

class UserController extends Controller
{
    /**
     * Menampilkan daftar karyawan di unit Kepala Unit.
     */
    public function indexUnit() // <-- TYPO DIPERBAIKI
    {
        $kepalaUnit = Auth::user();
        $unit = $kepalaUnit->unit;

        if (!$unit) {
            $karyawan = collect(); // Kosong jika tidak punya unit
        } else {
            // Cari semua user dengan role KARYAWAN di unit yg SAMA
            $karyawan = User::where('role', UserRole::KARYAWAN)
                ->where('unit', $unit)
                ->orderBy('name', 'asc')
                ->get();
        }

        // Kita kirim nama unit juga untuk judul halaman
        $namaUnit = $unit ? str_replace('_', ' ', $unit->name) : 'Anda';

        return view('management.unit_index', compact('karyawan', 'namaUnit'));
    }

    /**
     * Menampilkan SEMUA karyawan untuk SDM dan Pengurus.
     */
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
    
}
