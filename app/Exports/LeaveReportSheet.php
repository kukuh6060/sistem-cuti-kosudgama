<?php

namespace App\Exports;

use App\Models\LeaveRequest;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Enumerable;

class LeaveReportSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $filters) {}

    public function title(): string
    {
        return 'Cuti Harian';
    }

    public function headings(): array
    {
        return [
            'Karyawan',
            'Tipe',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status',
            'Alasan Penolakan',
        ];
    }

    public function collection(): Enumerable
    {
        $year = $this->filters['year'] ?? now()->year;

        $query = LeaveRequest::with('user')
            ->whereYear('start_date', $year)
            ->when(
                !empty($this->filters['start_date']) &&
                    !empty($this->filters['end_date']),
                fn($q) => $q->whereBetween('start_date', [
                    $this->filters['start_date'],
                    $this->filters['end_date'],
                ])
            );

        $sort = $this->filters['sort_leave'] ?? 'date';
        $direction = $this->filters['direction_leave'] ?? 'desc';

        if (!in_array($sort, ['name', 'date', 'type', 'status'], true)) {
            $sort = 'date';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        match ($sort) {
            'name' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'leave_requests.user_id'),
                $direction
            ),
            'type' => $query->orderBy('leave_type', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('start_date', $direction),
        };

        return $query->get()->map(function ($item) {
            $status = $item->status instanceof BackedEnum
                ? $item->status->value
                : (string) $item->status;

            return [
                $item->user?->name ?? '-',
                $item->leave_type,
                $item->start_date?->format('d-m-Y'),
                $item->end_date?->format('d-m-Y'),
                $this->statusLabel($status),
                $item->rejection_reason ?? '-',
            ];
        });
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu Kepala Unit',
            'approved_head' => 'Disetujui Kepala Unit',
            'approved_sdm' => 'Disetujui SDM',
            'approved_hr' => 'Disetujui Pengurus',
            'rejected' => 'Ditolak',
            default => $status,
        };
    }
}
