<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;
use App\DTOs\OrderDTO;
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

    public function createOrder(array $data, array $cart): Order
    {
        try {
            DB::beginTransaction();

            $orderDTO = OrderDTO::fromRequest($data, $cart, $this->cartService);
            $order = $this->createOrderFromDTO($orderDTO);
            $this->createOrderItems($order, $orderDTO->items);

            OrderCreatedEvent::dispatch($order);

            $order = $this->createPayPalOrder($order);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus(string $orderId): Order
    {
        $paypalOrder = $this->paypalService->capturePayment($orderId);
        $order = Order::where('token', $orderId)->firstOrFail();

        $status = $paypalOrder['status'] === 'COMPLETED'
            ? OrderStatus::PAID
            : OrderStatus::CANCELLED;

        $order->update(['status' => $status]);

        OrderStatusChangedEvent::dispatch($order, $status);

        return $order;
    }

    private function createOrderFromDTO(OrderDTO $dto): Order
    {
        return Order::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'qty' => $dto->totalQty,
            'sum' => $dto->totalSum,
            'currency' => $dto->currency,
            'status' => OrderStatus::PENDING,
        ]);
    }

    private function createOrderItems(Order $order, array $items): void
    {
        foreach ($items as $itemDTO) {
            OrderItem::create([
                'id_order' => $order->id,
                'name_product' => $itemDTO->name,
                'price' => $itemDTO->price,
                'qty' => $itemDTO->qty,
            ]);
        }
    }

    private function createPayPalOrder(Order $order): Order
    {
        $paypalOrderId = $this->paypalService->createOrder($order);
        $order->update(['token' => $paypalOrderId]);
        return $order->fresh();
    }
}
