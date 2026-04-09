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

    protected $type;

    public function __construct($data, $type = 'SUMMARY')
    {
        $this->data = collect($data);
        $this->type = $type;
    }
    public function collection()
    {
        $rows = collect();

        // ✅ MONTHLY MODE
        if ($this->type === 'MONTHLY') {

            foreach ($this->data as $monthData) {

                $monthItems = collect($monthData['data']);

                // 🔹 MONTH HEADER
                $rows->push([
                    strtoupper($monthData['month']),
                    '', '', '', '', '', ''
                ]);

                $grouped = $monthItems->groupBy('category');

                foreach ($grouped as $category => $items) {

                    // 🔹 CATEGORY HEADER
                    $rows->push([
                        strtoupper($category ?? 'UNCATEGORIZED'),
                        '', '', '', '', '', ''
                    ]);

                    foreach ($items as $item) {
                        $rows->push([
                            '',
                            $item->product,
                            $item->unit,
                            $item->ordered,
                            $item->delivered,
                            $item->adjustment,
                            $item->ending,
                        ]);
                    }
                }

                // 🔥 MONTH TOTAL ROW
                $rows->push([
                    'TOTAL ' . strtoupper($monthData['month']),
                    '',
                    '',
                    $monthItems->sum('ordered'),
                    $monthItems->sum('delivered'),
                    $monthItems->sum('adjustment'),
                    $monthItems->sum('ending'),
                ]);

                // 🔸 spacing
                $rows->push(['', '', '', '', '', '', '']);

                $allItems = collect($this->data)->flatMap(fn($m) => $m['data']);


            }

                $rows->push([
                    'GRAND TOTAL',
                    '',
                    '',
                    $allItems->sum('ordered'),
                    $allItems->sum('delivered'),
                    $allItems->sum('adjustment'),
                    $allItems->sum('ending'),
                ]);

            return $rows;
        }

        // ✅ SUMMARY MODE (your original logic)
        $grouped = $this->data->groupBy('category');

        foreach ($grouped as $category => $items) {

            $rows->push([
                strtoupper($category ?? 'UNCATEGORIZED'),
                '', '', '', '', '', ''
            ]);

            foreach ($items as $item) {
                $rows->push([
                    '',
                    $item->product,
                    $item->unit,
                    $item->ordered,
                    $item->delivered,
                    $item->adjustment,
                    $item->ending,
                ]);
            }
        }

        // 🔥 TOTAL ROW
        $rows->push([
            'TOTAL',
            '',
            '',
            $this->data->sum('ordered'),
            $this->data->sum('delivered'),
            $this->data->sum('adjustment'),
            $this->data->sum('ending'),
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        return [
            1 => ['font' => ['bold' => true]], // header

            // last row (grand total)
            $highestRow => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'CATEGORY',
            'PRODUCTS',
            'UNIT',
            'ORDERED',
            'DELIVERED',
            'ADJUSTMENT',
            'ENDING',
        ];
    }

}