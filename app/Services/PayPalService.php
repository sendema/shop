<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = 'https://api-m.sandbox.paypal.com';
        $this->clientId = env('PAYPAL_SANDBOX_CLIENT_ID');
        $this->clientSecret = env('PAYPAL_SANDBOX_CLIENT_SECRET');
    }

    private function getAccessToken(): string
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/v1/oauth2/token", [
                    'grant_type' => 'client_credentials'
                ]);

            if (!$response->successful()) {
                Log::error('PayPal authentication failed', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                throw new \Exception('Failed to authenticate with PayPal');
            }

            return $response->json()['access_token'];
        } catch (\Exception $e) {
            Log::error('PayPal token error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createOrder(Order $order): string
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post("{$this->baseUrl}/v2/checkout/orders", [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'amount' => [
                                'currency_code' => $order->currency,
                                'value' => number_format($order->sum, 2, '.', '')
                            ],
                            'reference_id' => (string)$order->id
                        ]
                    ]
                ]);

            if (!$response->successful()) {
                Log::error('PayPal order creation failed', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                throw new \Exception('Failed to create PayPal order');
            }

            return $response->json()['id'];
        } catch (\Exception $e) {
            Log::error('PayPal create order error', [
                'error' => $e->getMessage(),
                'order_id' => $order->id
            ]);
            throw $e;
        }
    }

    public function capturePayment(string $orderId): array
    {
        try {
            $accessToken = $this->getAccessToken();

            $response = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])
                ->withBody('{}', 'application/json')
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if (!$response->successful()) {
                Log::error('PayPal payment capture failed', [
                    'status' => $response->status(),
                    'body' => $response->json()
                ]);
                throw new \Exception('Failed to capture PayPal payment');
            }

            Log::info('PayPal payment captured successfully', [
                'order_id' => $orderId,
                'response' => $response->json()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('PayPal capture error', [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            throw $e;
        }
    }
}
