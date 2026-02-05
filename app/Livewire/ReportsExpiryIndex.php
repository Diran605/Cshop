<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockInItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsExpiryIndex extends Component
{
    public int $branch_id = 0;
    public int $days_ahead = 30;

    public string $search = '';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = 0;
        }

        $this->days_ahead = 30;
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->days_ahead = 30;
            $this->search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function render()
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey($this->branch_id)
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $today = Carbon::today()->toDateString();
        $nearDate = Carbon::today()->addDays(max(0, (int) $this->days_ahead))->toDateString();

        $expiredBase = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.expiry_date', '<', $today)
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id));

        $nearExpiryBase = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->whereNotNull('stock_in_items.expiry_date')
            ->whereBetween('stock_in_items.expiry_date', [$today, $nearDate])
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id));

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $expiredBase->where('products.name', 'like', $term);
            $nearExpiryBase->where('products.name', 'like', $term);
        }

        $expiredRows = (clone $expiredBase)
            ->orderBy('stock_in_items.expiry_date')
            ->limit(200)
            ->get([
                'products.name as product_name',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price',
                'stock_in_items.batch_ref_no',
                'stock_in_receipts.branch_id',
            ]);

        $nearExpiryRows = (clone $nearExpiryBase)
            ->orderBy('stock_in_items.expiry_date')
            ->limit(200)
            ->get([
                'products.name as product_name',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price',
                'stock_in_items.batch_ref_no',
                'stock_in_receipts.branch_id',
            ]);

        $expiredLoss = 0.0;
        foreach ($expiredRows as $row) {
            $expiredLoss += (float) ($row->cost_price ?? 0) * (int) ($row->remaining_quantity ?? 0);
        }

        $nearExpiryValue = 0.0;
        foreach ($nearExpiryRows as $row) {
            $nearExpiryValue += (float) ($row->cost_price ?? 0) * (int) ($row->remaining_quantity ?? 0);
        }

        $expiredCount = count($expiredRows);
        $nearExpiryCount = count($nearExpiryRows);

        return view('livewire.reports-expiry-index', [
            'branches' => $branches,
            'expiredRows' => $expiredRows,
            'nearExpiryRows' => $nearExpiryRows,
            'expiredLoss' => $expiredLoss,
            'nearExpiryValue' => $nearExpiryValue,
            'expiredCount' => $expiredCount,
            'nearExpiryCount' => $nearExpiryCount,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
