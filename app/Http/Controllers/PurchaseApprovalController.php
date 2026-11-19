<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Enums\UserRole;
use App\Notifications\PengajuanStatusNotification;
use Illuminate\Support\Facades\Storage; // <-- PASTIKAN INI ADA

class PurchaseApprovalController extends Controller
{
    // --- FUNGSI UNTUK MENYIMPAN TTD ---
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

    // --- LANGKAH 1: KEPALA UNIT ---
    public function indexUnit()
    {
        $unit = Auth::user()->unit;
        $requests = PurchaseRequisition::where('status', 'pending_unit')
            ->whereHas('user', function ($query) use ($unit) {
                $query->where('unit', $unit);
            })
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchase_approval.unit_index', compact('requests'));
    }

    public function approveUnit(Request $request, $id)
    {
        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        if ($requisition->status !== 'pending_unit') {
            return back()->with(
                'error',
                'Pengajuan ini sudah diproses atau bukan tahap persetujuan unit.'
            );
        }

        if (
            !$requisition->user ||
            $requisition->user->unit !== Auth::user()->unit
        ) {
            abort(403, 'Anda tidak berwenang memproses pengajuan dari unit lain.');
        }

        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $jabatan = Auth::user()->role === UserRole::WAKIL_KEPALA_UNIT
            ? 'Wakil Kepala Unit'
            : 'Kepala Unit';

        $signaturePath = $this->saveSignature(
            $request->signature_data,
            'purchase_unit'
        );

        $requisition->update([
            'status' => 'pending_sdm',
            'approved_by_unit_id' => Auth::id(),
            'signature_path_unit' => $signaturePath,
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda disetujui ' . $jabatan . ' (menunggu SDM).',
                    route('dashboard'),
                    'info'
                )
            );

            $sdmUsers = User::where('role', UserRole::SDM)->get();

            Notification::send(
                $sdmUsers,
                new PengajuanStatusNotification(
                    'Permohonan Pembelian dari ' . $requisition->user->name . ' disetujui ' . $jabatan . ' dan membutuhkan persetujuan Anda.',
                    route('purchase.approval.sdm.index')
                )
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Notif error approveUnit purchase: ' . $e->getMessage()
            );
        }

        return back()->with(
            'success',
            'Permohonan disetujui oleh ' . $jabatan . ', diteruskan ke SDM.'
        );
    }

    // --- LANGKAH 2: SDM (Mengisi Nomor) ---
    public function indexSdm()
    {
        $requests = PurchaseRequisition::where('status', 'pending_sdm')
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        $finalRequests = PurchaseRequisition::where('status', 'pending_final')
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view(
            'purchase_approval.sdm_index',
            compact('requests', 'finalRequests')
        );
    }
    public function indexSdmFinal()
    {
        $requests = PurchaseRequisition::where('status', 'pending_final')
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchase_approval.sdm_final_index', compact('requests'));
    }

    public function approveSdm(Request $request, $id)
    {
        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        if ($requisition->status !== 'pending_sdm') {
            return back()->with(
                'error',
                'Pengajuan ini sudah diproses atau bukan tahap SDM.'
            );
        }

        $validated = $request->validate([
            'requisition_number' => 'required|string|max:100',
            'signature_data' => 'required|string',
        ]);

        $signaturePath = $this->saveSignature(
            $validated['signature_data'],
            'purchase_sdm'
        );

        $requisition->update([
            'status' => 'pending_final',
            'requisition_number' => $validated['requisition_number'],
            'approved_by_sdm_id' => Auth::id(),
            'signature_path_sdm' => $signaturePath,
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda telah disetujui awal oleh SDM. Menunggu tanda tangan Pengurus dan persetujuan final SDM.',
                    route('dashboard'),
                    'info'
                )
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Notif error approveSdm purchase: ' . $e->getMessage()
            );
        }

        return back()->with(
            'success',
            'Persetujuan awal berhasil. Silakan cetak formulir untuk ditandatangani Pengurus.'
        );
    }
    public function approveFinal($id)
    {
        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        if ($requisition->status !== 'pending_final') {
            return back()->with(
                'error',
                'Pengajuan ini belum menunggu persetujuan final atau sudah diproses.'
            );
        }

        $requisition->update([
            'status' => 'approved',
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda telah DISETUJUI secara final oleh SDM.',
                    route('dashboard'),
                    'success'
                )
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Notif error approveFinal purchase: ' . $e->getMessage()
            );
        }

        return back()->with(
            'success',
            'Permohonan Pembelian berhasil disetujui secara final.'
        );
    }
    // --- LANGKAH 3: PENGURUS (Approval Final) ---
    public function indexPengurus()
    {
        $requests = PurchaseRequisition::where('status', 'pending_pengurus')
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchase_approval.pengurus_index', compact('requests'));
    }

    public function approvePengurus(Request $request, $id)
    {
        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        if ($requisition->status !== 'pending_pengurus') {
            return back()->with('error', 'Pengajuan ini sudah diproses atau bukan tahap Pengurus.');
        }

        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $signaturePath = $this->saveSignature(
            $request->signature_data,
            'purchase_pengurus'
        );

        $requisition->update([
            'status' => 'approved',
            'approved_by_pengurus_id' => Auth::id(),
            'signature_path_pengurus' => $signaturePath,
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda telah DISETUJUI (Final).',
                    route('dashboard'),
                    'success'
                )
            );
        } catch (\Exception $e) {
            // Kegagalan notifikasi tidak membatalkan persetujuan.
        }

        return back()->with(
            'success',
            'Permohonan disetujui (Final).'
        );
    }

    // --- FUNGSI REJECT (Berlaku untuk semua) ---
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        $role = Auth::user()->role;

        if (
            $role === UserRole::KEPALA_UNIT ||
            $role === UserRole::WAKIL_KEPALA_UNIT
        ) {
            $expectedStatus = 'pending_unit';

            if (
                !$requisition->user ||
                $requisition->user->unit !== Auth::user()->unit
            ) {
                abort(403, 'Anda tidak berwenang menolak pengajuan dari unit lain.');
            }

            $jabatan = $role === UserRole::WAKIL_KEPALA_UNIT
                ? 'Wakil Kepala Unit'
                : 'Kepala Unit';
        } elseif ($role === UserRole::SDM) {
            $expectedStatus = 'pending_sdm';
            $jabatan = 'SDM';
        } elseif ($role === UserRole::PENGURUS) {
            $expectedStatus = 'pending_pengurus';
            $jabatan = 'Pengurus';
        } else {
            abort(403, 'Anda tidak berwenang menolak pengajuan ini.');
        }

        if ($requisition->status !== $expectedStatus) {
            return back()->with(
                'error',
                'Pengajuan ini sudah diproses atau bukan tahap persetujuan Anda.'
            );
        }

        $requisition->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda DITOLAK oleh ' . $jabatan . '.',
                    route('dashboard'),
                    'danger'
                )
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                'Notif error reject purchase: ' . $e->getMessage()
            );
        }

        return back()->with(
            'success',
            'Permohonan ditolak oleh ' . $jabatan . ' dengan catatan.'
        );
    }
}
