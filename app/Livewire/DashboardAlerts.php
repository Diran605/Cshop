<?php

namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;

class DashboardAlerts extends Component
{
    public $alerts = [];

    public function mount(): void
    {
        $this->alerts = Alert::query()
            ->forUser(auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    public function markAsRead(int $alertId): void
    {
        $alert = Alert::find($alertId);
        if ($alert && $alert->user_id === auth()->id()) {
            $alert->markAsRead();
            $this->alerts = $this->alerts->reject(fn ($a) => $a->id === $alertId);
        }
    }

    public function render()
    {
        return view('livewire.dashboard-alerts');
    }
}
