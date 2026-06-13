<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'trial_days', 'value' => '7', 'group' => 'activation', 'label' => 'Free trial days for new devices'],
            ['key' => 'support_message', 'value' => 'Welcome to FOX PLAYER! Upload your playlist at our website using your Device ID.', 'group' => 'app', 'label' => 'Support message shown in the app'],
            ['key' => 'support_email', 'value' => 'support@foxplayer.app', 'group' => 'app', 'label' => 'Support email'],
            ['key' => 'support_whatsapp', 'value' => '', 'group' => 'app', 'label' => 'Support WhatsApp number'],
            ['key' => 'min_app_version', 'value' => '1.0.0', 'group' => 'app', 'label' => 'Minimum required app version'],
            ['key' => 'force_update', 'value' => '0', 'group' => 'app', 'label' => 'Force app update (1 = on)'],
            ['key' => 'apk_download_url', 'value' => 'https://foxplayer.app/download/app', 'group' => 'app', 'label' => 'APK download URL'],
            ['key' => 'stripe_enabled', 'value' => '1', 'group' => 'billing', 'label' => 'Enable Stripe payments (1 = on)'],
            ['key' => 'credit_price_usd', 'value' => '1.00', 'group' => 'billing', 'label' => 'Price per reseller credit (USD)'],
            ['key' => 'legal_disclaimer', 'value' => 'FOX PLAYER is a media player only. We do not provide, host, sell, or distribute any TV channels, movies, playlists, or media content. Users must add their own legally authorized content.', 'group' => 'legal', 'label' => 'Legal disclaimer'],
            ['key' => 'terms_of_service', 'value' => $this->termsText(), 'group' => 'legal', 'label' => 'Terms & Conditions'],
            ['key' => 'privacy_policy', 'value' => $this->privacyText(), 'group' => 'legal', 'label' => 'Privacy Policy'],
            ['key' => 'refund_policy', 'value' => $this->refundText(), 'group' => 'legal', 'label' => 'Refund Policy'],
            ['key' => 'activation_policy', 'value' => $this->activationText(), 'group' => 'legal', 'label' => 'Activation Policy'],
            ['key' => 'acceptable_use_policy', 'value' => $this->acceptableUseText(), 'group' => 'legal', 'label' => 'Acceptable Use Policy'],
            ['key' => 'cookie_policy', 'value' => $this->cookieText(), 'group' => 'legal', 'label' => 'Cookie Policy'],
            ['key' => 'admin_panel_path', 'value' => 'vip-panel-'.substr(md5('vip-player-admin'), 0, 10), 'group' => 'security', 'label' => 'Admin panel URL path'],
            ['key' => 'recaptcha_site_key', 'value' => '', 'group' => 'security', 'label' => 'reCAPTCHA site key'],
            ['key' => 'recaptcha_secret_key', 'value' => '', 'group' => 'security', 'label' => 'reCAPTCHA secret key'],
            ['key' => 'site_url', 'value' => 'https://foxplayer.app', 'group' => 'app', 'label' => 'Public website URL'],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    protected function termsText(): string
    {
        return <<<'TEXT'
1. AGREEMENT
By accessing FOX PLAYER websites, applications, or services, you agree to these Terms & Conditions. If you do not agree, do not use our services.

2. SERVICE DESCRIPTION
FOX PLAYER is a media player application and platform. We do not provide, host, sell, or distribute television channels, movies, series, playlists, or any copyrighted media content. Users are solely responsible for adding content they are legally authorized to access.

3. USER RESPONSIBILITIES
You must: (a) provide accurate device and account information; (b) use the service only for lawful purposes; (c) comply with copyright and broadcasting laws in your jurisdiction; (d) keep login credentials and device IDs secure.

4. ACTIVATION & SUBSCRIPTIONS
Device activation may be provided via free trial, direct purchase (when enabled), or authorized resellers. Activation periods, pricing, and availability are described at purchase and in our Activation Policy.

5. RESELLERS
Authorized resellers operate independently for customer activation and support. FOX PLAYER is not responsible for reseller pricing, conduct, or third-party promises unless expressly stated.

6. INTELLECTUAL PROPERTY
FOX PLAYER name, branding, software, and website content are protected. You may not reverse engineer, redistribute, or commercially exploit our software without written permission.

7. LIMITATION OF LIABILITY
To the maximum extent permitted by law, FOX PLAYER is provided "as is." We are not liable for service interruptions, third-party IPTV provider failures, data loss, or indirect damages.

8. TERMINATION
We may suspend or terminate access for violations of these terms, fraud, abuse, or legal requirements. You may stop using the service at any time.

9. CHANGES
We may update these terms. Continued use after changes constitutes acceptance. Material changes will be posted on this website.

10. CONTACT
Questions: support@foxplayer.app
TEXT;
    }

    protected function privacyText(): string
    {
        return <<<'TEXT'
1. INTRODUCTION
This Privacy Policy explains how FOX PLAYER collects, uses, and protects information when you use our website, app, and related services.

2. INFORMATION WE COLLECT
• Device information: unique device identifier, platform type, app version, activation status.
• Account/activation data: device codes, subscription dates, payment references (via Stripe when enabled).
• Playlist metadata: URLs and credentials you submit for your own playlists (stored to sync your device).
• Support communications: messages you send to our support channels.
• Website data: basic logs, cookies, and security tokens (see Cookie Policy).

3. HOW WE USE INFORMATION
We use data to: operate and secure the service; activate devices; sync playlists; process payments; prevent abuse; provide support; and improve the product.

4. SHARING
We do not sell personal data. We may share information with: payment processors (Stripe); infrastructure providers; law enforcement when legally required; and authorized resellers only for devices they manage.

5. DATA RETENTION
We retain data while your account/device is active and as needed for legal, billing, and security purposes. You may request deletion subject to legal obligations.

6. SECURITY
We use industry-standard measures including encrypted connections, access controls, and hashed credentials where applicable. No method is 100% secure.

7. YOUR RIGHTS
Depending on your location, you may have rights to access, correct, delete, or restrict processing of your data. Contact support@foxplayer.app.

8. CHILDREN
FOX PLAYER is not directed at children under 13. Parental controls are available in the app for adult content categories.

9. INTERNATIONAL USERS
Data may be processed in countries where our servers operate. By using the service you consent to such processing.

10. UPDATES
We may update this policy. The latest version is always published on this page.
TEXT;
    }

    protected function refundText(): string
    {
        return <<<'TEXT'
1. OVERVIEW
This Refund Policy applies to direct purchases made through FOX PLAYER's website via Stripe when online payments are enabled.

2. ACTIVATION PURCHASES
Device activation grants access to the FOX PLAYER application for a defined period or lifetime as described at checkout. Because activation is delivered digitally and immediately upon successful payment, refunds are generally not available once activation is applied to a device.

3. EXCEPTIONS
We may issue refunds at our discretion for: duplicate charges; technical failure preventing activation after payment; unauthorized transactions reported promptly. Contact support@foxplayer.app within 7 days with your device ID and payment reference.

4. RESELLER PURCHASES
Purchases made through official resellers are subject to each reseller's own refund policy. FOX PLAYER does not process refunds for reseller transactions.

5. CHARGEBACKS
Filing a chargeback without contacting support first may result in device suspension pending investigation.

6. STRIPE
Payments are processed by Stripe. Refunds, when approved, are returned to the original payment method via Stripe.

7. CONTACT
Refund requests: support@foxplayer.app — include Device ID, date of purchase, and reason.
TEXT;
    }

    protected function activationText(): string
    {
        return <<<'TEXT'
1. DEVICE ACTIVATION
Each FOX PLAYER installation receives a unique Device ID (MAC-style code). Activation unlocks full app usage beyond any free trial period.

2. ACTIVATION METHODS
• Free trial: automatically offered to new devices for a limited period (configured by admin).
• Direct purchase: available on our website when Stripe payments are enabled.
• Official resellers: authorized partners who activate devices using reseller credits.

3. SUBSCRIPTION PERIODS
Plans may include monthly, multi-month, annual, or lifetime access. Expiry dates are shown in the app. Renewals stack from your latest expiry date when purchasing additional time.

4. DEVICE ID ACCURACY
You are responsible for entering the correct Device ID during purchase or when requesting reseller activation. Activations applied to the wrong device ID cannot be transferred without support verification.

5. TRIAL & EXPIRY
When a trial or subscription expires, the app prompts renewal. Playlist data may remain on the device but playback requires valid activation.

6. DIRECT PURCHASE DISABLED
When online purchase is disabled, contact support or an official reseller listed at /resellers.

7. RESELLER ACTIVATION
Resellers activate devices from their panel. FOX PLAYER does not guarantee reseller response times or pricing.

8. SUPPORT
Activation issues: support@foxplayer.app — provide your Device ID and purchase method.
TEXT;
    }

    protected function acceptableUseText(): string
    {
        return <<<'TEXT'
1. PURPOSE
This Acceptable Use Policy defines prohibited uses of FOX PLAYER software and services.

2. PROHIBITED CONTENT
You may not use FOX PLAYER to access, stream, or distribute: pirated content; content you are not licensed to view; malware; or material that violates applicable law.

3. PROHIBITED CONDUCT
• Circumventing activation or security controls.
• Automated scraping or abuse of APIs.
• Reselling access without authorization.
• Impersonation, fraud, or harassment via support channels.
• Attempting to compromise our servers or other users' devices.

4. PLAYLIST RESPONSIBILITY
Users supply their own playlist URLs and credentials. FOX PLAYER does not verify legality of third-party sources.

5. ENFORCEMENT
Violations may result in device blocking, account suspension, and reporting to authorities where required.

6. REPORTING
Report abuse to support@foxplayer.app.
TEXT;
    }

    protected function cookieText(): string
    {
        return <<<'TEXT'
1. WHAT ARE COOKIES
Cookies are small text files stored on your device when you visit our website.

2. COOKIES WE USE
• Essential cookies: session management, CSRF protection, authentication for reseller/admin panels.
• Security cookies: reCAPTCHA on the upload page when enabled.
• Preference cookies: basic UI preferences where applicable.

3. THIRD PARTIES
Stripe may set cookies during checkout. Google reCAPTCHA may set cookies on the upload page when configured.

4. MANAGING COOKIES
You can disable cookies in your browser, but some website features may not work correctly.

5. CONTACT
Questions: support@foxplayer.app
TEXT;
    }
}
