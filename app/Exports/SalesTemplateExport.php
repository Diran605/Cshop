<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesTemplateExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect([
            ['2024-01-15', 'Sample Product 1', '5', '150.00', 'Sample Customer 1', 'cash', '750.00', '750.00', 'Sample note for sale 1'],
            ['2024-01-16', 'Sample Product 2', '3', '300.00', 'Sample Customer 2', 'cash', '900.00', '900.00', 'Sample note for sale 2'],
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Product Name',
            'Quantity',
            'Unit Price',
            'Customer Name',
            'Payment Method',
            'Amount Paid',
            'Total Amount',
            'Notes',
        ];
    }

    public function title(): string
    {
        return 'Sales Template';
    }
}
