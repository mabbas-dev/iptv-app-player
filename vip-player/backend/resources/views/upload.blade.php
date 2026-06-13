@extends('layouts.site')

@section('title', 'Upload Playlist — FOX PLAYER')

@push('head')
@if($recaptchaSiteKey)
<script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
@endif
<style>
    input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus {
        -webkit-text-fill-color: #fbbf24;
        -webkit-box-shadow: 0 0 0 1000px #27272a inset;
    }
</style>
@endpush

@section('content')
<div class="max-w-xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-black text-center mb-2">Upload Playlist</h1>
    <p class="text-zinc-400 text-center text-sm mb-8">Add your own legally authorized IPTV source to your device.</p>

    <div id="successBox" class="hidden mb-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-6 py-5 text-center">
        <p class="text-emerald-400 font-bold text-lg">✓ Successfully added IPTV playlist!</p>
        <p class="text-zinc-300 text-sm mt-2">Open FOX PLAYER on your device and tap <strong>Continue</strong> to sync.</p>
        <p class="text-zinc-500 text-xs mt-2">You can upload again anytime — it replaces the previous playlist.</p>
    </div>

    @if($locked)
    <div class="bg-red-950 border border-red-800 rounded-xl p-6 text-center mb-6">
        <p class="text-red-300 font-semibold">This Device ID is locked.</p>
        <p class="text-red-400 text-sm mt-2">Unlock MAC in FOX PLAYER app Settings before uploading.</p>
    </div>
    @else
    <form id="uploadForm" autocomplete="off" class="space-y-4 bg-zinc-900 border border-zinc-800 rounded-2xl p-6">
        <div style="position:absolute;left:-9999px;opacity:0;" aria-hidden="true">
            <input type="text" name="fake_user" tabindex="-1" autocomplete="username">
            <input type="password" name="fake_pass" tabindex="-1" autocomplete="current-password">
        </div>
        <div>
            <label class="text-xs text-zinc-400 uppercase tracking-wider">Device ID (MAC)</label>
            <input type="text" name="device_code" id="device_code" value="{{ $mac }}"
                   placeholder="A1:B2:C3:D4:E5:F6" required autocomplete="off"
                   class="w-full mt-1 bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 font-mono text-amber-400 focus:border-amber-500 outline-none">
            <p class="text-xs text-zinc-500 mt-1">Found in FOX PLAYER app. Re-uploading replaces the previous playlist.</p>
        </div>

        <div>
            <label class="text-xs text-zinc-400 uppercase tracking-wider">Source type</label>
            <select name="type" id="type" onchange="toggleFields()" class="w-full mt-1 bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">
                <option value="xtream">Xtream Codes API</option>
                <option value="m3u">M3U URL</option>
                <option value="m3u8">M3U8 URL</option>
                <option value="direct">Direct stream URL</option>
            </select>
        </div>

        <div id="xtreamFields" class="space-y-3">
            <input type="url" name="server_url" placeholder="Server URL (http://example.com:8080)" autocomplete="off"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">
            <input type="text" name="username" placeholder="Username" autocomplete="off"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">
            <input type="password" name="password" placeholder="Password" autocomplete="new-password"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">
        </div>

        <div id="urlFields" class="hidden">
            <input type="url" name="url" placeholder="Playlist or stream URL" autocomplete="off"
                   class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">
        </div>

        <input type="url" name="epg_url" placeholder="EPG URL (optional)" autocomplete="off"
               class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-4 py-3 focus:border-amber-500 outline-none">

        <button type="submit" id="submitBtn" class="w-full bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold py-3 rounded-lg transition">
            Upload Playlist
        </button>
        <p id="msg" class="text-center text-sm hidden"></p>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
const initialMac = @json($mac);
function toggleFields() {
    const type = document.getElementById('type').value;
    document.getElementById('xtreamFields').classList.toggle('hidden', type !== 'xtream');
    document.getElementById('urlFields').classList.toggle('hidden', type === 'xtream');
}
toggleFields();

function resetForm(keepMac) {
    const form = document.getElementById('uploadForm');
    form.reset();
    if (keepMac) document.getElementById('device_code').value = keepMac;
    document.getElementById('type').value = 'xtream';
    toggleFields();
    document.getElementById('msg').classList.add('hidden');
}

document.getElementById('uploadForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const msg = document.getElementById('msg');
    const successBox = document.getElementById('successBox');
    const mac = document.getElementById('device_code').value.trim().toUpperCase();

    btn.disabled = true;
    btn.textContent = 'Uploading…';
    successBox.classList.add('hidden');

    const form = new FormData(e.target);
    const body = Object.fromEntries(form.entries());
    delete body.fake_user; delete body.fake_pass;

    @if($recaptchaSiteKey)
    try { body.recaptcha_token = await grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'upload'}); } catch(err) {}
    @endif

    try {
        const res = await fetch('/upload', {
            method: 'POST',
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify(body),
        });
        const data = await res.json();

        if (data.success) {
            successBox.classList.remove('hidden');
            resetForm(mac || initialMac);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            msg.textContent = data.message;
            msg.className = 'text-center text-sm text-red-400';
            msg.classList.remove('hidden');
        }
    } catch(err) {
        msg.textContent = 'Network error. Please try again.';
        msg.className = 'text-center text-sm text-red-400';
        msg.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.textContent = 'Upload Playlist';
});
</script>
@endpush
