@php
    $baseUrl = rtrim(config('app.url'), '/') . '/api/v1/reseller';
@endphp

<x-filament::section
    heading="Reseller API Documentation"
    description="Use your API key to activate devices from your website, Telegram bot, or any automation."
    collapsible
    collapsed
    icon="heroicon-o-book-open"
>
    <div class="space-y-6 text-sm text-gray-300">

        <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4">
            <p class="font-semibold text-amber-400">Authentication</p>
            <p class="mt-1">Send your API key on every request using the <code class="rounded bg-gray-800 px-1.5 py-0.5 font-mono text-xs text-amber-300">X-API-Key</code> header.</p>
            <pre class="mt-3 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">X-API-Key: your_api_key_here</pre>
            <p class="mt-2 text-xs text-gray-400">Base URL: <code class="font-mono text-amber-300">{{ $baseUrl }}</code></p>
        </div>

        {{-- GET balance --}}
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded bg-emerald-600/20 px-2 py-0.5 font-mono text-xs font-bold text-emerald-400">GET</span>
                <code class="font-mono text-sm text-white">/balance</code>
            </div>
            <p class="text-gray-400">Check your current credit balance.</p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">curl -H "X-API-Key: YOUR_KEY" {{ $baseUrl }}/balance</pre>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">{
  "success": true,
  "data": {
    "reseller": "Your Name",
    "balance": 25
  }
}</pre>
        </div>

        {{-- GET plans --}}
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded bg-emerald-600/20 px-2 py-0.5 font-mono text-xs font-bold text-emerald-400">GET</span>
                <code class="font-mono text-sm text-white">/plans</code>
            </div>
            <p class="text-gray-400">List available reseller activation plans and credit costs.</p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">curl -H "X-API-Key: YOUR_KEY" {{ $baseUrl }}/plans</pre>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">{
  "success": true,
  "data": [
    { "id": 1, "name": "1 Month", "duration_days": 30, "credit_cost": 1, "is_trial": false }
  ]
}</pre>
        </div>

        {{-- POST activate --}}
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded bg-blue-600/20 px-2 py-0.5 font-mono text-xs font-bold text-blue-400">POST</span>
                <code class="font-mono text-sm text-white">/devices/activate</code>
            </div>
            <p class="text-gray-400">Activate a device by MAC address (Device ID). Credits are deducted from your wallet.</p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">curl -X POST {{ $baseUrl }}/devices/activate \
  -H "X-API-Key: YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{"device_code":"55:76:DE:4F:F2:A2","plan_id":1}'</pre>
            <p class="mt-2 text-xs text-gray-400">Body parameters:</p>
            <ul class="mt-1 list-inside list-disc text-xs text-gray-400">
                <li><code class="text-amber-300">device_code</code> — MAC address shown in the FOX PLAYER app (e.g. 55:76:DE:4F:F2:A2)</li>
                <li><code class="text-amber-300">plan_id</code> — Plan ID from <code class="text-amber-300">GET /plans</code></li>
            </ul>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">{
  "success": true,
  "message": "Device activated with plan: 1 Month",
  "data": {
    "device_code": "55:76:DE:4F:F2:A2",
    "status": "active",
    "ends_at": "2026-07-13T12:00:00+00:00",
    "credits_spent": 1,
    "balance": 24
  }
}</pre>
        </div>

        {{-- GET devices --}}
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded bg-emerald-600/20 px-2 py-0.5 font-mono text-xs font-bold text-emerald-400">GET</span>
                <code class="font-mono text-sm text-white">/devices</code>
            </div>
            <p class="text-gray-400">List devices you have activated. Optional query: <code class="text-amber-300">?per_page=25</code></p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">curl -H "X-API-Key: YOUR_KEY" "{{ $baseUrl }}/devices?per_page=25"</pre>
        </div>

        {{-- GET transactions --}}
        <div>
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <span class="rounded bg-emerald-600/20 px-2 py-0.5 font-mono text-xs font-bold text-emerald-400">GET</span>
                <code class="font-mono text-sm text-white">/transactions</code>
            </div>
            <p class="text-gray-400">View your credit transaction history. Optional query: <code class="text-amber-300">?per_page=25</code></p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-950 p-3 font-mono text-xs text-gray-200">curl -H "X-API-Key: YOUR_KEY" "{{ $baseUrl }}/transactions?per_page=25"</pre>
        </div>

        <div class="rounded-lg border border-gray-700 bg-gray-900/50 p-4">
            <p class="font-semibold text-white">Bot integration flow</p>
            <ol class="mt-2 list-inside list-decimal space-y-1 text-xs text-gray-400">
                <li>User sends their Device ID (MAC address) to your bot.</li>
                <li>Call <code class="text-amber-300">GET /plans</code> and show available plans.</li>
                <li>User picks a plan — call <code class="text-amber-300">POST /devices/activate</code> with <code class="text-amber-300">device_code</code> + <code class="text-amber-300">plan_id</code>.</li>
                <li>User uploads their own playlist at <code class="text-amber-300">{{ rtrim(config('app.url'), '/') }}/upload?mac=DEVICE_ID</code></li>
            </ol>
            <p class="mt-3 text-xs text-gray-500">Error responses return <code class="text-red-400">{"success": false, "message": "..."}</code> with HTTP 4xx status codes.</p>
        </div>

    </div>
</x-filament::section>
