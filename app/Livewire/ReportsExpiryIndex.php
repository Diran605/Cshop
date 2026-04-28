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

    public int $category_id = 0;
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
        $this->category_id = 0;
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->days_ahead = 30;
            $this->category_id = 0;
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

        $categories = \App\Models\Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $today = Carbon::today()->toDateString();
        $nearDate = Carbon::today()->addDays(max(0, (int) $this->days_ahead))->toDateString();

        $baseQuery = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where('products.name', 'like', $term);
            });

        // Expired
        $expiredRows = (clone $baseQuery)
            ->where('stock_in_items.expiry_date', '<', $today)
            ->orderBy('stock_in_items.expiry_date')
            ->get([
                'products.name as product_name',
                'stock_in_items.*',
                'stock_in_receipts.branch_id',
            ]);

        // Near Expiry
        $nearExpiryRows = (clone $baseQuery)
            ->whereBetween('stock_in_items.expiry_date', [$today, $nearDate])
            ->orderBy('stock_in_items.expiry_date')
            ->get([
                'products.name as product_name',
                'stock_in_items.*',
                'stock_in_receipts.branch_id',
            ]);

        // Timeline Data (Next 4 months)
        $timelineData = [];
        for ($i = 0; $i < 4; $i++) {
            $mStart = Carbon::today()->addMonths($i)->startOfMonth();
            $mEnd = Carbon::today()->addMonths($i)->endOfMonth();
            
            $count = (clone $baseQuery)
                ->whereBetween('stock_in_items.expiry_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->count();
            
            $timelineData[] = [
                'month' => $mStart->format('M Y'),
                'count' => $count
            ];
        }

        $expiredLoss = $expiredRows->sum(fn($r) => $r->cost_price * $r->remaining_quantity);
        $nearExpiryValue = $nearExpiryRows->sum(fn($r) => $r->cost_price * $r->remaining_quantity);

        return view('livewire.reports-expiry-index', [
            'branches' => $branches,
            'categories' => $categories,
            'expiredRows' => $expiredRows,
            'nearExpiryRows' => $nearExpiryRows,
            'expiredLoss' => $expiredLoss,
            'nearExpiryValue' => $nearExpiryValue,
            'expiredCount' => $expiredRows->count(),
            'nearExpiryCount' => $nearExpiryRows->count(),
            'timelineData' => $timelineData,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
