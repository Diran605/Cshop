<?php

namespace App\Http\Controllers;

use App\Models\StockInReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockInReceiptsBatchPrintController extends Controller
{
    public function __invoke(Request $request): View
    {
        $ids = $request->input('ids', []);

        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', preg_split('/\s*,\s*/', $ids) ?: []));
        }

        if (! is_array($ids)) {
            $ids = [];
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));

        $query = StockInReceipt::query()
            ->with(['branch', 'user', 'items.product'])
            ->whereNull('voided_at');

        $user = $request->user();
        if ($user && $user->role !== 'super_admin') {
            $query->where('branch_id', (int) ($user->branch_id ?? 0));
        }

        if (count($ids) > 0) {
            $query->whereIn('id', $ids);
        } else {
            $query->whereRaw('1 = 0');
        }

        $receipts = $query->orderByDesc('received_at')->get();

        return view('stock-in.receipts-batch-print', [
            'receipts' => $receipts,
        ]);
    }
}
