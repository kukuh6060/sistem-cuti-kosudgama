<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'total_quota',
        'used_quota',
        'total_izin',
        'used_izin',
    ];

    // Relasi: Jatah ini milik satu User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
