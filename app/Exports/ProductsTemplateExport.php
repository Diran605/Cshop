<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsTemplateExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            ['Sample Product 1', 'Sample Category 1', '100.00', '120.00', '150.00', 'no', '', 'active', '50', '100.00', '2025-12-31'],
            ['Sample Product 2', 'Sample Category 2', '200.00', '240.00', '300.00', 'no', '', 'active', '30', '200.00', '2026-06-30'],
        ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Category',
            'Cost Price',
            'Min Selling Price',
            'Selling Price',
            'Bulk Enabled',
            'Bulk Type ID',
            'Status',
            'Opening Stock Qty',
            'Opening Cost Price',
            'Expiry Date',
        ];
    }

    public function title(): string
    {
        return 'Products Template';
    }
}
