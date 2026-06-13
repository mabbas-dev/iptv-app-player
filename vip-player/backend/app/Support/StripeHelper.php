<?php

namespace App\Support;

use App\Models\AppSetting;

class StripeHelper
{
    public static function isEnabled(): bool
    {
        if (AppSetting::get('stripe_enabled', '1') !== '1') {
            return false;
        }

        return (bool) config('services.stripe.secret');
    }
}
