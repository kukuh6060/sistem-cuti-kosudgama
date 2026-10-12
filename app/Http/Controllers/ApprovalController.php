<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\PartialLeaveRequest;
use App\Enums\LeaveStatus;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PengajuanStatusNotification;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // Ditambahkan untuk error handling

class ApprovalController extends Controller
{
    /**
     * [DIPULIHKAN] Menampilkan daftar approval untuk Kepala Unit
     */
    public function indexUnit()
    {
        $kepalaUnit = Auth::user();
        $unitUser = $kepalaUnit->unit;

        if (!$unitUser) {
            $requests = collect();
            $partialRequests = collect();
        } else {
            // Ambil Cuti Harian
            $requests = LeaveRequest::where('status', LeaveStatus::PENDING)
                ->whereHas('user', function ($query) use ($unitUser) {
                    $query->where('unit', $unitUser);
                })
                ->with('user.absenteeRecord') // Muat data absensi
                ->get();

            // Ambil Izin Meninggalkan Tempat
            $partialRequests = PartialLeaveRequest::where('status', LeaveStatus::PENDING)
                ->whereHas('user', function ($query) use ($unitUser) {
                    $query->where('unit', $unitUser);
                })
                ->with('user')
                ->get();
        }

        return view('approvals.unit_index', compact('requests', 'partialRequests'));
    }

    /**
     * [DIPULIHKAN] Menampilkan daftar approval untuk SDM
     */
    public function indexSdm()
    {
        $currentYear = now()->year;

        // Ambil Cuti Harian
        $requests = LeaveRequest::where('status', LeaveStatus::APPROVED_HEAD)
            ->with(['user' => function ($query) use ($currentYear) {
                $query->with(['leaveQuotas' => function ($q) use ($currentYear) {
                    $q->where('year', $currentYear);
                }, 'absenteeRecord']);
            }])
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil Izin Meninggalkan Tempat
        $partialRequests = PartialLeaveRequest::where('status', LeaveStatus::APPROVED_HEAD)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('approvals.sdm_index', compact('requests', 'partialRequests'));
    }

    /**
     * Halaman Approval Pengurus
     */
    public function indexPengurus()
    {
        $requests = LeaveRequest::where('status', LeaveStatus::APPROVED_SDM)
            ->with('user', 'approverUnit', 'approverSdm')
            ->orderBy('created_at', 'asc')
            ->get();

        $partialRequests = PartialLeaveRequest::where('status', LeaveStatus::APPROVED_SDM)
            ->with('user', 'approverUnit', 'approverSdm')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('approvals.pengurus_index', compact('requests', 'partialRequests'));
    }

    // --- Fungsi simpan TTD (Helper) ---
    private function saveSignature($signatureData, $prefix)
    {
        $signaturePath = null;
        if ($signatureData && !empty($signatureData)) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
            $fileName = 'signatures/' . $prefix . '_' . Auth::id() . '_' . time() . '.png';
            Storage::disk('public')->put($fileName, $imageData);
            $signaturePath = $fileName;
        }
        return $signaturePath;
    }


    // === CUTI HARIAN ===

    public function approveUnit(Request $request, $id)
    {
        $request->validate(['signature_data' => 'required|string']);
        $signaturePath = $this->saveSignature($request->signature_data, 'unit_leave');

        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => LeaveStatus::APPROVED_HEAD,
            'approved_by_head_id' => Auth::id(),
            'signature_path_unit' => $signaturePath,
        ]);

        try {
            $jenisPengajuan = $leaveRequest->leave_type === 'cuti'
                ? 'Cuti Harian'
                : 'Ijin Tidak Masuk';

            $leaveRequest->user->notify(
                new PengajuanStatusNotification(
                    'Pengajuan ' . $jenisPengajuan . ' Anda disetujui Kepala Unit (menunggu SDM).',
                    route('dashboard'),
                    'info'
                )
            );
            $sdmUsers = User::where('role', UserRole::SDM)->get();
            Notification::send(
                $sdmUsers,
                new PengajuanStatusNotification(
                    $jenisPengajuan . ' dari ' . $leaveRequest->user->name . ' butuh approval Anda.',
                    route('approval.sdm.index')
                )
            );
        } catch (\Exception $e) {
            Log::error('Notif error approveUnit: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan disetujui, menunggu approval SDM.');
    }

    public function rejectUnit(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|min:5']);
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => LeaveStatus::REJECTED,
            'approved_by_head_id' => Auth::id(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        try {
            $leaveRequest->user->notify(new PengajuanStatusNotification('Pengajuan Cuti Harian Anda DITOLAK oleh Kepala Unit.', route('dashboard'), 'danger'));
        } catch (\Exception $e) {
            Log::error('Notif error rejectUnit: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan ditolak dengan catatan.');
    }

    public function approveSdm(Request $request, $id)
    {
        $request->validate(['signature_data' => 'required|string']);
        $signaturePath = $this->saveSignature($request->signature_data, 'sdm_leave');

        $leaveRequest = LeaveRequest::findOrFail($id);

        $leaveRequest->update([
            'status' => LeaveStatus::APPROVED_SDM,
            'approved_by_hr_id' => Auth::id(),
            'signature_path_sdm' => $signaturePath,
        ]);

        try {
            $jenisPengajuan = $leaveRequest->leave_type === 'cuti'
                ? 'Cuti Harian'
                : 'Ijin Tidak Masuk';

            $leaveRequest->user->notify(
                new PengajuanStatusNotification(
                    'Pengajuan ' . $jenisPengajuan . ' Anda disetujui SDM (menunggu Pengurus).',
                    route('dashboard'),
                    'info'
                )
            );
            $pengurusUsers = User::where('role', UserRole::PENGURUS)->get();
        } catch (\Exception $e) {
            Log::error('Notif error approveSdm: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan disetujui, diteruskan ke Pengurus.');
    }

    public function rejectSdm(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|min:5']);
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => LeaveStatus::REJECTED,
            'approved_by_hr_id' => Auth::id(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        try {
            $leaveRequest->user->notify(new PengajuanStatusNotification('Pengajuan Cuti Harian Anda DITOLAK oleh SDM.', route('dashboard'), 'danger'));
        } catch (\Exception $e) {
            Log::error('Notif error rejectSdm: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan ditolak dengan catatan.');
    }

    public function approvePengurus(Request $request, $id)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== LeaveStatus::APPROVED_SDM) {
            return back()->withErrors([
                'error' => 'Pengajuan ini sudah diproses atau belum dapat disetujui oleh Pengurus.',
            ]);
        }

        $signaturePath = $this->saveSignature(
            $request->signature_data,
            'pengurus_leave'
        );

        if ($leaveRequest->leave_type === 'cuti') {
            $quota = $leaveRequest->user->getCurrentQuota();

            if (!$quota) {
                $quota = LeaveQuota::create([
                    'user_id' => $leaveRequest->user_id,
                    'year' => now()->year,
                    'total_quota' => $leaveRequest->user->getAnnualLeaveQuota(),
                    'used_quota' => 0,
                    'total_izin' => 3,
                    'used_izin' => 0,
                ]);
            }
            Notification::send(
                $pengurusUsers,
                new PengajuanStatusNotification(
                    $jenisPengajuan . ' dari ' . $leaveRequest->user->name . ' butuh approval final Anda.',
                    route('approval.pengurus.index')
                )
            );
            $sisaCuti = $quota->total_quota - $quota->used_quota;

            if ($leaveRequest->total_days > $sisaCuti) {
                return back()->withErrors([
                    'error' => 'Jatah cuti tidak mencukupi. Sisa cuti: ' . $sisaCuti . ' hari.',
                ]);
            }

            // Potong jatah cuti hanya setelah persetujuan final Pengurus
            $quota->increment('used_quota', $leaveRequest->total_days);
        }

        if ($leaveRequest->leave_type === 'ijin') {
            $quota = $leaveRequest->user->getCurrentQuota();

            if (!$quota) {
                $quota = LeaveQuota::create([
                    'user_id' => $leaveRequest->user_id,
                    'year' => now()->year,
                    'total_quota' => $leaveRequest->user->getAnnualLeaveQuota(),
                    'used_quota' => 0,
                    'total_izin' => 3,
                    'used_izin' => 0,
                ]);
            }

            $sisaIjin = $quota->total_izin - $quota->used_izin;

            if ($leaveRequest->total_days > $sisaIjin) {
                return back()->withErrors([
                    'error' => 'Jatah ijin tidak mencukupi. Sisa ijin: ' . $sisaIjin . ' hari.',
                ]);
            }

            // Potong jatah ijin hanya setelah persetujuan final Pengurus
            $quota->increment('used_izin', $leaveRequest->total_days);
        }

        $leaveRequest->update([
            'status' => LeaveStatus::APPROVED_HR,
            'approved_by_pengurus_id' => Auth::id(),
            'signature_path_pengurus' => $signaturePath,
        ]);

        try {
            $leaveRequest->user->notify(
                new PengajuanStatusNotification(
                    'Pengajuan ' . $jenisPengajuan . ' Anda telah DISETUJUI (Final).',
                    route('dashboard'),
                    'success'
                )
            );
        } catch (\Exception $e) {
            Log::error('Notif error approvePengurus: ' . $e->getMessage());
        }

        $jenisPengajuan = $leaveRequest->leave_type === 'cuti'
            ? 'cuti'
            : 'ijin';

        return back()->with(
            'success',
            'Pengajuan ' . $jenisPengajuan . ' disetujui (Final). Jatah ' . $jenisPengajuan . ' telah dipotong.'
        );
    }

    public function rejectPengurus(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string|min:5']);
        $leaveRequest = LeaveRequest::findOrFail($id);
        $leaveRequest->update([
            'status' => LeaveStatus::REJECTED,
            'approved_by_pengurus_id' => Auth::id(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        try {
            $leaveRequest->user->notify(new PengajuanStatusNotification('Pengajuan Cuti Harian Anda DITOLAK oleh Pengurus.', route('dashboard'), 'danger'));
        } catch (\Exception $e) {
            Log::error('Notif error rejectPengurus: ' . $e->getMessage());
        }

        return back()->with('success', 'Pengajuan ditolak dengan catatan.');
    }


    // === IZIN MENINGGALKAN TEMPAT ===

    public function approveUnitPartial(Request $request, $id)
{
    $request->validate([
        'signature_data' => 'required|string',
    ]);

    $partialRequest = PartialLeaveRequest::findOrFail($id);

    if ($partialRequest->status !== LeaveStatus::PENDING) {
        return back()->withErrors([
            'error' => 'Pengajuan Izin Meninggalkan Tempat ini sudah diproses atau tidak dapat disetujui pada tahap ini.',
        ]);
    }

    $signaturePath = $this->saveSignature(
        $request->signature_data,
        'unit_partial'
    );

    $partialRequest->update([
        'status' => LeaveStatus::APPROVED_HEAD,
        'approved_by_head_id' => Auth::id(),
        'signature_path_unit' => $signaturePath,
    ]);

    try {
        // Notifikasi kepada karyawan
        $partialRequest->user->notify(
            new PengajuanStatusNotification(
                'Izin Meninggalkan Tempat Anda disetujui Kepala Unit dan menunggu persetujuan SDM.',
                route('dashboard'),
                'info'
            )
        );

        // Notifikasi kepada SDM
        $sdmUsers = User::where('role', UserRole::SDM)->get();

        Notification::send(
            $sdmUsers,
            new PengajuanStatusNotification(
                'Izin Meninggalkan Tempat dari ' . $partialRequest->user->name . ' membutuhkan persetujuan Anda.',
                route('approval.sdm.index')
            )
        );
    } catch (\Exception $e) {
        Log::error(
            'Notif error approveUnitPartial: ' . $e->getMessage()
        );
    }

    return back()->with(
        'success',
        'Izin Meninggalkan Tempat disetujui Kepala Unit dan diteruskan ke SDM.'
    );
}

    public function rejectUnitPartial(Request $request, $id)
{
    $request->validate([
        'rejection_reason' => 'required|string|min:5',
    ]);

    $partialRequest = PartialLeaveRequest::findOrFail($id);

    if ($partialRequest->status !== LeaveStatus::PENDING) {
        return back()->withErrors([
            'error' => 'Pengajuan Izin Meninggalkan Tempat ini sudah diproses atau tidak dapat ditolak pada tahap ini.',
        ]);
    }

    $partialRequest->update([
        'status' => LeaveStatus::REJECTED,
        'approved_by_head_id' => Auth::id(),
        'rejection_reason' => $request->input('rejection_reason'),
    ]);

    try {
        $partialRequest->user->notify(
            new PengajuanStatusNotification(
                'Izin Meninggalkan Tempat Anda DITOLAK oleh Kepala Unit.',
                route('dashboard'),
                'danger'
            )
        );
    } catch (\Exception $e) {
        Log::error(
            'Notif error rejectUnitPartial: ' . $e->getMessage()
        );
    }

    return back()->with(
        'success',
        'Izin Meninggalkan Tempat ditolak oleh Kepala Unit.'
    );
}

    public function approveSdmPartial(Request $request, $id)
{
    $request->validate([
        'signature_data' => 'required|string',
    ]);

    $partialRequest = PartialLeaveRequest::findOrFail($id);

    if ($partialRequest->status !== LeaveStatus::APPROVED_HEAD) {
        return back()->withErrors([
            'error' => 'Pengajuan Izin Meninggalkan Tempat belum disetujui Kepala Unit atau sudah diproses.',
        ]);
    }

    $signaturePath = $this->saveSignature(
        $request->signature_data,
        'sdm_partial'
    );

    $partialRequest->update([
        'status' => LeaveStatus::APPROVED_SDM,
        'approved_by_hr_id' => Auth::id(),
        'signature_path_sdm' => $signaturePath,
    ]);

    try {
        // SDM adalah approval FINAL
        $partialRequest->user->notify(
            new PengajuanStatusNotification(
                'Izin Meninggalkan Tempat Anda telah DISETUJUI (Final) oleh SDM.',
                route('dashboard'),
                'success'
            )
        );
    } catch (\Exception $e) {
        Log::error(
            'Notif error approveSdmPartial: ' . $e->getMessage()
        );
    }

    return back()->with(
        'success',
        'Izin Meninggalkan Tempat disetujui SDM (Final).'
    );
}

    public function rejectSdmPartial(Request $request, $id)
{
    $request->validate([
        'rejection_reason' => 'required|string|min:5',
    ]);

    $partialRequest = PartialLeaveRequest::findOrFail($id);

    if ($partialRequest->status !== LeaveStatus::APPROVED_HEAD) {
        return back()->withErrors([
            'error' => 'Pengajuan Izin Meninggalkan Tempat belum disetujui Kepala Unit atau sudah diproses.',
        ]);
    }

    $partialRequest->update([
        'status' => LeaveStatus::REJECTED,
        'approved_by_hr_id' => Auth::id(),
        'rejection_reason' => $request->input('rejection_reason'),
    ]);

    try {
        $partialRequest->user->notify(
            new PengajuanStatusNotification(
                'Izin Meninggalkan Tempat Anda DITOLAK oleh SDM.',
                route('dashboard'),
                'danger'
            )
        );
    } catch (\Exception $e) {
        Log::error(
            'Notif error rejectSdmPartial: ' . $e->getMessage()
        );
    }

    return back()->with(
        'success',
        'Izin Meninggalkan Tempat ditolak oleh SDM.'
    );
}

    

}
