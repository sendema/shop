@extends('layouts.app')

@section('content')
    <div class="min-h-[400px] flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow p-8 text-center">
            <div class="mb-6">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-800 mb-4">Payment Successful!</h2>
            <p class="text-gray-600 mb-8">{{ session('message', 'Thank you for your purchase. Your order has been confirmed.') }}</p>

            <div class="space-y-4">
                <a href="{{ route('orders.index') }}"
                   class="block w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                    View Orders
                </a>
                <a href="{{ route('checkout') }}"
                   class="block w-full text-blue-500 hover:text-blue-600 transition">
                    Return to Shop
                </a>
            </div>
        </div>
    </div>
@endsection
