<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BatchesIndex extends Component
{
    use WithPagination;

    public int $branch_id = 0;
    public string $search = '';
    public string $status_filter = 'all';
    public int $product_id = 0;

    public bool $isSuperAdmin = false;
    public ?int $userBranchId = 0;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->syncAuthContext();
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->userBranchId = (int) ($user?->branch_id ?? 0);

        if (!$this->isSuperAdmin) {
            $this->branch_id = $this->userBranchId;
        }
    }

    public function render()
    {
        $this->syncAuthContext();

        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : $this->userBranchId;

        $batches = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when($branchId > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $branchId))
            ->when($this->product_id > 0, fn ($q) => $q->where('stock_in_items.product_id', $this->product_id))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('products.name', 'like', $term)
                        ->orWhere('stock_in_items.batch_ref_no', 'like', $term)
                        ->orWhere('stock_in_receipts.receipt_no', 'like', $term);
                });
            })
            ->when($this->status_filter === 'active', fn ($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where(function ($qq) {
                    $qq->whereNull('stock_in_items.expiry_date')
                        ->orWhere('stock_in_items.expiry_date', '>=', now()->toDateString());
                })
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'expiring', fn ($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where('stock_in_items.expiry_date', '<', now()->addDays(30)->toDateString())
                ->where('stock_in_items.expiry_date', '>=', now()->toDateString())
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'expired', fn ($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where('stock_in_items.expiry_date', '<', now()->toDateString())
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'voided', fn ($q) => $q->whereNotNull('stock_in_receipts.voided_at'))
            ->select([
                'stock_in_items.id',
                'stock_in_items.product_id',
                'stock_in_items.batch_ref_no',
                'stock_in_items.quantity as original_quantity',
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price',
                'stock_in_items.expiry_date',
                'stock_in_items.entry_mode',
                'stock_in_items.created_at',
                'stock_in_receipts.id as receipt_id',
                'stock_in_receipts.receipt_no',
                'stock_in_receipts.received_at',
                'stock_in_receipts.voided_at',
                'products.name as product_name',
                'categories.name as category_name',
            ])
            ->orderByDesc('stock_in_items.created_at')
            ->paginate(20);

        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $branches = [];
        if ($this->isSuperAdmin) {
            $branches = \App\Models\Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('livewire.batches-index', [
            'batches' => $batches,
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    public function getBatchStatus(StockInItem $batch): string
    {
        if ($batch->receipt && $batch->receipt->voided_at) {
            return 'voided';
        }

        if ($batch->remaining_quantity <= 0) {
            return 'depleted';
        }

        if (!$batch->expiry_date) {
            return 'active';
        }

        $daysToExpiry = now()->diffInDays($batch->expiry_date, false);

        if ($daysToExpiry < 0) {
            return 'expired';
        }

        if ($daysToExpiry <= 30) {
            return 'expiring';
        }

        return 'active';
    }
}
