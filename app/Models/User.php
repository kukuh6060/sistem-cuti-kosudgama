<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PartialLeaveRequest;
use App\Models\PurchaseRequisition; // <-- Ditambahkan sesuai instruksi

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'unit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'unit' => UserUnit::class,
        ];
    }

    // Relasi: User punya banyak pengajuan cuti
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Relasi: User punya banyak jatah cuti (per tahun)
    public function leaveQuotas(): HasMany
    {
        return $this->hasMany(LeaveQuota::class);
    }

    /**
     * Relasi: User punya banyak izin meninggalkan tempat.
     */
    public function partialLeaveRequests(): HasMany
    {
        return $this->hasMany(PartialLeaveRequest::class);
    }

    /**
     * Relasi: User punya banyak purchase requisition.
     * <-- Ditambahkan sesuai instruksi
     */
    public function purchaseRequisitions(): HasMany
    {
        return $this->hasMany(PurchaseRequisition::class);
    }

    // Helper untuk cek jatah cuti tahun ini
    public function getCurrentQuota()
    {
        // Menggunakan firstOrNew agar jika belum ada, 
        // kita bisa Crate datanya di controller
        return $this->leaveQuotas()->where('year', now()->year)->first();
    }
    public function getAnnualLeaveQuota(): int
    {
        return 12;
    }
    // Relasi ke catatan absensi
    public function absenteeRecord(): HasOne
    {
        // Asumsi kita hanya peduli data tahun ini
        return $this->hasOne(AbsenteeRecord::class)->where('year', now()->year);
    }
}
