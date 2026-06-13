<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return $this->page('terms_of_service', 'Terms & Conditions');
    }

    public function privacy(): View
    {
        return $this->page('privacy_policy', 'Privacy Policy');
    }

    public function refund(): View
    {
        return $this->page('refund_policy', 'Refund Policy');
    }

    public function activation(): View
    {
        return $this->page('activation_policy', 'Activation Policy');
    }

    public function acceptableUse(): View
    {
        return $this->page('acceptable_use_policy', 'Acceptable Use Policy');
    }

    public function cookies(): View
    {
        return $this->page('cookie_policy', 'Cookie Policy');
    }

    protected function page(string $key, string $title, string $view = 'legal.page'): View
    {
        return view($view, [
            'title' => $title,
            'content' => AppSetting::get($key, ''),
            'siteName' => config('app.name', 'FOX PLAYER'),
        ]);
    }
}
