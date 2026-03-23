<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        $rows = $this->data->map(function ($item) {
            return [
                $item->product,
                $item->unit,
                $item->ordered,
                $item->actual_deliver,
                $item->ending_balance,
            ];
        });

        // 🔥 ADD TOTAL ROW
        $rows->push([
            'TOTAL',
            '',
            $this->data->sum('ordered'),
            $this->data->sum('actual_deliver'),
            $this->data->sum('ending_balance'),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'PRODUCTS',
            'UNIT',
            'ORDERED',
            'ACTUAL DELIVER',
            'ENDING BALANCE',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header
            1 => ['font' => ['bold' => true]],

            // Total row (last row)
            $sheet->getHighestRow() => [
                'font' => ['bold' => true],
            ],
        ];
    }
}