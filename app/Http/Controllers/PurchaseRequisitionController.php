<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\User;
use App\Enums\UserRole;
use App\Notifications\PengajuanStatusNotification;

class PurchaseRequisitionController extends Controller
{
    /**
     * Menampilkan formulir permohonan pembelian.
     */
    public function create()
    {
        return view('purchase.create');
    }

    /**
     * Menyimpan permohonan pembelian baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi data utama
        $request->validate([
            'requisition_date' => ['required', 'date'],
            'signature_mode' => ['required', 'string', 'in:draw,upload'],
            'signature_data' => ['required_if:signature_mode,draw', 'nullable', 'string'],
            'signature_file' => ['required_if:signature_mode,upload', 'nullable', 'image', 'mimes:png', 'max:2048'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_barang' => ['required', 'string', 'max:255'],
            'items.*.jumlah_barang' => ['required', 'integer', 'min:1'],
            'items.*.perkiraan_harga' => ['nullable', 'numeric', 'min:0'],
            'items.*.harga' => ['nullable', 'numeric', 'min:0'],
            'items.*.keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Anda harus menambahkan minimal 1 barang.',
            'items.*.nama_barang.required' => 'Nama barang wajib diisi.',
            'items.*.jumlah_barang.required' => 'Jumlah barang wajib diisi.',
        ]);

        // --- Proses Tanda Tangan ---
        $signaturePath = null;
        if ($request->signature_mode == 'draw' && $request->has('signature_data') && !empty($request->signature_data)) {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data));
            $fileName = 'signatures/purchase_' . $user->id . '_' . time() . '.png';
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

        // --- Simpan ke Database (menggunakan Transaksi) ---
        DB::beginTransaction();
        try {
            // 1. Buat Requisition Utama
            $requisition = PurchaseRequisition::create([
                'user_id' => $user->id,
                'requisition_date' => $request->requisition_date,
                'status' => 'pending_unit',
                'signature_path' => $signaturePath,
            ]);

            // 2. Loop dan Simpan Item Barang
            foreach ($request->items as $item) {
                $requisition->items()->create([
                    'nama_barang' => $item['nama_barang'],
                    'jumlah_barang' => $item['jumlah_barang'],
                    'perkiraan_harga' => $item['perkiraan_harga'] ?? 0,
                    'harga' => $item['harga'] ?? 0,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            // 3. Kirim Notifikasi ke Kepala Unit
            $kepalaUnit = User::where('role', UserRole::KEPALA_UNIT)
                ->where('unit', $user->unit)
                ->first();

            if ($kepalaUnit) {
                $kepalaUnit->notify(new PengajuanStatusNotification(
                    'Permohonan Pembelian Barang baru dari ' . $user->name,
                    route('purchase.approval.unit.index') // Rute ini akan kita buat
                ));
            }

            DB::commit(); // Selesaikan transaksi

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            return back()->withErrors(['error' => 'Gagal menyimpan data. ' . $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Permohonan Pembelian Barang berhasil dikirim.');
    }
}
