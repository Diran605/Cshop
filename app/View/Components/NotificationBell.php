<?php

namespace App\View\Components;

use App\Models\Alert;
use Illuminate\View\Component;
use Illuminate\View\View;

class NotificationBell extends Component
{
    public int $unreadCount;
    public $notifications;

    public function __construct()
    {
        $this->unreadCount = Alert::query()
            ->forUser(auth()->id())
            ->unread()
            ->count();

        // Fetch notifications once in constructor, not in view
        $this->notifications = Alert::query()
            ->forUser(auth()->id())
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        return view('components.notification-bell');
    }
}
