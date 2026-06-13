<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Reseller;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $resellers = Reseller::query()
            ->where('status', Reseller::STATUS_ACTIVE)
            ->where('show_in_directory', true)
            ->whereNotNull('store_name')
            ->orderBy('store_name')
            ->get();

        return view('landing', [
            'resellers' => $resellers,
            'apkUrl' => AppSetting::get('apk_download_url', '/download/app'),
            'supportEmail' => AppSetting::get('support_email', 'support@foxplayer.app'),
            'supportWhatsapp' => AppSetting::get('support_whatsapp', ''),
        ]);
    }
}
