<?php

namespace App\Exports;

use App\Models\PurchaseRequisition;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Enumerable;

class PurchaseReportSheet implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $filters) {}

    public function title(): string
    {
        return 'Pembelian Barang';
    }

    public function headings(): array
    {
        return [
            'Pemohon',
            'Tanggal Pengajuan',
            'Nomor Surat',
            'Daftar Barang',
            'Status',
            'Alasan Penolakan',
        ];
    }

    public function collection(): Enumerable
    {
        $year = $this->filters['year'] ?? now()->year;

        $query = PurchaseRequisition::with('user', 'items')
            ->whereYear('requisition_date', $year)
            ->when(
                !empty($this->filters['start_date']) &&
                    !empty($this->filters['end_date']),
                fn($q) => $q->whereBetween('requisition_date', [
                    $this->filters['start_date'],
                    $this->filters['end_date'],
                ])
            );

        $sort = $this->filters['sort_purchase'] ?? 'date';
        $direction = $this->filters['direction_purchase'] ?? 'desc';

        if (!in_array($sort, ['name', 'date', 'number', 'status'], true)) {
            $sort = 'date';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        match ($sort) {
            'name' => $query->orderBy(
                User::select('name')
                    ->whereColumn('users.id', 'purchase_requisitions.user_id'),
                $direction
            ),
            'number' => $query->orderBy('requisition_number', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('requisition_date', $direction),
        };

        return $query->get()->map(function ($item) {
            $items = $item->items->map(function ($barang) {
                $detail = ($barang->nama_barang ?? '-')
                    . ' (Jumlah: ' . ($barang->jumlah_barang ?? '-') . ')'
                    . ' (Perkiraan harga: Rp ' .
                    number_format((float) ($barang->perkiraan_harga ?? 0), 0, ',', '.') . ')';

                if (!empty($barang->keterangan)) {
                    $detail .= ' - ' . $barang->keterangan;
                }

                return $detail;
            })->implode("\n");

            return [
                $item->user?->name ?? '-',
                $item->requisition_date?->format('d-m-Y'),
                $item->requisition_number ?? '-',
                $items ?: '-',
                $this->statusLabel((string) $item->status),
                $item->rejection_reason ?? '-',
            ];
        });
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending_unit' => 'Menunggu Kepala Unit',
            'approved_head' => 'Disetujui Kepala Unit',
            'approved_sdm' => 'Disetujui SDM',
            'approved' => 'Disetujui Pengurus',
            'rejected' => 'Ditolak',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
