<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $config = config('paypal.' . config('paypal.mode'));
        $this->baseUrl = $config['base_url'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
    }

    public function createOrder(Order $order): string
    {
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
            throw new \Exception('Failed to create PayPal order');
        }

        return $response->json()['id'];
    }

    public function capturePayment(string $orderId): array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])
            ->withBody('{}', 'application/json')
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

        if (!$response->successful()) {
            throw new \Exception('Failed to capture PayPal payment');
        }

        return $response->json();
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials'
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to authenticate with PayPal');
        }

        return $response->json()['access_token'];
    }

}
