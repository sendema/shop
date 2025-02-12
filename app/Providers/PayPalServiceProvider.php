<?php

namespace App\Providers;

use App\Services\PayPalService;
use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PayPalService::class, function ($app) {
            $config = config('paypal.' . config('paypal.mode'));

            return new PayPalService(
                baseUrl: $config['base_url'],
                clientId: $config['client_id'],
                clientSecret: $config['client_secret']
            );
        });
    }
}
