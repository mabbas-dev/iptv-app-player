<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Plan;
use App\Support\StripeHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class ActivationPageController extends Controller
{
    public function show(Request $request): View
    {
        $mac = strtoupper(trim($request->query('mac', '')));
        $device = $mac ? Device::where('device_code', $mac)->first() : null;

        $plans = Plan::where('is_active', true)
            ->where('plan_type', 'customer')
            ->orderBy('sort_order')
            ->get();

        return view('activation', [
            'mac' => $mac,
            'device' => $device,
            'plans' => $plans,
            'stripeEnabled' => StripeHelper::isEnabled(),
            'siteName' => config('app.name', 'FOX PLAYER'),
            'status' => $request->query('status'),
            'supportEmail' => \App\Models\AppSetting::get('support_email', 'support@foxplayer.app'),
            'supportWhatsapp' => \App\Models\AppSetting::get('support_whatsapp', ''),
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $mac = strtoupper(trim($request->input('device_code', '')));
        $device = Device::where('device_code', $mac)->first();

        if (! $device) {
            return response()->json(['success' => false, 'message' => 'Device not found.'], 404);
        }

        $device->refreshStatus();

        return response()->json([
            'success' => true,
            'data' => [
                'device_code' => $device->device_code,
                'status' => $device->status,
                'expires_at' => $device->expiresAt()?->toIso8601String(),
                'is_lifetime' => $device->is_lifetime,
            ],
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        if (! StripeHelper::isEnabled()) {
            return response()->json(['success' => false, 'message' => 'Direct purchase is currently disabled. Please contact support or an official reseller.'], 503);
        }

        $data = $request->validate([
            'device_code' => ['required', 'string'],
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ]);

        $mac = strtoupper(trim($data['device_code']));
        $device = Device::where('device_code', $mac)->first();

        if (! $device) {
            return response()->json(['success' => false, 'message' => 'Device not found.'], 404);
        }

        if ($device->status === Device::STATUS_BLOCKED) {
            return response()->json(['success' => false, 'message' => 'This device is blocked and cannot be activated.'], 422);
        }

        $plan = Plan::where('is_active', true)
            ->where('plan_type', 'customer')
            ->findOrFail($data['plan_id']);

        Stripe::setApiKey(config('services.stripe.secret'));

        $description = $plan->is_lifetime
            ? "Lifetime access for device {$mac}"
            : "{$plan->duration_days} days access for device {$mac}";

        $session = StripeSession::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) round($plan->price_usd * 100),
                    'product_data' => [
                        'name' => 'FOX PLAYER — '.$plan->name,
                        'description' => $description,
                    ],
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'type' => 'device_activation',
                'device_id' => (string) $device->id,
                'device_code' => $mac,
                'plan_id' => (string) $plan->id,
            ],
            'success_url' => route('activation.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/activation').'?mac='.urlencode($mac).'&status=cancelled',
        ]);

        return response()->json([
            'success' => true,
            'checkout_url' => $session->url,
        ]);
    }
}
