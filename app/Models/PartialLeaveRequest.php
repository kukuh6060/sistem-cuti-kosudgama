<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\LeaveStatus; // <-- 1. IMPORT ENUM STATUS

class PartialLeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'leave_date',
        'start_time',
        'end_time',
        'purpose_category',
        'purpose_other',
        'status',
        'signature_path',
        'approved_by_head_id',
        'approved_by_hr_id',
        'rejection_reason',
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
        'leave_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'status' => LeaveStatus::class, // <-- 2. TAMBAHKAN CAST INI
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