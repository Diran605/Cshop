<?php

namespace App\Http\Controllers;

use App\Models\StockInReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInReceiptPrintController extends Controller
{
    public function __invoke(Request $request, StockInReceipt $receipt): View
    {
        $receipt->load(['branch', 'user', 'items.product']);

        $user = $request->user();
        if ($user && $user->role !== 'super_admin') {
            abort_unless((int) ($user->branch_id ?? 0) === (int) $receipt->branch_id, 403);
        }

        abort_if($receipt->voided_at !== null, 404);

        return view('stock-in.receipt-print', [
            'receipt' => $receipt,
        ]);
    }
}
