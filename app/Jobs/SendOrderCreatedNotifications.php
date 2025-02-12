<?php
namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreated;

class SendOrderCreatedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        try {
            Mail::to($this->order->email)
                ->send(new OrderCreated($this->order));

            Mail::to(config('mail.admin.address'))
                ->send(new OrderCreated($this->order));
        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
