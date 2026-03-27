<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeCheckoutService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.stripe.secret');
    }

    public function publishableKey(): ?string
    {
        return config('services.stripe.key');
    }

    public function createCheckoutSession(Tenant $tenant, string $interval = 'monthly'): Session
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $plan = $tenant->plan ?? Plan::query()->find($tenant->plan_id);
        if (! $plan) {
            throw new \RuntimeException('Plan missing for tenant.');
        }

        $priceKey = $interval === 'yearly'
            ? $plan->stripe_price_yearly_id
            : $plan->stripe_price_monthly_id;

        if (! $priceKey) {
            Log::warning('Stripe price id missing; complete setup in .env and plans table.');
            throw new \RuntimeException('Stripe price not configured for this plan.');
        }

        $base = config('courseflow.base_domain');
        $sub = $tenant->domains->first()?->domain;
        $tenantUrl = 'https://'.$sub.'.'.$base;

        return Session::create([
            'mode' => 'subscription',
            'line_items' => [['price' => $priceKey, 'quantity' => 1]],
            'success_url' => $tenantUrl.'/login?checkout=success',
            'cancel_url' => config('courseflow.central_url').'/subscribe?cancelled=1',
            'customer_email' => $tenant->centralOwner?->email,
            'client_reference_id' => $tenant->id,
            'metadata' => [
                'tenant_id' => $tenant->id,
                'plan_id' => (string) $plan->id,
            ],
        ]);
    }

    /**
     * @return \Stripe\Event
     */
    public function parseWebhook(string $payload, string $signatureHeader)
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret) {
            throw new \RuntimeException('Stripe webhook secret missing.');
        }

        return Webhook::constructEvent($payload, $signatureHeader, $secret);
    }
}
