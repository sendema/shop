<?php
namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusMail;

class SendOrderStatusNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Order $order;
    private OrderStatus $status;

    public function __construct(Order $order, OrderStatus $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function handle(): void
    {
        try {
            Mail::to($this->order->email)
                ->send(new OrderStatusMail($this->order, $this->status->label()));

            Mail::to(config('mail.admin.address'))
                ->send(new OrderStatusMail($this->order, $this->status->label()));

        } catch (\Exception $e) {
            $this->fail($e);
        }
    }
}
