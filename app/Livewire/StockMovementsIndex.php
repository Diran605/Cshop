<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Livewire\Component;

class StockMovementsIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;
    public string $movement_type = 'all';
    public string $date_from;
    public string $date_to;

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
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $today = Carbon::today();
        $this->date_from = $today->copy()->startOfMonth()->toDateString();
        $this->date_to = $today->toDateString();

        $this->product_id = 0;
        $this->movement_type = 'all';
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->product_id = 0;
            $this->movement_type = 'all';
            $this->search = '';

            $today = Carbon::today();
            $this->date_from = $today->copy()->startOfMonth()->toDateString();
            $this->date_to = $today->toDateString();
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function render()
    {
        $this->syncAuthContext();
        $user = auth()->user();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey($this->branch_id)
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $products = Product::query()->orderBy('name')->get();

        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        $movements = StockMovement::query()
            ->with(['branch', 'product', 'user', 'stockInReceipt', 'salesReceipt'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->product_id > 0, fn ($q) => $q->where('product_id', $this->product_id))
            ->when($this->movement_type !== 'all', fn ($q) => $q->where('movement_type', $this->movement_type))
            ->whereBetween('moved_at', [$from, $to])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->whereHas('product', fn ($qp) => $qp->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            })
            ->orderByDesc('moved_at')
            ->limit(300)
            ->get();

        return view('livewire.stock-movements-index', [
            'branches' => $branches,
            'products' => $products,
            'movements' => $movements,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
