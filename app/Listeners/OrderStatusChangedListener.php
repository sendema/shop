<?php
namespace App\Listeners;

use App\Events\OrderStatusChangedEvent;
use App\Jobs\SendOrderStatusNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderStatusChangedListener implements ShouldQueue
{
    public function handle(OrderStatusChangedEvent $event): void
    {
        SendOrderStatusNotifications::dispatch(
            $event->order,
            $event->status
        );
    }
}
