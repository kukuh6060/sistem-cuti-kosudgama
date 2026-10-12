<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\PartialLeaveRequest;
use App\Models\PurchaseRequisition;
use App\Enums\LeaveStatus;
use Illuminate\Support\Facades\DB; 

class ReportController extends Controller
{
    /**
     * Menampilkan semua laporan
     */
    public function index(Request $request)
    {
        // 1. Ambil semua filter dari request
        $selectedYear = $request->input('year', now()->year);
        
        // === PERBAIKAN DI SINI ===
        // Pastikan variabel ini selalu ada, meskipun nilainya null
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        // =========================

        // 2. Ambil Laporan Cuti Harian
        $leaveReports = LeaveRequest::with('user')
                                    ->whereYear('start_date', $selectedYear)
                                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                        return $query->whereBetween('start_date', [$startDate, $endDate]);
                                    })
                                    ->orderBy('start_date', 'desc')
                                    ->get();

        // 3. Ambil Laporan Izin Tempat
        $partialReports = PartialLeaveRequest::with('user')
                                            ->whereYear('leave_date', $selectedYear)
                                            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                                return $query->whereBetween('leave_date', [$startDate, $endDate]);
                                            })
                                            ->orderBy('leave_date', 'desc')
                                            ->get();

        // 4. Ambil Laporan Pembelian Barang
        $purchaseReports = PurchaseRequisition::with('user', 'items')
                                            ->whereYear('requisition_date', $selectedYear)
                                            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                                                return $query->whereBetween('requisition_date', [$startDate, $endDate]);
                                            })
                                            ->orderBy('requisition_date', 'desc')
                                            ->get();

        // 5. Ambil daftar tahun yang tersedia untuk filter
        $leaveYears = LeaveRequest::select(DB::raw('YEAR(start_date) as year'))->distinct();
        $partialYears = PartialLeaveRequest::select(DB::raw('YEAR(leave_date) as year'))->distinct();
        $purchaseYears = PurchaseRequisition::select(DB::raw('YEAR(requisition_date) as year'))->distinct();

        $years = $leaveYears->union($partialYears)->union($purchaseYears)
                           ->orderBy('year', 'desc')
                           ->pluck('year');
        
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        // 6. Kirim semua data (termasuk nilai filter) ke view
        return view('reports.index', compact(
            'leaveReports', 
            'partialReports', 
            'purchaseReports', 
            'selectedYear', 
            'years',
            'startDate', // <-- Variabel ini sekarang selalu ada
            'endDate'    // <-- Variabel ini sekarang selalu ada
        ));
    }

    /**
     * Menampilkan halaman cetak untuk Izin Meninggalkan Tempat
     */
    public function printPartialLeave(PartialLeaveRequest $request)
    {
        $request->load('user', 'approverUnit', 'approverSdm');
        return view('reports.print_partial_leave', ['data' => $request]);
    }

    /**
     * Menampilkan halaman cetak untuk Permohonan Pembelian Barang
     */
    public function printPurchaseRequisition(PurchaseRequisition $requisition)
    {
        $requisition->load('user', 'items', 'approverUnit', 'approverSdm', 'approverPengurus');
        return view('reports.print_purchase_requisition', ['data' => $requisition]);
    }

    /**
     * Menampilkan halaman cetak untuk Cuti / Ijin Harian
     */
    public function printLeaveRequest(LeaveRequest $request)
    {
        $request->load('user', 'approverUnit', 'approverSdm');
        return view('reports.print_leave_request', ['data' => $request]);
    }
}