@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="text-2xl font-bold">Checkout</h2>
            </div>

            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Your Cart</h3>
                    <div class="space-y-2">
                        @foreach($cart as $item)
                            <div class="flex justify-between items-center">
                                <span>{{ $item['name_product'] }} ({{ $item['qty'] }}x)</span>
                                <span>${{ number_format($item['price'] * $item['qty'], 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t">
                        <div class="flex justify-between items-center font-bold">
                            <span>Total:</span>
                            <span>${{ number_format(array_sum(array_map(fn($item) => $item['price'] * $item['qty'], $cart)), 2) }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('orders.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Pay with PayPal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

