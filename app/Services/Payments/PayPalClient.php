<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around PayPal's REST API (v1/v2, no SDK dependency — PayPal's
 * own PHP SDK is legacy; direct REST calls are the current recommended
 * approach). Shared by PayPalGateway (checkout/cancel/refund) and
 * PayPalWebhookController (signature verification), both of which need an
 * OAuth2 access token and the correct sandbox/live base URL.
 */
class PayPalClient
{
    public function __construct()
    {
        if (! config('services.paypal.client_id') || ! config('services.paypal.client_secret')) {
            throw new RuntimeException(
                'PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in .env, '
                .'or switch the active payment gateway back to "test" in Admin > Settings.'
            );
        }
    }

    public function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function accessToken(): string
    {
        return Cache::remember('paypal_access_token_'.config('services.paypal.mode'), 270, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.client_secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
                ->throw();

            return $response->json('access_token');
        });
    }

    public function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())->withToken($this->accessToken())->acceptJson();
    }
}
