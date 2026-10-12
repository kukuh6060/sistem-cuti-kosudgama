<?php

namespace App\Models;

use App\Enums\LeaveStatus; // <-- Import Enum
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon; // Pastikan Carbon diimport

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'rejection_reason',
        'approved_by_head_id',
        'approved_by_hr_id',
        'signature_path',
        'signature_path_unit',
        'signature_path_sdm',
        'approved_by_pengurus_id', // <-- TAMBAHAN
        'signature_path_pengurus', // <-- TAMBAHAN
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => LeaveStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relasi: Pengajuan ini milik satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendapatkan user (Kepala Unit) yang menyetujui.
     */
    public function approverUnit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_head_id');
    }

    /**
     * Mendapatkan user (SDM) yang menyetujui.
     */
    public function approverSdm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_hr_id');
    }

    /**
     * Mendapatkan user (Pengurus) yang menyetujui.
     */
    public function approverPengurus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_pengurus_id');
    }
}