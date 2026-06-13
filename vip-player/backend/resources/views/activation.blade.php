@extends('layouts.site')

@section('title', 'Activate — FOX PLAYER')

@push('head')
<style>
    input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus {
        -webkit-text-fill-color: #fbbf24;
        -webkit-box-shadow: 0 0 0 1000px #27272a inset;
    }
</style>
@endpush

@section('content')
<div class="max-w-lg mx-auto px-6 py-12">
    <h1 class="text-3xl font-black text-center mb-8">Activate Device</h1>

    @if($status === 'success')
    <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-4 text-center">
        <p class="font-bold text-emerald-400">Payment successful!</p>
        <p class="mt-1 text-sm text-zinc-300">Your device is now activated. Open FOX PLAYER to start watching.</p>
        <a href="/upload?mac={{ urlencode($mac) }}" class="mt-3 inline-block text-sm text-amber-400 hover:text-amber-300">Upload your playlist →</a>
    </div>
    @elseif($status === 'cancelled')
    <div class="mb-6 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-300">
        Payment was cancelled. You can try again below.
    </div>
    @elseif($status === 'error')
    <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-center text-sm text-red-300">
        Something went wrong processing your payment. Please contact support.
    </div>
    @endif

    @unless($stripeEnabled)
    <div class="mb-6 rounded-2xl border border-amber-500/30 bg-amber-500/10 px-6 py-5 text-center space-y-3">
        <p class="font-bold text-amber-300 text-lg">Direct purchase is currently disabled</p>
        <p class="text-sm text-zinc-300 leading-relaxed">
            Kindly contact us for activation, or purchase from an <a href="/resellers" class="text-amber-400 hover:underline font-semibold">official reseller</a>.
            Online Stripe checkout is turned off at this time.
        </p>
        <div class="flex flex-wrap justify-center gap-3 pt-2 text-sm">
            @if($supportEmail)
            <a href="mailto:{{ $supportEmail }}" class="px-4 py-2 bg-zinc-800 rounded-lg hover:bg-zinc-700">{{ $supportEmail }}</a>
            @endif
            @if($supportWhatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supportWhatsapp) }}" target="_blank" class="px-4 py-2 bg-emerald-700 rounded-lg hover:bg-emerald-600">WhatsApp Support</a>
            @endif
            <a href="/resellers" class="px-4 py-2 bg-amber-500 text-zinc-950 font-bold rounded-lg hover:bg-amber-400">Official Resellers</a>
        </div>
    </div>
    @endunless

    <div id="step1" class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-4 {{ ($mac && $status !== 'success') ? 'hidden' : '' }}">
        <label class="text-xs text-zinc-400 uppercase tracking-wider">Step 1 — Enter Device ID</label>
        <input type="text" id="device_code" value="{{ $mac }}" placeholder="A1:B2:C3:D4:E5:F6" autocomplete="off"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 font-mono text-amber-400 focus:border-amber-500 outline-none">
        <p class="text-xs text-zinc-500">Find your Device ID in the FOX PLAYER app on your device.</p>
        <button onclick="lookupDevice()" class="w-full bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold py-3 rounded-lg">Continue</button>
        <p id="lookupMsg" class="text-sm text-center hidden"></p>
    </div>

    @if($stripeEnabled)
    <div id="step2" class="{{ ($mac && $status !== 'success') ? '' : 'hidden' }} mt-6 space-y-3">
        <p class="text-sm text-zinc-400 text-center mb-4">Step 2 — Choose a plan for <span id="macDisplay" class="text-amber-400 font-mono">{{ $mac }}</span></p>
        @forelse($plans as $plan)
        <div class="plan-card bg-zinc-900 border border-zinc-800 hover:border-amber-500 rounded-xl p-5 flex justify-between items-center cursor-pointer transition"
             data-plan-id="{{ $plan->id }}" data-price="{{ $plan->price_usd }}" data-name="{{ $plan->name }}"
             data-duration="{{ $plan->is_lifetime ? 'Lifetime' : $plan->duration_days . ' days' }}"
             onclick="selectPlan(this)">
            <div>
                <p class="font-bold">{{ $plan->name }}</p>
                <p class="text-xs text-zinc-500">@if($plan->is_lifetime) Lifetime @else {{ $plan->duration_days }} days @endif</p>
            </div>
            <p class="text-amber-400 font-black text-xl">${{ number_format($plan->price_usd, 2) }}</p>
        </div>
        @empty
        <p class="text-zinc-500 text-center">No customer plans available. <a href="/resellers" class="text-amber-400">Contact a reseller</a>.</p>
        @endforelse

        <div id="step3" class="hidden mt-6 bg-zinc-900 border border-zinc-800 rounded-2xl p-6 space-y-5">
            <label class="text-xs text-zinc-400 uppercase tracking-wider">Step 3 — Payment</label>
            <div class="flex justify-between items-center rounded-lg bg-zinc-800 px-4 py-3">
                <div>
                    <p id="selectedPlanName" class="font-bold"></p>
                    <p id="selectedPlanDuration" class="text-xs text-zinc-500"></p>
                </div>
                <p id="selectedPlanPrice" class="text-amber-400 font-black text-xl"></p>
            </div>
            <button id="payBtn" onclick="startCheckout()" class="w-full bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold py-3.5 rounded-lg">Pay securely with Stripe</button>
            <p id="payMsg" class="text-sm text-center hidden"></p>
        </div>
        <p class="text-xs text-amber-500/80 text-center mt-4 bg-amber-500/10 border border-amber-500/20 rounded-lg px-4 py-3">
            Verify your Device ID is correct before paying. See our <a href="{{ route('legal.activation') }}" class="underline">Activation Policy</a>.
        </p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
let currentMac = @json($mac);
let selectedPlanId = null;

@if($mac && $status !== 'success' && $stripeEnabled)
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2')?.classList.remove('hidden');
});
@endif

async function lookupDevice() {
    const mac = document.getElementById('device_code').value.trim().toUpperCase();
    const msg = document.getElementById('lookupMsg');
    msg.classList.remove('hidden');
    msg.textContent = 'Checking…';
    msg.className = 'text-sm text-center text-zinc-400';

    const res = await fetch('/activation/lookup', {
        method: 'POST',
        headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({device_code: mac}),
    });
    const data = await res.json();

    if (!data.success) {
        msg.textContent = data.message || 'Device not found.';
        msg.className = 'text-sm text-center text-red-400';
        return;
    }

    currentMac = mac;
    document.getElementById('macDisplay') && (document.getElementById('macDisplay').textContent = mac);
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2')?.classList.remove('hidden');
}

function selectPlan(el) {
    document.querySelectorAll('.plan-card').forEach(c => {
        c.classList.remove('border-amber-500', 'ring-1', 'ring-amber-500/50');
        c.classList.add('border-zinc-800');
    });
    el.classList.add('border-amber-500', 'ring-1', 'ring-amber-500/50');
    selectedPlanId = el.dataset.planId;
    document.getElementById('selectedPlanName').textContent = el.dataset.name;
    document.getElementById('selectedPlanDuration').textContent = el.dataset.duration;
    document.getElementById('selectedPlanPrice').textContent = '$' + parseFloat(el.dataset.price).toFixed(2);
    document.getElementById('step3').classList.remove('hidden');
}

async function startCheckout() {
    if (!currentMac || !selectedPlanId) return;
    const btn = document.getElementById('payBtn');
    const msg = document.getElementById('payMsg');
    btn.disabled = true;
    btn.textContent = 'Redirecting…';

    const res = await fetch('/activation/checkout', {
        method: 'POST',
        headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
        body: JSON.stringify({device_code: currentMac, plan_id: parseInt(selectedPlanId)}),
    });
    const data = await res.json();

    if (!data.success || !data.checkout_url) {
        msg.textContent = data.message || 'Could not start checkout.';
        msg.className = 'text-sm text-center text-red-400';
        msg.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Pay securely with Stripe';
        return;
    }
    window.location.href = data.checkout_url;
}
</script>
@endpush
