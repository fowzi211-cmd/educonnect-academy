<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayContract;
use App\Models\Setting;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Resolves the active payment gateway driver. Which driver is "active" is
 * read from the admin-editable Setting first, falling back to the
 * PAYMENT_GATEWAY env var — so an administrator can switch providers from
 * the settings panel without a deployment, per the brief to keep this
 * configurable without touching application code.
 */
class PaymentGatewayManager
{
    /** @var array<string, class-string<PaymentGatewayContract>> */
    protected array $drivers = [
        'test' => TestGateway::class,
        'stripe' => StripeGateway::class,
    ];

    public function __construct(protected Container $container) {}

    public function activeDriverName(): string
    {
        return Setting::get('payment.default_gateway', config('services.payment.default_gateway', 'test'));
    }

    public function driver(?string $name = null): PaymentGatewayContract
    {
        $name ??= $this->activeDriverName();

        if (! isset($this->drivers[$name])) {
            throw new InvalidArgumentException("Unknown payment gateway driver [{$name}].");
        }

        return $this->container->make($this->drivers[$name]);
    }

    /**
     * Resolve by driver name directly — used when acting on an existing
     * subscription/transaction, which always carries the gateway it was
     * created under, regardless of what the *current* active driver is.
     */
    public function driverFor(string $name): PaymentGatewayContract
    {
        return $this->driver($name);
    }

    /** @return array<int, string> */
    public function availableDrivers(): array
    {
        return array_keys($this->drivers);
    }
}
