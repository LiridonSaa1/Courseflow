<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeCheckoutService $stripe): Response
    {
        try {
            $event = $stripe->parseWebhook($request->getContent(), (string) $request->header('Stripe-Signature'));
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook invalid: '.$e->getMessage());

            return response('Invalid signature', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $tenantId = $session->metadata->tenant_id ?? $session->client_reference_id ?? null;
                if ($tenantId) {
                    $tenant = Tenant::query()->find($tenantId);
                    if ($tenant) {
                        $tenant->update([
                            'stripe_customer_id' => $session->customer,
                            'stripe_subscription_id' => $session->subscription,
                            'subscription_status' => 'active',
                            'subscription_ends_at' => null,
                            'suspended_at' => null,
                        ]);
                        Payment::query()->create([
                            'tenant_id' => $tenant->id,
                            'amount_cents' => $session->amount_total ?? 0,
                            'currency' => $session->currency ?? 'usd',
                            'stripe_payment_intent_id' => $session->payment_intent ?? null,
                            'status' => 'succeeded',
                            'meta' => ['session_id' => $session->id],
                        ]);
                    }
                }
                break;

            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $sub = $event->data->object;
                $tenant = Tenant::query()->where('stripe_subscription_id', $sub->id)->first();
                if ($tenant) {
                    $status = $sub->status ?? 'canceled';
                    $tenant->subscription_status = $status;
                    if ($status === 'active' || $status === 'trialing') {
                        $tenant->subscription_ends_at = null;
                        $tenant->suspended_at = null;
                    }
                    if (! empty($sub->current_period_end)) {
                        $tenant->subscription_ends_at = \Carbon\Carbon::createFromTimestamp($sub->current_period_end);
                    }
                    $tenant->save();
                }
                break;

            case 'invoice.paid':
                $invoice = $event->data->object;
                $tenant = Tenant::query()->where('stripe_customer_id', $invoice->customer)->first();
                if ($tenant) {
                    $tenant->update([
                        'subscription_status' => 'active',
                        'suspended_at' => null,
                    ]);
                }
                break;

            default:
                break;
        }

        return response('ok', 200);
    }
}
