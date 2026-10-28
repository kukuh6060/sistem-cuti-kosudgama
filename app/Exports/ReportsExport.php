<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportsExport implements Export, WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new LeaveReportSheet($this->filters),
            new PartialLeaveReportSheet($this->filters),
            new PurchaseReportSheet($this->filters),
        ];
    }
}