<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case PENDING = 'pending';
    case APPROVED_HEAD = 'approved_head'; // Disetujui Kepala Unit
    case APPROVED_SDM = 'approved_sdm';   // Menunggu Pengurus
    case APPROVED_HR = 'approved_hr';     // Final (Disetujui Pengurus)
    case REJECTED = 'rejected';
}