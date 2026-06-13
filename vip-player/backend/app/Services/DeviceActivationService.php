<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Device;
use App\Models\Plan;
use App\Models\Reseller;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeviceActivationService
{
    public function __construct(
        protected CreditService $credits,
    ) {}

    /**
     * Activate or renew a device with a plan.
     * Time always stacks onto the latest expiry — never shortens an existing subscription.
     * Direct website purchases clear reseller ownership on the device.
     */
    public function activate(Device $device, Plan $plan, ?Reseller $reseller = null, ?int $adminId = null, ?string $stripePaymentId = null): Subscription
    {
        if (in_array($device->status, [Device::STATUS_BLOCKED], true)) {
            throw new RuntimeException('This device is blocked and cannot be activated.');
        }

        $isDirectPurchase = $stripePaymentId !== null && $reseller === null;
        $isLifetime = (bool) ($plan->is_lifetime ?? false);

        return DB::transaction(function () use ($device, $plan, $reseller, $adminId, $stripePaymentId, $isDirectPurchase, $isLifetime) {
            if ($device->is_lifetime && ! $isLifetime) {
                return Subscription::create([
                    'device_id' => $device->id,
                    'plan_id' => $plan->id,
                    'reseller_id' => $reseller?->id,
                    'starts_at' => now(),
                    'ends_at' => null,
                    'credits_spent' => $reseller ? $plan->credit_cost : 0,
                    'status' => Subscription::STATUS_ACTIVE,
                    'stripe_payment_id' => $stripePaymentId,
                ]);
            }

            $hadRemainingTime = $device->effectiveExpiry()?->isFuture() ?? false;
            $endsAt = $isLifetime ? null : $device->renewalBase()->addDays($plan->duration_days);

            $subscription = Subscription::create([
                'device_id' => $device->id,
                'plan_id' => $plan->id,
                'reseller_id' => $reseller?->id,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'credits_spent' => $reseller ? $plan->credit_cost : 0,
                'status' => Subscription::STATUS_ACTIVE,
                'stripe_payment_id' => $stripePaymentId,
            ]);

            if ($reseller && $plan->credit_cost > 0) {
                $this->credits->debit(
                    $reseller,
                    $plan->credit_cost,
                    CreditTransaction::TYPE_DEBIT,
                    "Activated device {$device->device_code} ({$plan->name})",
                    $subscription->id,
                    $adminId,
                );
            }

            $isTrialOnly = $plan->is_trial && ! $hadRemainingTime && ! $isLifetime;

            $device->update([
                'status' => $isLifetime || ! $isTrialOnly ? Device::STATUS_ACTIVE : Device::STATUS_TRIAL,
                'reseller_id' => $isDirectPurchase ? null : ($reseller?->id ?? $device->reseller_id),
                'trial_started_at' => $isTrialOnly ? now() : $device->trial_started_at,
                'trial_ends_at' => $isTrialOnly ? $endsAt : ($hadRemainingTime ? $device->trial_ends_at : null),
                'subscription_ends_at' => $isLifetime ? null : ($isTrialOnly ? null : $endsAt),
                'is_lifetime' => $isLifetime || $device->is_lifetime,
            ]);

            return $subscription;
        });
    }

    /**
     * Start a trial on a brand-new device using the configured trial days.
     */
    public function startTrial(Device $device, int $days): void
    {
        if ($device->status !== Device::STATUS_NEW) {
            throw new RuntimeException('Trial can only be started on a new device.');
        }

        $device->update([
            'status' => Device::STATUS_TRIAL,
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays($days),
        ]);
    }
}
