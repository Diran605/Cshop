<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockInTemplateExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            ['2024-01-15', 'Sample Product 1', '20', '100.00', 'Sample supplier 1', 'BATCH001', '2024-12-31', 'Sample note for stock in 1'],
            ['2024-01-16', 'Sample Product 2', '15', '200.00', 'Sample supplier 2', 'BATCH002', '2025-06-30', 'Sample note for stock in 2'],
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Product Name',
            'Quantity',
            'Unit Cost',
            'Supplier',
            'Batch Ref No',
            'Expiry Date',
            'Notes',
        ];
    }

    public function title(): string
    {
        return 'Stock In Template';
    }
}
