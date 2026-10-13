<?php

namespace App\Http\Controllers;

use App\Notifications\PengajuanStatusNotification;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Models\PartialLeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Pastikan ini ada

class PartialLeaveController extends Controller
{
    /**
     * Menampilkan formulir Izin Meninggalkan Tempat Kerja.
     */
    public function create()
    {
        return view('partial_leave.create');
    }

    /**
     * Menyimpan pengajuan izin baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'leave_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose_category' => ['required', 'string'],
            'purpose_other' => ['required_if:purpose_category,lain', 'nullable', 'string', 'max:500'],
            'signature_mode' => ['required', 'string', 'in:draw,upload'],
            'signature_data' => ['required_if:signature_mode,draw', 'nullable', 'string'],
            'signature_file' => ['required_if:signature_mode,upload', 'nullable', 'image', 'mimes:png', 'max:2048'],
        ]);

        // --- Proses Tanda Tangan ---
        $signaturePath = null;
        if ($request->signature_mode == 'draw' && $request->has('signature_data') && !empty($request->signature_data)) {

            // === INI ADALAH PERBAIKANNYA (base6acyy -> base64) ===
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data));

            $fileName = 'signatures/partial_' . $user->id . '_' . time() . '.png';
            Storage::disk('public')->put($fileName, $imageData);
            $signaturePath = $fileName;
        } elseif ($request->signature_mode == 'upload') {
            if (!$request->hasFile('signature_file')) {
                return back()
                    ->withErrors([
                        'signature_file' => 'Silakan pilih file tanda tangan terlebih dahulu.',
                    ])
                    ->withInput();
            }

            $file = $request->file('signature_file');
            $stream = fopen($file->getPathname(), 'rb');

            if ($stream === false) {
                return back()
                    ->withErrors([
                        'signature_file' => 'File tanda tangan tidak dapat dibaca.',
                    ])
                    ->withInput();
            }

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

        // --- Simpan Data ---
        PartialLeaveRequest::create([
            'user_id' => $user->id,
            'leave_date' => $request->leave_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose_category' => $request->purpose_category,
            'purpose_other' => $request->purpose_other,
            'status' => 'pending',
            'signature_path' => $signaturePath,
        ]);

        // === NOTIFIKASI BARU SAAT MENGAJUKAN ===
        try {
            // 1. Cari Kepala Unit yang sesuai
            $kepalaUnit = User::where('role', UserRole::KEPALA_UNIT)
                ->where('unit', $user->unit)
                ->first();

            // 2. Kirim notifikasi ke Kepala Unit (jika ada)
            if ($kepalaUnit) {
                $kepalaUnit->notify(new PengajuanStatusNotification(
                    'Izin Meninggalkan Tempat baru dari ' . $user->name,
                    route('approval.unit.index')
                ));
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi pengajuan izin tempat: ' . $e->getMessage());
        }
        // ======================================

        return redirect()->route('dashboard')->with('success', 'Pengajuan Izin Meninggalkan Tempat Kerja berhasil dikirim.');
    }
}
