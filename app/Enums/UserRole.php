<?php

namespace App\Enums;

enum UserRole: string
{
    case KARYAWAN = 'karyawan';
    case KEPALA_UNIT = 'kepala_unit';
    case SDM = 'sdm';
    case PENGURUS = 'pengurus';
}