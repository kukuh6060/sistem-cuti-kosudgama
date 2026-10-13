<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\PartialLeaveRequest;
use App\Models\PurchaseRequisition;
use App\Models\LeaveQuota;
use App\Models\Setting;
use App\Enums\UserRole;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User; // <-- Pastikan ini ada
use App\Notifications\PengajuanStatusNotification; // <-- Pastikan ini ada
use Illuminate\Support\Facades\Storage; // <-- INI ADALAH BARIS YANG HILANG

class LeaveRequestController extends Controller
{
    /**
     * Menampilkan Dashboard berdasarkan Role.
     */
    public function index()
    {
        $user = Auth::user();
        $myRequests = collect();
        $myPartialRequests = collect();
        $myPurchaseRequests = collect();
        $myQuota = null;

        // Ambil data catatan penting untuk SEMUA role
        $catatanPenting = Setting::where('key', 'catatan_penting_karyawan')->value('value');

        if ($user->role == UserRole::KARYAWAN) {

            $myRequests = $user->leaveRequests()->orderBy('created_at', 'desc')->get();
            $myPartialRequests = $user->partialLeaveRequests()->orderBy('created_at', 'desc')->get();
            $myPurchaseRequests = $user->purchaseRequisitions()->with('items')->orderBy('created_at', 'desc')->get();

            $myQuota = $user->getCurrentQuota();
            if (!$myQuota) {
                $jatahAwal = $user->getAnnualLeaveQuota();

                $myQuota = LeaveQuota::create([
                    'user_id' => $user->id,
                    'year' => now()->year,
                    'total_quota' => $jatahAwal,
                    'used_quota' => 0,
                ]);
            }
        }

        return view('dashboard', compact(
            'myRequests',
            'myQuota',
            'catatanPenting',
            'myPartialRequests',
            'myPurchaseRequests'
        ));
    }

    /**
     * Menampilkan form pengajuan cuti
     */
    public function create()
    {
        return view('cuti.create');
    }

    /**
     * Menyimpan pengajuan cuti baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'leave_type' => ['required', 'string', 'in:cuti,ijin'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:10'],
            'signature_mode' => ['required', 'string', 'in:draw,upload'],
            'signature_data' => ['required_if:signature_mode,draw', 'nullable', 'string'],
            'signature_file' => ['required_if:signature_mode,upload', 'nullable', 'image', 'mimes:png', 'max:2048'],
        ]);

        $myQuota = $user->getCurrentQuota();

        if (!$myQuota) {
            $myQuota = LeaveQuota::create([
                'user_id' => $user->id,
                'year' => now()->year,
                'total_quota' => $user->getAnnualLeaveQuota(),
                'used_quota' => 0,
            ]);
        }


        $period = CarbonPeriod::create($request->start_date, $request->end_date);
        $totalDays = 0;

        foreach ($period as $date) {
            // Minggu tidak dihitung sebagai hari cuti
            if ($date->isSunday()) {
                continue;
            }

            // Libur nasional dan cuti bersama tidak dihitung
            $isHoliday = \App\Models\Holiday::whereDate('date', $date->toDateString())
                ->exists();

            if ($isHoliday) {
                continue;
            }

            // Senin-Sabtu dihitung
            $totalDays++;
        }
        // Maksimal pengajuan cuti adalah 3 hari
        if ($request->leave_type === 'cuti' && $totalDays > 3) {
            return back()->withErrors([
                'error' => 'Pengajuan cuti maksimal 3 hari dalam satu pengajuan.',
            ])->withInput();
        }
        if ($request->leave_type == 'cuti') {
            $sisaCuti = $myQuota->total_quota - $myQuota->used_quota;
            if ($totalDays > $sisaCuti) {
                return back()->withErrors(['error' => 'Jatah cuti Anda tidak mencukupi. Sisa cuti: ' . $sisaCuti . ' hari.']);
            }
        }

        // Aturan Ijin Tidak Masuk
        if ($request->leave_type === 'ijin') {
            $sisaCuti = $myQuota->total_quota - $myQuota->used_quota;
            $sisaIjin = $myQuota->total_izin - $myQuota->used_izin;

            // Ijin hanya boleh diajukan jika jatah cuti sudah habis
            if ($sisaCuti > 0) {
                return back()->withErrors([
                    'error' => 'Ijin tidak masuk hanya dapat diajukan jika jatah cuti Anda sudah habis.',
                ])->withInput();
            }

            // Jumlah hari ijin tidak boleh melebihi sisa jatah ijin
            if ($totalDays > $sisaIjin) {
                return back()->withErrors([
                    'error' => 'Jatah ijin Anda tidak mencukupi. Sisa ijin: ' . $sisaIjin . ' hari.',
                ])->withInput();
            }
        }

        // --- Proses Tanda Tangan ---
        $signaturePath = null;
        if ($request->signature_mode == 'draw' && $request->has('signature_data') && !empty($request->signature_data)) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data));
            $fileName = 'signatures/leave_' . $user->id . '_' . time() . '.png';
            Storage::disk('public')->put($fileName, $imageData); // Ini sekarang akan berfungsi
            $signaturePath = $fileName;
        } elseif ($request->signature_mode == 'upload' && $request->hasFile('signature_file')) {
            $file = $request->file('signature_file');

            // Buka file sementara menggunakan pathname langsung.
            $stream = fopen($file->getPathname(), 'rb');

            if ($stream === false) {
                return back()
                    ->withErrors([
                        'signature_file' => 'File tanda tangan tidak dapat dibaca.',
                    ])
                    ->withInput();
            }

            // Nama file unik untuk menghindari bentrok nama.
            $fileName = 'signatures/' . bin2hex(random_bytes(16)) . '.png';

            try {
                $saved = Storage::disk('public')->put($fileName, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (!$saved) {
                return back()
                    ->withErrors([
                        'signature_file' => 'Gagal menyimpan file tanda tangan.',
                    ])
                    ->withInput();
            }

            $signaturePath = $fileName;
        }

        // Jika lolos, simpan data
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending', // Status awal
            'signature_path' => $signaturePath,
        ]);

        // Kirim Notifikasi ke Kepala Unit
        try {
            $kepalaUnit = User::where('role', UserRole::KEPALA_UNIT)
                ->where('unit', $user->unit)
                ->first();

            if ($kepalaUnit) {
                $jenisPengajuan = $request->leave_type === 'cuti'
                    ? 'Cuti Harian'
                    : 'Ijin Tidak Masuk';

                $kepalaUnit->notify(new PengajuanStatusNotification(
                    'Pengajuan ' . $jenisPengajuan . ' baru dari ' . $user->name,
                    route('approval.unit.index')
                ));
            }
        } catch (\Exception $e) {
            // Tangani error jika notifikasi gagal (opsional)
        }

        return redirect()->route('dashboard')->with(
            'success',
            'Pengajuan ' . ($request->leave_type === 'cuti' ? 'cuti' : 'ijin tidak masuk') . ' berhasil dikirim.'
        );
    }
}
