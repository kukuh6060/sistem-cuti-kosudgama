<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsenteeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year',
        'sakit',
        'tanpa_kabar',
        'ijin_meninggalkan_tempat',
        'terlambat',
        'setengah_hari',
        'pulang_awal',
        'ijin_lebih_1_jam',
        'ijin_kurang_1_jam',
        'sisa_cuti_input',
        'sisa_ijin_input',
    ];

    /**
     * Mendapatkan data user pemilik record ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}