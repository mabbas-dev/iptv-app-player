<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\CreditTransaction;
use App\Models\Device;
use App\Models\Reseller;
use App\Services\CreditService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(CreditService $credits): void
    {
        $reseller = Reseller::firstOrCreate(
            ['email' => 'reseller@vipplayer.app'],
            [
                'name' => 'Demo Reseller',
                'password' => Hash::make('reseller12345'),
                'company_name' => 'Demo IPTV Shop',
                'status' => Reseller::STATUS_ACTIVE,
                'permissions' => [Reseller::PERM_ACTIVATE],
            ],
        );

        if ($reseller->wallet->balance === 0 && $reseller->creditTransactions()->count() === 0) {
            $credits->credit(
                $reseller,
                25,
                CreditTransaction::TYPE_ADJUSTMENT,
                'Welcome demo credits',
            );
        }

        if ($reseller->apiKeys()->count() === 0) {
            ApiKey::create([
                'reseller_id' => $reseller->id,
                'name' => 'Demo API Key',
                'key' => ApiKey::generateKey(),
                'is_active' => true,
            ]);
        }

        Device::firstOrCreate(
            ['device_uuid' => '00000000-0000-4000-8000-000000000001'],
            [
                'device_code' => Device::generateDeviceCode(),
                'platform' => 'android',
                'app_version' => '1.0.0',
                'status' => Device::STATUS_NEW,
            ],
        );
    }
}
