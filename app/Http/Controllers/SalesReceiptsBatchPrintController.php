<?php

namespace App\Http\Controllers;

use App\Models\SalesReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReceiptsBatchPrintController extends Controller
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

        $query = SalesReceipt::query()
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

        $sales = $query->orderByDesc('sold_at')->get();

        return view('sales.receipts-batch-print', [
            'sales' => $sales,
        ]);
    }
}
