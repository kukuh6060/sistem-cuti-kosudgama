<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case PENDING = 'pending';
    case APPROVED_HEAD = 'approved_head'; // Disetujui Kepala Unit atau Wakil Kepala Unit
    case APPROVED_SDM = 'approved_sdm';   // Persetujuan awal SDM, menunggu tanda tangan Pengurus offline
    case APPROVED_HR = 'approved_hr';     // Persetujuan Final oleh SDM
    case REJECTED = 'rejected';
}
