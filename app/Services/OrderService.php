<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;
use App\DTOs\OrderUserDataDTO;
use App\Enums\OrderStatus;
use App\Events\OrderCreatedEvent;
use App\Events\OrderStatusChangedEvent;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private PayPalService $paypalService;
    private CartServiceInterface $cartService;

    public function __construct(
        PayPalService $paypalService,
        CartServiceInterface $cartService
    ) {
        $this->paypalService = $paypalService;
        $this->cartService = $cartService;
    }

    public function createOrder(OrderUserDataDTO $userData): Order
    {
        $products = $this->cartService->getProducts();
        $totalQty = $this->cartService->getTotalQuantity($products);
        $totalSum = $this->cartService->getTotalSum($products);

        $order = DB::transaction(function () use ($userData, $totalQty, $totalSum, $products) {
            $order = Order::create([
                'name' => $userData->name,
                'email' => $userData->email,
                'qty' => $totalQty,
                'sum' => $totalSum,
                'currency' => 'USD',
                'status' => OrderStatus::PENDING,
            ]);

            foreach ($products as $item) {
                OrderItem::create([
                    'id_order' => $order->id,
                    'name_product' => $item['name_product'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            return $order;
        });

        $paypalOrder = $this->createPayPalOrder($order);

        OrderCreatedEvent::dispatch($paypalOrder);

        return $paypalOrder;
    }

    public function updateOrderStatus(string $orderId): Order
    {
        $paypalResponse = $this->paypalService->capturePayment($orderId);
        $order = Order::where('token', $orderId)->firstOrFail();

        $status = $paypalResponse->isCompleted()
            ? OrderStatus::PAID
            : OrderStatus::CANCELLED;

        $order->update(['status' => $status]);

        OrderStatusChangedEvent::dispatch($order, $status);

        return $order;
    }

    private function createPayPalOrder(Order $order): Order
    {
        $paypalOrderId = $this->paypalService->createOrder($order);
        $order->update(['token' => $paypalOrderId]);
        return $order->fresh();
    }
}
