@extends('layouts.app')

@section('content')
    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">Complete Your Payment</h2>

            <div class="mb-6">
                <div class="bg-gray-50 p-4 rounded">
                    <h3 class="font-semibold mb-2">Order Summary</h3>
                    <p>Order Total: ${{ number_format($order->sum, 2) }}</p>
                </div>
            </div>

            <div id="paypal-button-container"></div>
        </div>
    </div>

    @push('scripts')
        <script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_SANDBOX_CLIENT_ID') }}&currency=USD"></script>
        <script>
            paypal.Buttons({
                createOrder: function(data, actions) {
                    return '{{ $orderId }}';
                },

                onApprove: function(data, actions) {
                    document.body.style.cursor = 'wait';

                    return fetch('/orders/capture/' + data.orderID, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                        .then(function(response) {
                            return response.json();
                        })
                        .then(function(orderData) {
                            if (orderData.success) {
                                window.location.href = '/orders/success';
                            } else {
                                window.location.href = '/orders/error';
                            }
                        })
                        .catch(function(error) {
                            console.error('Error:', error);
                            window.location.href = '/orders/error';
                        });
                },

                onCancel: function(data) {
                    window.location.href = '{{ route("orders.cancel", ["orderId" => $orderId]) }}';
                },

                onError: function(err) {
                    console.error('PayPal Error:', err);
                    window.location.href = '{{ route("orders.error") }}';
                }
            }).render('#paypal-button-container')
                .catch(function(error) {
                    console.error('Failed to render PayPal Buttons:', error);
                });
        </script>
    @endpush
@endsection
