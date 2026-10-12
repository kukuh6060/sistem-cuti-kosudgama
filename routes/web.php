<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PartialLeaveController;
use App\Http\Controllers\AbsenteeRecordController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PurchaseRequisitionController; // <-- Ditambahkan sesuai instruksi
use App\Http\Controllers\PurchaseApprovalController; // <-- Ditambahkan sesuai instruksi

// Halaman utama
Route::get('/', function () {
    return redirect()->route('login');
});

// Grup rute yang only bisa diakses setelah login
Route::middleware(['auth', 'verified'])->group(function () {

    // Halaman Dashboard Umum (Untuk semua role)
    Route::get('/dashboard', [LeaveRequestController::class, 'index'])->name('dashboard');

    // Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Notifikasi
    Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.markAsRead');

    // === ROLE: KARYAWAN ===
    Route::middleware('role:karyawan')->group(function () {
        // Rute Cuti (yang sudah ada)
        Route::prefix('cuti')->name('cuti.')->group(function () {
            Route::get('/create', [LeaveRequestController::class, 'create'])->name('create');
            Route::post('/', [LeaveRequestController::class, 'store'])->name('store');
        });
        // Rute Izin Tempat (yang sudah ada)
        Route::prefix('izin-tempat')->name('partial-leave.')->group(function () {
            Route::get('/create', [PartialLeaveController::class, 'create'])->name('create');
            Route::post('/', [PartialLeaveController::class, 'store'])->name('store');
        });

        // --- RUTE BARU PEMBELIAN BARANG ---
        Route::prefix('pembelian')->name('purchase.')->group(function () {
            Route::get('/create', [PurchaseRequisitionController::class, 'create'])->name('create');
            Route::post('/', [PurchaseRequisitionController::class, 'store'])->name('store');
        });
    });

    // === ROLE: KEPALA UNIT ===
    Route::middleware('role:kepala_unit')->group(function () {
        Route::prefix('approval-unit')->name('approval.unit.')->group(function () {
            Route::get('/', [ApprovalController::class, 'indexUnit'])->name('index');
            Route::post('/approve/{id}', [ApprovalController::class, 'approveUnit'])->name('approve');
            Route::post('/reject/{id}', [ApprovalController::class, 'rejectUnit'])->name('reject');
            Route::post('/partial/approve/{id}', [ApprovalController::class, 'approveUnitPartial'])->name('partial.approve');
            Route::post('/partial/reject/{id}', [ApprovalController::class, 'rejectUnitPartial'])->name('partial.reject');
        });

        Route::get('/management/unit-karyawan', [UserController::class, 'indexUnit'])
            ->name('management.unit.index');

        // --- RUTE BARU APPROVAL PEMBELIAN ---
        Route::prefix('approval-pembelian-unit')->name('purchase.approval.unit.')->group(function () {
            Route::get('/', [PurchaseApprovalController::class, 'indexUnit'])->name('index');
            Route::post('/approve/{id}', [PurchaseApprovalController::class, 'approveUnit'])->name('approve');
            Route::post('/reject/{id}', [PurchaseApprovalController::class, 'reject'])->name('reject');
        });
    });

    // === ROLE: SDM ===
    Route::middleware('role:sdm')->group(function () {
        Route::prefix('approval-sdm')->name('approval.sdm.')->group(function () {
            Route::get('/', [ApprovalController::class, 'indexSdm'])->name('index');
            Route::post('/approve/{id}', [ApprovalController::class, 'approveSdm'])->name('approve');
            Route::post('/reject/{id}', [ApprovalController::class, 'rejectSdm'])->name('reject');
            Route::post('/partial/approve/{id}', [ApprovalController::class, 'approveSdmPartial'])->name('partial.approve');
            Route::post('/partial/reject/{id}', [ApprovalController::class, 'rejectSdmPartial'])->name('partial.reject');
        });

        // Rute Data Absensi
        Route::prefix('data-absensi')->name('absentee.')->group(function () {
            Route::get('/', [AbsenteeRecordController::class, 'index'])->name('index');
            Route::get('/rekap', [AbsenteeRecordController::class, 'recap'])->name('recap');
            Route::get('/show/{user}', [AbsenteeRecordController::class, 'show'])->name('show');
            Route::get('/edit/{user}', [AbsenteeRecordController::class, 'edit'])->name('edit');
            Route::patch('/update/{user}', [AbsenteeRecordController::class, 'update'])->name('update');
            Route::delete('/reset/{user}', [AbsenteeRecordController::class, 'reset'])->name('reset');
            Route::patch('/reset-section/absence/{user}', [AbsenteeRecordController::class, 'resetAbsenceSection'])->name('reset.absence');
        });

        // Rute Edit Catatan
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/notes', [SettingController::class, 'edit'])->name('notes.edit');
            Route::patch('/notes', [SettingController::class, 'update'])->name('notes.update');
        });

        // --- RUTE BARU APPROVAL PEMBELIAN ---
        Route::prefix('approval-pembelian-sdm')->name('purchase.approval.sdm.')->group(function () {
            Route::get('/', [PurchaseApprovalController::class, 'indexSdm'])->name('index');
            Route::post('/approve/{id}', [PurchaseApprovalController::class, 'approveSdm'])->name('approve'); // Ini akan butuh 'requisition_number'
            Route::post('/reject/{id}', [PurchaseApprovalController::class, 'reject'])->name('reject');
        });
    });

    // === ROLE: PENGURUS ===
    Route::middleware('role:pengurus')->group(function () {

        // --- [BARU] RUTE APPROVAL CUTI & IZIN UNTUK PENGURUS ---
        Route::prefix('approval-pengurus')->name('approval.pengurus.')->group(function () {
            Route::get('/', [ApprovalController::class, 'indexPengurus'])->name('index');
            // Rute Cuti Harian
            Route::post('/cuti/approve/{id}', [ApprovalController::class, 'approvePengurus'])->name('leave.approve');
            Route::post('/cuti/reject/{id}', [ApprovalController::class, 'rejectPengurus'])->name('leave.reject');
        });

        // --- RUTE BARU APPROVAL PEMBELIAN --- (Sudah ada sebelumnya)
        Route::prefix('approval-pembelian-pengurus')->name('purchase.approval.pengurus.')->group(function () {
            Route::get('/', [PurchaseApprovalController::class, 'indexPengurus'])->name('index');
            Route::post('/approve/{id}', [PurchaseApprovalController::class, 'approvePengurus'])->name('approve');
            Route::post('/reject/{id}', [PurchaseApprovalController::class, 'reject'])->name('reject');
        });
    });

    // === RUTE GABUNGAN UNTUK SDM & PENGURUS ===
    Route::middleware('role:sdm,pengurus')->group(function () {
        // Laporan Cuti
        Route::prefix('laporan')->name('report.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');

            Route::get('/izin-tempat/{request}/print', [ReportController::class, 'printPartialLeave'])
                ->name('partial.print');

            Route::get('/pembelian/{requisition}/print', [ReportController::class, 'printPurchaseRequisition'])
                ->name('purchase.print');

            // --- RUTE BARU DITAMBAHKAN DI SINI ---
            Route::get('/cuti/{request}/print', [ReportController::class, 'printLeaveRequest'])
                ->name('leave.print');
        });

        // Data Karyawan
        Route::get('/management/semua-karyawan', [UserController::class, 'indexAll'])
            ->name('management.all.index');
    });
});

// Load file auth.php bawaan Breeze
require __DIR__ . '/auth.php';
