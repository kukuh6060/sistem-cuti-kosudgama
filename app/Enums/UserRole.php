<?php

namespace App\Enums;

enum UserRole: string
{
    case KARYAWAN = 'karyawan';
    case WAKIL_KEPALA_UNIT = 'wakil_kepala_unit';
    case KEPALA_UNIT = 'kepala_unit';
    case SDM = 'sdm';

    // Pertahankan sementara untuk membaca akun dan riwayat lama.
    // Role ini tidak akan ditampilkan atau dapat dipilih lagi.
    case PENGURUS = 'pengurus';
}
