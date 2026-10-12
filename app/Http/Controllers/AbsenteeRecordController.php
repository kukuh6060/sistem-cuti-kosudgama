<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AbsenteeRecord;
use App\Models\LeaveQuota;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB; // Tambahan DB facade

class AbsenteeRecordController extends Controller
{
    /**
     * Menampilkan daftar semua karyawan untuk SDM.
     */
    public function index()
    {
        $karyawan = User::where('role', UserRole::KARYAWAN)
            ->orderBy('name', 'asc')
            ->get();

        return view('absentee.index', compact('karyawan'));
    }

    /**
     * [FUNGSI BARU] Menampilkan rekap data absensi per tahun.
     */
    public function recap(Request $request)
    {
        $selectedYear = $request->input('year', now()->year);

        // Ambil SEMUA data absensi untuk tahun yang dipilih
        $records = AbsenteeRecord::where('year', $selectedYear)
            ->with('user') // Eager load data user
            ->get()
            ->filter(function ($record) {
                // Filter untuk memastikan user masih ada
                return $record->user;
            });

        // Ambil daftar tahun yang tersedia
        $years = AbsenteeRecord::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('absentee.recap', compact('records', 'selectedYear', 'years'));
    }

    /**
     * [METODE BARU] Menampilkan preview data absensi 1 karyawan.
     */
    public function show(User $user)
    {
        $currentYear = now()->year;

        // Cari record absensi tahun ini.
        $record = AbsenteeRecord::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->first();

        // Jika data absensi belum ada sama sekali,
        // "preview" tidak ada gunanya, jadi kita langsung arahkan
        // ke halaman 'edit' agar SDM bisa mengisinya.
        if (!$record) {
            return redirect()->route('absentee.edit', $user)->with('status', 'Data absensi untuk ' . $user->name . ' belum ada. Silakan isi data baru.');
        }

        // Jika data ada, tampilkan halaman 'show' (pratinjau)
        return view('absentee.show', compact('user', 'record'));
    }

    /**
     * Menampilkan formulir absensi untuk 1 karyawan.
     */
    public function edit(User $user)
    {
        $currentYear = now()->year;

        // Cari record absensi tahun ini, atau buat baru jika belum ada
        $record = AbsenteeRecord::firstOrCreate(
            ['user_id' => $user->id, 'year' => $currentYear]
        );

        // Ambil data JATAH CUTI yang SEBENARNYA dari tabel leave_quotas
        $quota = LeaveQuota::firstOrCreate(
            ['user_id' => $user->id, 'year' => $currentYear],
            [
                'total_quota' => 12,
                'used_quota' => 0,
                'total_izin' => 3,
                'used_izin' => 0,
            ]
        );

        // Masukkan data sisa cuti yang asli ke form
        $record->sisa_cuti_input = $quota->total_quota - $quota->used_quota;
        // (Kita tidak punya sistem jatah ijin, jadi biarkan 0 atau dari input manual)
        $record->sisa_ijin_input = $record->sisa_ijin_input ?? 0;

        return view('absentee.edit', compact('user', 'record'));
    }

    /**
     * Update data absensi dan sinkronkan sisa cuti.
     */
    public function update(Request $request, User $user)
    {
        $currentYear = now()->year;

        // 1. Validasi semua input
        $validated = $request->validate([
            'sakit' => 'required|integer|min:0',
            'tanpa_kabar' => 'required|integer|min:0',
            'ijin_meninggalkan_tempat' => 'required|integer|min:0',
            'terlambat' => 'required|integer|min:0',
            'setengah_hari' => 'required|integer|min:0',
            'pulang_awal' => 'required|integer|min:0',
            'ijin_lebih_1_jam' => 'required|integer|min:0',
            'ijin_kurang_1_jam' => 'required|integer|min:0',
            'sisa_cuti_input' => 'required|integer|min:0',
            'sisa_ijin_input' => 'required|integer|min:0',
        ]);

        // 2. Ambil data absensi tahun berjalan
        $record = AbsenteeRecord::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->firstOrFail();

        // 3. Simpan data absensi
        $record->update($validated);

        // 4. Ambil quota cuti tahun berjalan
        $quota = LeaveQuota::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => $currentYear,
            ],
            [
                'total_quota' => 12,
                'used_quota' => 0,
                'total_izin' => 3,
                'used_izin' => 0,
            ]
        );

        // 5. Hitung total jatah cuti baru
        //
        // used_quota = cuti yang sudah benar-benar digunakan
        // sisa_cuti_input = sisa cuti yang ditentukan SDM
        //
        // Contoh:
        // used_quota = 2
        // sisa_cuti_input = 9
        // total_quota = 2 + 9 = 11
        //
        // Dengan demikian used_quota tidak pernah di-reset.
        $totalQuotaBaru = $quota->used_quota + $validated['sisa_cuti_input'];

        // 6. Update quota cuti
        $quota->update([
            'total_quota' => $totalQuotaBaru,
        ]);

        return redirect()
            ->route('absentee.index')
            ->with(
                'success',
                'Data absensi untuk ' . $user->name . ' berhasil diperbarui.'
            );
    }

    /**
     * [FUNGSI DIPERBARUI] Reset data "Tidak Masuk Bekerja" DAN "Masuk Tidak Penuh".
     */
    public function resetAbsenceSection(User $user)
    {
        $currentYear = now()->year;

        // Cari record absensi tahun ini
        $record = AbsenteeRecord::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->first();

        if ($record) {
            // Update SEMUA 8 field rekap bulanan ke 0
            $record->update([
                // Bagian 1
                'sakit' => 0,
                'tanpa_kabar' => 0,
                'ijin_meninggalkan_tempat' => 0,

                // Bagian 2 (Tambahan)
                'terlambat' => 0,
                'setengah_hari' => 0,
                'pulang_awal' => 0,
                'ijin_lebih_1_jam' => 0,
                'ijin_kurang_1_jam' => 0,
            ]);

            return redirect()->route('absentee.edit', $user)
                ->with('success', 'Data rekap absensi bulanan telah di-reset ke 0.');
        }

        return redirect()->route('absentee.edit', $user)
            ->with('error', 'Data record tidak ditemukan.');
    }
}
