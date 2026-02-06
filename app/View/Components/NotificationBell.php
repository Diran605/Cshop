<?php

namespace App\View\Components;

use App\Models\Alert;
use Illuminate\View\Component;
use Illuminate\View\View;

class NotificationBell extends Component
{
    public int $unreadCount;

    public function __construct()
    {
        $this->unreadCount = Alert::query()
            ->forUser(auth()->id())
            ->unread()
            ->count();
    }

    public function render(): View
    {
        return view('components.notification-bell');
    }
}
