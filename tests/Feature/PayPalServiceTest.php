<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Services\PayPalService;
use Illuminate\Support\Facades\Http;
use App\Enums\OrderStatus;

class PayPalServiceTest extends TestCase
{
    public function test_create_paypal_order()
    {
        $order = new Order();
        $order->name = 'Test User';
        $order->email = 'test@example.com';
        $order->qty = 7;
        $order->sum = 297.5;
        $order->currency = 'USD';
        $order->status = OrderStatus::PENDING;
        $order->save();

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test_access_token',
                'token_type' => 'Bearer'
            ], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'test_paypal_order_id',
                'status' => 'CREATED'
            ], 201)
        ]);

        $paypalService = new PayPalService(
            'https://api-m.sandbox.paypal.com',
            'test_client_id',
            'test_client_secret'
        );

        $orderId = $paypalService->createOrder($order);

        $this->assertEquals('test_paypal_order_id', $orderId);
    }
}
