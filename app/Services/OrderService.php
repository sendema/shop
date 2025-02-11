<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\OrderCreated;
use App\Mail\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderService
{
    private PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    public function createOrder(array $data, array $cart): Order
    {
        try {
            DB::beginTransaction();

            Log::info('Creating order with data:', ['data' => $data, 'cart' => $cart]);

            $totalQty = array_sum(array_column($cart, 'qty'));
            $totalSum = array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart));

            $order = Order::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'qty' => $totalQty,
                'sum' => $totalSum,
                'currency' => 'USD',
                'status' => Order::STATUS_PENDING,
            ]);

            Log::info('Order created:', ['order_id' => $order->id]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'id_order' => $order->id,
                    'name_product' => $item['name_product'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                ]);
            }

            Log::info('Order items created');

            try {
                $paypalOrderId = $this->paypalService->createOrder($order);
                $order->update(['token' => $paypalOrderId]);
                Log::info('PayPal order created', ['paypal_order_id' => $paypalOrderId]);
            } catch (\Exception $e) {
                Log::error('PayPal order creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            try {
                $this->sendOrderCreatedNotifications($order);
                Log::info('Order notifications sent');
            } catch (\Exception $e) {
                Log::warning('Failed to send order notifications', [
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();
            Log::info('Order transaction committed');

            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    public function updateOrderStatus(string $orderId): Order
    {
        $paypalOrder = $this->paypalService->capturePayment($orderId);
        $order = Order::where('token', $orderId)->firstOrFail();

        $status = $paypalOrder['status'] === 'COMPLETED' ? Order::STATUS_PAID : Order::STATUS_CANCELLED;
        $order->update(['status' => $status]);

        $this->sendStatusNotifications($order, $status === Order::STATUS_PAID ? 'paid' : 'cancelled');

        return $order;
    }

    private function sendOrderCreatedNotifications(Order $order): void
    {
        try {
            Log::info('Attempting to send order created notification', [
                'order_id' => $order->id,
                'customer_email' => $order->email,
                'admin_email' => config('mail.admin.address')
            ]);

            Mail::to($order->email)
                ->send(new OrderCreated($order));

            Mail::to(config('mail.admin.address'))
                ->send(new OrderCreated($order));

            Log::info('Order created notifications sent successfully');
        } catch (\Exception $e) {
            Log::error('Failed to send order notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendStatusNotifications(Order $order, string $status): void
    {
        try {
            Log::info('Attempting to send order status notification', [
                'order_id' => $order->id,
                'status' => $status,
                'customer_email' => $order->email,
                'admin_email' => config('mail.admin.address')
            ]);

            Mail::to($order->email)
                ->send(new OrderStatus($order, $status));

            Mail::to(config('mail.admin.address'))
                ->send(new OrderStatus($order, $status));

            Log::info('Order status notifications sent successfully');
        } catch (\Exception $e) {
            Log::error('Failed to send status notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
