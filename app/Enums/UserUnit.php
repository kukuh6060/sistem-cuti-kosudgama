<?php

namespace App\Enums;

enum UserUnit: string
{
    case SIMPAN_PINJAM = 'simpan_pinjam';
    case APOTEK = 'apotek';
    case NIAGA = 'niaga';
    case JASA = 'jasa';
    case SDM = 'sdm';
    case LAINNYA = 'lainnya'; // Fallback
}