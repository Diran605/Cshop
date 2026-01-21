<?php

namespace App\Http\Controllers;

use App\Models\SalesReceipt;
use Illuminate\View\View;

class SalesReceiptPrintController extends Controller
{
    public function __invoke(SalesReceipt $sale): View
    {
        $sale->load(['branch', 'user', 'items.product']);

        return view('sales.receipt-print', [
            'sale' => $sale,
        ]);
    }
}
