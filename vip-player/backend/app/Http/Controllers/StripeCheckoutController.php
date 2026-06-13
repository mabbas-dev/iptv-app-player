<?php

namespace App\Http\Controllers;

use App\Models\CreditTransaction;
use App\Models\Device;
use App\Models\Plan;
use App\Models\Reseller;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\DeviceActivationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class StripeCheckoutController extends Controller
{
    public function success(Request $request, CreditService $credits): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect('/reseller/buy-credits?status=cancelled');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return redirect('/reseller/buy-credits?status=cancelled');
        }

        // Idempotency: skip if this payment was already credited.
        if (CreditTransaction::where('stripe_payment_id', $session->id)->exists()) {
            return redirect('/reseller/buy-credits?status=success');
        }

        $reseller = Reseller::find($session->metadata->reseller_id);
        $amount = (int) $session->metadata->credits;

        if ($reseller && $amount > 0) {
            $credits->credit(
                $reseller,
                $amount,
                CreditTransaction::TYPE_PURCHASE,
                "Stripe purchase of {$amount} credits",
                stripePaymentId: $session->id,
            );
        }

        return redirect('/reseller/buy-credits?status=success');
    }

    public function activationSuccess(Request $request, DeviceActivationService $activation): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect('/activation?status=cancelled');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = StripeSession::retrieve($sessionId);

        if ($session->payment_status !== 'paid' || ($session->metadata->type ?? '') !== 'device_activation') {
            return redirect('/activation?status=cancelled');
        }

        $mac = $session->metadata->device_code ?? '';

        if (Subscription::where('stripe_payment_id', $session->id)->exists()) {
            return redirect('/activation?mac='.urlencode($mac).'&status=success');
        }

        $device = Device::find($session->metadata->device_id);
        $plan = Plan::where('plan_type', 'customer')
            ->where('is_active', true)
            ->find($session->metadata->plan_id);

        if (! $device || ! $plan) {
            Log::error('Activation payment succeeded but device/plan missing', [
                'session_id' => $session->id,
                'device_id' => $session->metadata->device_id ?? null,
                'plan_id' => $session->metadata->plan_id ?? null,
            ]);

            return redirect('/activation?mac='.urlencode($mac).'&status=error');
        }

        try {
            $activation->activate($device, $plan, stripePaymentId: $session->id);
        } catch (\Throwable $e) {
            Log::error('Activation failed after Stripe payment', [
                'session_id' => $session->id,
                'device_code' => $mac,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return redirect('/activation?mac='.urlencode($mac).'&status=error');
        }

        return redirect('/activation?mac='.urlencode($mac).'&status=success');
    }
}
