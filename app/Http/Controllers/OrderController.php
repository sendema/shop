<?php

namespace App\Http\Controllers;

use App\Contracts\CartServiceInterface;
use App\DTOs\OrderUserDataDTO;
use App\Enums\OrderStatus;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    private OrderService $orderService;
    private CartServiceInterface $cartService;

    public function __construct(
        OrderService $orderService,
        CartServiceInterface $cartService
    ) {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    public function checkout(): View
    {
        $cart = $this->cartService->getProducts();
        return view('checkout', compact('cart'));
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $userData = OrderUserDataDTO::fromRequest($request);
        $order = $this->orderService->createOrder($userData);

        return redirect()->route('orders.process-payment', ['orderId' => $order->token]);
    }

    public function processPayment(string $orderId): View
    {
        $order = Order::where('token', $orderId)->firstOrFail();

        return view('process-payment', [
            'orderId' => $orderId,
            'order' => $order
        ]);
    }

    public function cancel(string $orderId): RedirectResponse
    {
        $order = Order::where('token', $orderId)->firstOrFail();
        $order->update(['status' => OrderStatus::CANCELLED]);

        return redirect()->route('orders.error')
            ->with('message', 'Payment was cancelled.');
    }

    public function capture(string $orderId): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus($orderId);

        return response()->json([
            'success' => true,
            'message' => 'Payment captured successfully',
            'status' => $order->status->label()
        ]);
    }

    public function index(): View
    {
        $orders = Order::with('items')->latest()->get();
        return view('orders.index', compact('orders'));
    }
}
