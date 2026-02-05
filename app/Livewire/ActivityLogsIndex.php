<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class ActivityLogsIndex extends Component
{
    public int $branch_id = 0;
    public int $user_id = 0;
    public string $action = '';

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
            $this->branch_id = 0;
        }

        $today = Carbon::today();
        $this->date_from = $today->copy()->startOfMonth()->toDateString();
        $this->date_to = $today->toDateString();

        $this->user_id = 0;
        $this->action = '';
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;

            $today = Carbon::today();
            $this->date_from = $today->copy()->startOfMonth()->toDateString();
            $this->date_to = $today->toDateString();

            $this->user_id = 0;
            $this->action = '';
            $this->search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        }
    }

    public function render()
    {
        $this->syncAuthContext();
        $user = auth()->user();

        if (! $this->isSuperAdmin) {
            $branches = Branch::query()
                ->whereKey((int) ($user?->branch_id ?? 0))
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $users = User::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) ($user?->branch_id ?? 0)))
            ->orderBy('name')
            ->get();

        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        $logs = ActivityLog::query()
            ->with(['branch', 'user'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->user_id > 0, fn ($q) => $q->where('user_id', $this->user_id))
            ->when(trim($this->action) !== '', fn ($q) => $q->where('action', trim($this->action)))
            ->whereBetween('created_at', [$from, $to])
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('description', 'like', $term)
                        ->orWhere('action', 'like', $term)
                        ->orWhere('subject_type', 'like', $term)
                        ->orWhere('subject_id', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->limit(300)
            ->get();

        return view('livewire.activity-logs-index', [
            'branches' => $branches,
            'users' => $users,
            'logs' => $logs,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
