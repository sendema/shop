<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OrderController extends Controller
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function checkout(): View
    {
        $cart = $this->getCartData();
        return view('checkout', compact('cart'));
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        try {
            Log::info('Starting order creation', ['request' => $request->validated()]);

            $cart = $this->getCartData();
            Log::info('Cart data', ['cart' => $cart]);

            $order = $this->orderService->createOrder($request->validated(), $cart);
            Log::info('Order created successfully', ['order_id' => $order->id]);

            return redirect()->route('orders.process-payment', ['orderId' => $order->token]);
        } catch (\Exception $e) {
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function processPayment(string $orderId): View
    {
        Log::info('Processing payment for order', ['order_id' => $orderId]);

        $order = Order::where('token', $orderId)->firstOrFail();

        return view('process-payment', [
            'orderId' => $orderId,
            'order' => $order
        ]);
    }

    public function cancel(string $orderId)
    {
        $order = Order::where('token', $orderId)->firstOrFail();
        $order->update(['status' => Order::STATUS_CANCELLED]);

        return redirect()->route('orders.error')
            ->with('message', 'Payment was cancelled.');
    }

    public function capture(string $orderId): JsonResponse
    {
        try {
            Log::info('Capturing payment', ['order_id' => $orderId]);

            $this->orderService->updateOrderStatus($orderId);

            return response()->json([
                'success' => true,
                'message' => 'Payment captured successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Payment capture failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed'
            ], 500);
        }
    }

    public function index(): View
    {
        $orders = Order::with('items')->latest()->get();
        return view('orders.index', compact('orders'));
    }

    private function getCartData(): array
    {
        return [
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
        ];
    }
}
