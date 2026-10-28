<?php

namespace App\Exports;

use App\Models\PartialLeaveRequest;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Enumerable;

class PartialLeaveReportSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $filters) {}

    public function title(): string
    {
        return 'Izin Meninggalkan Tempat';
    }

    public function headings(): array
    {
        return [
            'Karyawan',
            'Tanggal',
            'Jam Mulai',
            'Jam Selesai',
            'Keperluan',
            'Status',
            'Alasan Penolakan',
        ];
    }

    public function collection(): Enumerable
    {
        $year = $this->filters['year'] ?? now()->year;

        $query = PartialLeaveRequest::with('user')
            ->whereYear('leave_date', $year)
            ->when(
                !empty($this->filters['start_date']) &&
                    !empty($this->filters['end_date']),
                fn($q) => $q->whereBetween('leave_date', [
                    $this->filters['start_date'],
                    $this->filters['end_date'],
                ])
            );

        $sort = $this->filters['sort_partial'] ?? 'date';
        $direction = $this->filters['direction_partial'] ?? 'desc';

        if (!in_array($sort, ['name', 'date', 'purpose', 'status'], true)) {
            $sort = 'date';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        match ($sort) {
            'name' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'partial_leave_requests.user_id'),
                $direction
            ),
            'purpose' => $query->orderBy('purpose_category', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('leave_date', $direction),
        };

        return $query->get()->map(function ($item) {
            $status = $item->status instanceof BackedEnum
                ? $item->status->value
                : (string) $item->status;

            $purpose = $item->purpose_category === 'lain'
                ? ($item->purpose_other ?? 'Lain')
                : ucwords(str_replace('_', ' ', $item->purpose_category));

            return [
                $item->user?->name ?? '-',
                $item->leave_date?->format('d-m-Y'),
                $item->start_time
                    ? Carbon::parse($item->start_time)->format('H:i')
                    : '-',
                $item->end_time
                    ? Carbon::parse($item->end_time)->format('H:i')
                    : '-',
                $purpose,
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
            'rejected' => 'Ditolak',
            default => $status,
        };
    }
}
