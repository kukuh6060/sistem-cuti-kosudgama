<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'requisition_number',
        'requisition_date',
        'status',
        'signature_path',
        'approved_by_unit_id',
        'approved_by_sdm_id',
        'approved_by_pengurus_id',
        'rejection_reason',
        'signature_path_unit',     // <-- TAMBAHAN BARU
        'signature_path_sdm',      // <-- TAMBAHAN BARU
        'signature_path_pengurus', // <-- TAMBAHAN BARU
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = ['requisition_date' => 'date'];

    /**
     * Relasi: Pengajuan ini milik satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Pengajuan ini memiliki banyak Item
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }

    /**
     * Mendapatkan user (Kepala Unit) yang menyetujui.
     */
    public function approverUnit(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_unit_id');
    }

    /**
     * Mendapatkan user (SDM) yang menyetujui.
     */
    public function approverSdm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_sdm_id');
    }

    /**
     * Mendapatkan user (Pengurus) yang menyetujui.
     */
    public function approverPengurus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_pengurus_id');
    }
}