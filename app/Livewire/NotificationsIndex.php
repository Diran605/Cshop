<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Models\ProductStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationsIndex extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public int $branchId = 0;

    public function mount(): void
    {
        $this->branchId = (int) (auth()->user()?->branch_id ?? 0);
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert && $alert->user_id === auth()->id()) {
            $alert->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        Alert::query()
            ->where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function deleteRead(): void
    {
        Alert::query()
            ->where('user_id', auth()->id())
            ->where('is_read', true)
            ->delete();
    }

    public function getAlertsProperty()
    {
        $query = Alert::query()
            ->where('user_id', auth()->id())
            ->when($this->filter !== 'all', fn ($q) => $q->where('type', $this->filter))
            ->orderByDesc('created_at');

        return $query->paginate(20);
    }

    public function getLowStockAlertsProperty(): \Illuminate\Support\Collection
    {
        return ProductStock::query()
            ->with(['product'])
            ->where('branch_id', $this->branchId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock')
            ->get();
    }

    public function getExpiryAlertsProperty(): \Illuminate\Support\Collection
    {
        return DB::table('stock_in_items')
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->where('stock_in_receipts.branch_id', $this->branchId)
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.expiry_date', '<=', Carbon::today()->addDays(30))
            ->orderBy('stock_in_items.expiry_date')
            ->get([
                'products.id as product_id',
                'products.name as product_name',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
                'stock_in_items.id as stock_in_item_id',
            ]);
    }

    public function getStatsProperty(): array
    {
        $total = Alert::where('user_id', auth()->id())->count();
        $unread = Alert::where('user_id', auth()->id())->where('is_read', false)->count();
        $lowStock = $this->low_stock_alerts->count();
        $expiring = $this->expiry_alerts->count();

        return [
            'total' => $total,
            'unread' => $unread,
            'low_stock' => $lowStock,
            'expiring' => $expiring,
        ];
    }

    public function render()
    {
        return view('livewire.notifications-index');
    }
}
