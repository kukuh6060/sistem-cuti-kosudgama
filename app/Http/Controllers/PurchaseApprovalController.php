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

        // Pastikan pengajuan memang menunggu Kepala Unit.
        if ($requisition->status !== 'pending_unit') {
            return back()->with('error', 'Pengajuan ini sudah diproses atau bukan tahap Kepala Unit.');
        }

        // Kepala Unit hanya boleh memproses pengajuan dari unitnya.
        if ($requisition->user->unit !== Auth::user()->unit) {
            abort(403, 'Anda tidak berwenang memproses pengajuan dari unit lain.');
        }

        $request->validate([
            'signature_data' => 'required|string',
        ]);

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
                    'Permohonan Pembelian Anda disetujui Kepala Unit (menunggu SDM).',
                    route('dashboard'),
                    'info'
                )
            );

            $sdmUsers = User::where('role', UserRole::SDM)->get();

            Notification::send(
                $sdmUsers,
                new PengajuanStatusNotification(
                    'Permohonan Pembelian dari ' . $requisition->user->name . ' butuh approval Anda.',
                    route('purchase.approval.sdm.index')
                )
            );
        } catch (\Exception $e) {
            // Kegagalan notifikasi tidak membatalkan persetujuan.
        }

        return back()->with(
            'success',
            'Permohonan disetujui, diteruskan ke SDM.'
        );
    }

    // --- LANGKAH 2: SDM (Mengisi Nomor) ---
    public function indexSdm()
    {
        $requests = PurchaseRequisition::where('status', 'pending_sdm')
            ->with('user', 'items')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchase_approval.sdm_index', compact('requests'));
    }

    public function approveSdm(Request $request, $id)
    {
        $requisition = PurchaseRequisition::with('user')->findOrFail($id);

        if ($requisition->status !== 'pending_sdm') {
            return back()->with('error', 'Pengajuan ini sudah diproses atau bukan tahap SDM.');
        }

        $request->validate([
            'requisition_number' => 'required|string|max:100',
            'signature_data' => 'required|string',
        ]);

        $signaturePath = $this->saveSignature(
            $request->signature_data,
            'purchase_sdm'
        );

        $requisition->update([
            'status' => 'pending_pengurus',
            'requisition_number' => $request->requisition_number,
            'approved_by_sdm_id' => Auth::id(),
            'signature_path_sdm' => $signaturePath,
        ]);

        try {
            $requisition->user->notify(
                new PengajuanStatusNotification(
                    'Permohonan Pembelian Anda disetujui SDM (menunggu Pengurus).',
                    route('dashboard'),
                    'info'
                )
            );

            $pengurusUsers = User::where('role', UserRole::PENGURUS)->get();

            Notification::send(
                $pengurusUsers,
                new PengajuanStatusNotification(
                    'Permohonan Pembelian dari ' . $requisition->user->name . ' butuh approval final Anda.',
                    route('purchase.approval.pengurus.index')
                )
            );
        } catch (\Exception $e) {
            // Kegagalan notifikasi tidak membatalkan persetujuan.
        }

        return back()->with(
            'success',
            'Permohonan disetujui dan diberi nomor, diteruskan ke Pengurus.'
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

        // Tentukan status yang boleh ditolak oleh masing-masing peran.
        if ($role === UserRole::KEPALA_UNIT) {
            $expectedStatus = 'pending_unit';

            if ($requisition->user->unit !== Auth::user()->unit) {
                abort(403, 'Anda tidak berwenang menolak pengajuan dari unit lain.');
            }
        } elseif ($role === UserRole::SDM) {
            $expectedStatus = 'pending_sdm';
        } elseif ($role === UserRole::PENGURUS) {
            $expectedStatus = 'pending_pengurus';
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
                    'Permohonan Pembelian Anda DITOLAK oleh ' . $role->value,
                    route('dashboard'),
                    'danger'
                )
            );
        } catch (\Exception $e) {
            // Kegagalan notifikasi tidak membatalkan penolakan.
        }

        return back()->with(
            'success',
            'Permohonan ditolak dengan catatan.'
        );
    }
}
