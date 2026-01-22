<?php

namespace App\Http\Controllers;

use App\Models\SalesReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReceiptPrintController extends Controller
{
    public function __invoke(Request $request, SalesReceipt $sale): View
    {
        $sale->load(['branch', 'user', 'items.product']);

        $user = $request->user();
        if ($user && $user->role !== 'super_admin') {
            abort_unless((int) ($user->branch_id ?? 0) === (int) $sale->branch_id, 403);
        }

        abort_if($sale->voided_at !== null, 404);

        return view('sales.receipt-print', [
            'sale' => $sale,
        ]);
    }
}
