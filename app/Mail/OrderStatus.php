<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class OrderStatus extends Mailable
{
    public $order;
    public $status;

    public function __construct(Order $order, string $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function build()
    {
        Log::info('Building OrderStatus email', [
            'order_id' => $this->order->id,
            'email' => $this->order->email,
            'status' => $this->status
        ]);

        return $this->markdown('emails.orders.status')
            ->subject('Order #' . $this->order->id . ' Status Update');
    }
}
