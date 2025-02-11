<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

class OrderCreated extends Mailable
{
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        Log::info('Building OrderCreated email', [
            'order_id' => $this->order->id,
            'email' => $this->order->email
        ]);

        return $this->markdown('emails.orders.created')
            ->subject('New Order Created #' . $this->order->id);
    }
}
