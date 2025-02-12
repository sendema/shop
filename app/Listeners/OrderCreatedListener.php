<?php
namespace App\Listeners;

use App\Events\OrderCreatedEvent;
use App\Jobs\SendOrderCreatedNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderCreatedListener implements ShouldQueue
{
    public function handle(OrderCreatedEvent $event): void
    {
        SendOrderCreatedNotifications::dispatch($event->order);
    }
}
