<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\StripeCheckoutService;
use App\Services\TenantProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubscribeController extends Controller
{
    public function create(StripeCheckoutService $stripe): Response
    {
        return Inertia::render('Central/Subscribe', [
            'plans' => Plan::query()->orderBy('id')->get(),
            'stripePublishableKey' => $stripe->publishableKey(),
            'baseDomain' => config('courseflow.base_domain'),
        ]);
    }

    /**
     * Use the same scheme as the request that submitted /subscribe (e.g. http on Herd).
     * Falling back to CENTRAL_URL avoids redirecting to https://tenant... when only http works locally.
     */
    protected function tenantLoginUrl(Request $request, string $subdomainLabel, string $baseDomain): string
    {
        $scheme = $request->getScheme();
        if (! in_array($scheme, ['http', 'https'], true)) {
            $parsed = parse_url(config('courseflow.central_url'), PHP_URL_SCHEME);

            $scheme = in_array($parsed, ['http', 'https'], true) ? $parsed : 'https';
        }

        return "{$scheme}://{$subdomainLabel}.{$baseDomain}/login";
    }

    public function store(Request $request, TenantProvisioner $provisioner): RedirectResponse
    {
        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:255'],
            'teacher_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:64'],
            'language' => ['required', 'string', 'max:64'],
            'tenant_type' => ['required', Rule::in(['teacher', 'school'])],
            'subdomain' => ['nullable', 'string', 'max:64'],
            'plan_id' => ['required', 'exists:plans,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'billing_interval' => ['nullable', Rule::in(['monthly', 'yearly'])],
        ]);

        $tenant = $provisioner->provision([
            ...$validated,
            'password' => $request->input('password'),
        ]);
        $tenant->load('domains');

        $domain = $tenant->domains->first()?->domain;
        $base = config('courseflow.base_domain');
        $loginUrl = $this->tenantLoginUrl($request, (string) $domain, (string) $base);

        $stripeCheckout = app(StripeCheckoutService::class);
        if ($stripeCheckout->isEnabled()) {
            try {
                $session = $stripeCheckout->createCheckoutSession($tenant, $validated['billing_interval'] ?? 'monthly');

                return redirect($session->url);
            } catch (\Throwable $e) {
                return redirect()
                    ->away($loginUrl)
                    ->with('warning', 'Workspace created. Stripe checkout unavailable: '.$e->getMessage())
                    ->with('workspace_host', $domain.'.'.$base);
            }
        }

        return redirect()
            ->away($loginUrl)
            ->with('success', 'Your workspace is ready at '.$domain.'.'.$base.'. Log in with your email and password.')
            ->with('workspace_host', $domain.'.'.$base);
    }
}
