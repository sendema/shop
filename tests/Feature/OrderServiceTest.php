<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Services\OrderService;
use App\DTOs\OrderUserDataDTO;
use Illuminate\Support\Facades\Event;
use App\Events\OrderCreatedEvent;
use App\Enums\OrderStatus;
use Mockery;

class OrderServiceTest extends TestCase
{
    public function test_create_order_successfully()
    {
        Event::fake();

        $userData = new OrderUserDataDTO('Test User', 'test@example.com');

        $cartServiceMock = Mockery::mock(\App\Contracts\CartServiceInterface::class);
        $cartServiceMock->shouldReceive('getProducts')->once()->andReturn([
            [
                'name_product' => 'Скрепки',
                'price' => 20,
                'qty' => 2,
            ],
            [
                'name_product' => 'Шариковая ручка',
                'price' => 55.5,
                'qty' => 5,
            ]
        ]);
        $cartServiceMock->shouldReceive('getTotalQuantity')->once()->andReturn(7);
        $cartServiceMock->shouldReceive('getTotalSum')->once()->andReturn(297.5);

        $paypalServiceMock = Mockery::mock(\App\Services\PayPalService::class);
        $paypalServiceMock->shouldReceive('createOrder')->once()->andReturn('test_paypal_order_id');

        $orderService = new OrderService($paypalServiceMock, $cartServiceMock);
        $order = $orderService->createOrder($userData);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('Test User', $order->name);
        $this->assertEquals('test@example.com', $order->email);
        $this->assertEquals('test_paypal_order_id', $order->token);
        $this->assertEquals(OrderStatus::PENDING, $order->status);
        $this->assertEquals(7, $order->qty);
        $this->assertEquals(297.5, $order->sum);

        Event::assertDispatched(OrderCreatedEvent::class);
    }

    public function test_update_order_status()
    {
        $order = new Order();
        $order->name = 'Test User';
        $order->email = 'test@example.com';
        $order->qty = 7;
        $order->sum = 297.5;
        $order->currency = 'USD';
        $order->status = OrderStatus::PENDING;
        $order->token = 'test_token';
        $order->save();

        $paypalServiceMock = Mockery::mock(\App\Services\PayPalService::class);
        $paypalCaptureResponseMock = Mockery::mock(\App\DTOs\PayPalCaptureResponseDTO::class);

        $paypalCaptureResponseMock->shouldReceive('isCompleted')->once()->andReturn(true);
        $paypalServiceMock->shouldReceive('capturePayment')->once()->with('test_token')->andReturn($paypalCaptureResponseMock);

        $cartServiceMock = Mockery::mock(\App\Contracts\CartServiceInterface::class);

        $orderService = new OrderService($paypalServiceMock, $cartServiceMock);
        $updatedOrder = $orderService->updateOrderStatus('test_token');

        $this->assertEquals(OrderStatus::PAID, $updatedOrder->status);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
