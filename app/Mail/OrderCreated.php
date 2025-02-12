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
        return $this->markdown('emails.orders.created')
            ->subject('New Order Created #' . $this->order->id);
    }
}
