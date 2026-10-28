<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\PartialLeaveRequest;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\AbsenteeRecord;
use App\Models\LeaveQuota;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Menampilkan semua laporan dengan fitur pengurutan.
     */
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', now()->year);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Pengurutan laporan cuti harian
        $sortLeave = $request->input('sort_leave', 'date');
        $directionLeave = $request->input('direction_leave', 'desc');

        $allowedLeaveSorts = ['name', 'date', 'type', 'status'];

        if (!in_array($sortLeave, $allowedLeaveSorts, true)) {
            $sortLeave = 'date';
        }

        if (!in_array($directionLeave, ['asc', 'desc'], true)) {
            $directionLeave = 'desc';
        }

        $leaveQuery = LeaveRequest::with('user')
            ->whereYear('start_date', $selectedYear)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('start_date', [$startDate, $endDate]);
            });

        $leaveSortColumns = [
            'name' => fn($query, $direction) => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'leave_requests.user_id'),
                $direction
            ),
            'date' => fn($query, $direction) => $query->orderBy('start_date', $direction),
            'type' => fn($query, $direction) => $query->orderBy('leave_type', $direction),
            'status' => fn($query, $direction) => $query->orderBy('status', $direction),
        ];

        $leaveSortColumns[$sortLeave]($leaveQuery, $directionLeave);
        $leaveReports = $leaveQuery->get();

        // Pengurutan laporan izin meninggalkan tempat
        $sortPartial = $request->input('sort_partial', 'date');
        $directionPartial = $request->input('direction_partial', 'desc');

        $allowedPartialSorts = ['name', 'date', 'purpose', 'status'];

        if (!in_array($sortPartial, $allowedPartialSorts, true)) {
            $sortPartial = 'date';
        }

        if (!in_array($directionPartial, ['asc', 'desc'], true)) {
            $directionPartial = 'desc';
        }

        $partialQuery = PartialLeaveRequest::with('user')
            ->whereYear('leave_date', $selectedYear)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('leave_date', [$startDate, $endDate]);
            });

        $partialSortColumns = [
            'name' => fn($query, $direction) => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'partial_leave_requests.user_id'),
                $direction
            ),
            'date' => fn($query, $direction) => $query->orderBy('leave_date', $direction),
            'purpose' => fn($query, $direction) => $query->orderBy('purpose_category', $direction),
            'status' => fn($query, $direction) => $query->orderBy('status', $direction),
        ];

        $partialSortColumns[$sortPartial]($partialQuery, $directionPartial);
        $partialReports = $partialQuery->get();

        // Pengurutan laporan pembelian barang
        $sortPurchase = $request->input('sort_purchase', 'date');
        $directionPurchase = $request->input('direction_purchase', 'desc');

        $allowedPurchaseSorts = ['name', 'date', 'number', 'status'];

        if (!in_array($sortPurchase, $allowedPurchaseSorts, true)) {
            $sortPurchase = 'date';
        }

        if (!in_array($directionPurchase, ['asc', 'desc'], true)) {
            $directionPurchase = 'desc';
        }

        $purchaseQuery = PurchaseRequisition::with('user', 'items')
            ->whereYear('requisition_date', $selectedYear)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('requisition_date', [$startDate, $endDate]);
            });

        $purchaseSortColumns = [
            'name' => fn($query, $direction) => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'purchase_requisitions.user_id'),
                $direction
            ),
            'date' => fn($query, $direction) => $query->orderBy('requisition_date', $direction),
            'number' => fn($query, $direction) => $query->orderBy('requisition_number', $direction),
            'status' => fn($query, $direction) => $query->orderBy('status', $direction),
        ];

        $purchaseSortColumns[$sortPurchase]($purchaseQuery, $directionPurchase);
        $purchaseReports = $purchaseQuery->get();

        // Daftar tahun laporan
        $leaveYears = LeaveRequest::select(DB::raw('YEAR(start_date) as year'))->distinct();
        $partialYears = PartialLeaveRequest::select(DB::raw('YEAR(leave_date) as year'))->distinct();
        $purchaseYears = PurchaseRequisition::select(DB::raw('YEAR(requisition_date) as year'))->distinct();

        $years = $leaveYears->union($partialYears)->union($purchaseYears)
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('reports.index', compact(
            'leaveReports',
            'partialReports',
            'purchaseReports',
            'selectedYear',
            'years',
            'startDate',
            'endDate',
            'sortLeave',
            'directionLeave',
            'sortPartial',
            'directionPartial',
            'sortPurchase',
            'directionPurchase'
        ));
    }
    /**
     * Ekspor laporan rekapitulasi ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only([
            'year',
            'start_date',
            'end_date',
            'sort_leave',
            'direction_leave',
            'sort_partial',
            'direction_partial',
            'sort_purchase',
            'direction_purchase',
        ]);

        $year = $filters['year'] ?? now()->year;

        return Excel::download(
            new ReportsExport($filters),
            'laporan-rekapitulasi-' . $year . '.xlsx'
        );
    }
    /**
     * Cetak izin meninggalkan tempat.
     */
    public function printPartialLeave(PartialLeaveRequest $request)
    {
        $request->load('user', 'approverUnit', 'approverSdm');

        return view('reports.print_partial_leave', ['data' => $request]);
    }

    /**
     * Cetak permohonan pembelian barang.
     */
    public function printPurchaseRequisition(PurchaseRequisition $requisition)
    {
        $requisition->load('user', 'items', 'approverUnit', 'approverSdm', 'approverPengurus');

        return view('reports.print_purchase_requisition', ['data' => $requisition]);
    }

    /**
     * Cetak cuti atau izin harian.
     */
    /**
     * Cetak formulir pengajuan cuti atau izin tidak masuk bekerja.
     */
    public function printLeaveRequest(LeaveRequest $request)
    {
        $request->load(
            'user',
            'approverUnit',
            'approverSdm',
            'approverPengurus'
        );

        $tahunPengajuan = $request->created_at->year;

        // Ambil data rekap absensi milik pemohon pada tahun pengajuan.
        $absensi = AbsenteeRecord::where('user_id', $request->user_id)
            ->where('year', $tahunPengajuan)
            ->first();

        // Ambil data kuota cuti pada tahun pengajuan.
        $quota = LeaveQuota::where('user_id', $request->user_id)
            ->where('year', $tahunPengajuan)
            ->first();

        // Hitung sisa cuti berdasarkan kuota yang tersimpan.
        $totalCuti = $quota?->total_quota ?? 12;
        $cutiTerpakai = $quota?->used_quota ?? 0;
        $sisaCuti = max(0, $totalCuti - $cutiTerpakai);

        // Sisa izin menggunakan data input SDM jika tersedia.
        $sisaIzin = $absensi?->sisa_ijin_input
            ?? max(
                0,
                ($quota?->total_izin ?? 3) - ($quota?->used_izin ?? 0)
            );

        return view('reports.print_leave_request', [
            'data' => $request,
            'absensi' => $absensi,
            'sisaCuti' => $sisaCuti,
            'sisaIzin' => $sisaIzin,
        ]);
    }
}
