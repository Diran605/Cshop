<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\StockInItem;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\On;

class SalesBatchSelector extends Component
{
    public int $branchId = 0;
    public ?int $categoryId = null;
    public string $search = '';
    public ?int $selectedProductId = null;
    public array $batches = [];
    public bool $isFifo = true;
    public ?int $selectedBatchId = null;

    public function updatedSearch()
    {
        $this->selectedProductId = null;
        $this->batches = [];
        $this->selectedBatchId = null;
    }

    #[On('product-added')]
    public function clearSelection()
    {
        $this->search = '';
        $this->categoryId = null;
        $this->updatedSearch();
    }

    public function selectProduct(int $productId)
    {
        $this->selectedProductId = $productId;
        $this->search = '';
        $this->loadBatches();
    }

    public function loadBatches()
    {
        if (!$this->selectedProductId || $this->branchId <= 0) {
            $this->batches = [];
            return;
        }

        $today = Carbon::today()->toDateString();

        // Get clearance items for this product & branch that have an approved discount
        $clearanceMap = ClearanceItem::query()
            ->where('product_id', $this->selectedProductId)
            ->where('branch_id', $this->branchId)
            ->whereNotNull('stock_in_item_id')
            ->where('approval_status', ClearanceItem::APPROVAL_APPROVED)
            ->whereNotNull('clearance_price')
            ->where('quantity', '>', 0)
            ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)
            ->get()
            ->keyBy('stock_in_item_id');

        $this->batches = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_receipts.branch_id', $this->branchId)
            ->where('stock_in_items.product_id', $this->selectedProductId)
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->select([
                'stock_in_items.id',
                'stock_in_items.batch_ref_no',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price',
                'stock_in_receipts.receipt_no'
            ])
            ->orderByRaw('CASE WHEN stock_in_items.expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('stock_in_items.expiry_date')
            ->orderBy('stock_in_items.id')
            ->get()
            ->map(function ($batch) use ($today, $clearanceMap) {
                $isExpired = $batch->expiry_date && $batch->expiry_date->toDateString() < $today;
                $isNearExpiry = $batch->expiry_date && !$isExpired && $batch->expiry_date->diffInDays(Carbon::today()) <= 30;

                // Check if this expired batch has an approved clearance discount
                $clearance = $clearanceMap->get($batch->id);
                $hasClearanceDiscount = $isExpired && $clearance !== null;
                $clearancePrice = $hasClearanceDiscount ? (float) $clearance->clearance_price : null;
                $clearanceQty = $hasClearanceDiscount ? (int) $clearance->quantity : null;
                $clearanceItemId = $hasClearanceDiscount ? (int) $clearance->id : null;

                return [
                    'id' => $batch->id,
                    'batch_ref' => $batch->batch_ref_no ?? $batch->receipt_no,
                    'expiry_date' => $batch->expiry_date ? $batch->expiry_date->toDateString() : null,
                    'remaining_qty' => $batch->remaining_quantity,
                    'cost_price' => $batch->cost_price,
                    'is_expired' => $isExpired,
                    'is_near_expiry' => $isNearExpiry,
                    'has_clearance_discount' => $hasClearanceDiscount,
                    'clearance_price' => $clearancePrice,
                    'clearance_qty' => $clearanceQty,
                    'clearance_item_id' => $clearanceItemId,
                ];
            })
            ->toArray();

        // If FIFO, auto-select the first non-expired batch
        if ($this->isFifo && count($this->batches) > 0) {
            $firstValid = collect($this->batches)->firstWhere('is_expired', false);
            if ($firstValid) {
                $this->selectedBatchId = $firstValid['id'];
            }
        }
        
        $this->dispatch('product-selected', productId: $this->selectedProductId, batchId: $this->selectedBatchId, isFifo: $this->isFifo);
    }

    public function selectBatch(int $batchId)
    {
        $batch = collect($this->batches)->firstWhere('id', $batchId);

        if ($batch && $batch['is_expired']) {
            // Allow selection if a clearance discount is approved
            if ($batch['has_clearance_discount']) {
                $this->selectedBatchId = $batchId;
                $this->isFifo = false;
                $this->dispatch('banner-message', message: 'Warning: Expired batch selected at clearance price (XAF ' . number_format($batch['clearance_price'], 2) . '). Max clearance qty: ' . $batch['clearance_qty'], style: 'warning');
                $this->dispatch('batch-selected', batchId: $batchId, isFifo: $this->isFifo, isClearance: true, clearancePrice: $batch['clearance_price'], clearanceQty: $batch['clearance_qty'], clearanceItemId: $batch['clearance_item_id']);
                return;
            }

            // No clearance discount - block selection
            $this->dispatch('banner-message', message: 'Error: Cannot select an expired batch without an approved clearance discount! Send it to clearance first.', style: 'danger');
            return;
        }

        $this->selectedBatchId = $batchId;
        $this->isFifo = false;
        $this->dispatch('batch-selected', batchId: $batchId, isFifo: $this->isFifo, isClearance: false, clearancePrice: null, clearanceQty: null, clearanceItemId: null);
    }

    public function toggleFifo()
    {
        $this->isFifo = true;
        $this->loadBatches();
    }

    public function nextBatch()
    {
        if (!$this->selectedBatchId || count($this->batches) === 0) return;
        $idx = collect($this->batches)->search(fn($b) => $b['id'] === $this->selectedBatchId);
        if ($idx !== false && $idx < count($this->batches) - 1) {
            $this->selectBatch($this->batches[$idx + 1]['id']);
        }
    }

    public function prevBatch()
    {
        if (!$this->selectedBatchId || count($this->batches) === 0) return;
        $idx = collect($this->batches)->search(fn($b) => $b['id'] === $this->selectedBatchId);
        if ($idx !== false && $idx > 0) {
            $this->selectBatch($this->batches[$idx - 1]['id']);
        }
    }

    public function render()
    {
        $categories = [];
        $searchableProducts = [];
        if ($this->branchId > 0) {
            $categories = Category::where('branch_id', $this->branchId)->orderBy('name')->get();
            
            if (!$this->selectedProductId) {
                $query = Product::query()
                    ->where('status', Product::STATUS_ACTIVE)
                    ->where('branch_id', $this->branchId);
                    
                if ($this->categoryId) {
                    $query->where('category_id', $this->categoryId);
                }
                    
                if (trim($this->search) !== '') {
                    $term = '%' . trim($this->search) . '%';
                    $query->where(function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('description', 'like', $term)
                            ->orWhereHas('category', fn($qc) => $qc->where('name', 'like', $term));
                    });
                }
                
                $searchableProducts = $query->with('category')->orderBy('name')->limit(10)->get();
            }
        }

        $selectedProduct = null;
        if ($this->selectedProductId) {
            $selectedProduct = Product::query()->with('category')->find($this->selectedProductId);
        }

        return view('livewire.sales-batch-selector', [
            'searchableProducts' => $searchableProducts,
            'selectedProduct' => $selectedProduct,
            'categories' => $categories,
        ]);
    }
}
