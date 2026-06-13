@extends('layouts.site')

@section('title', $reseller->store_name . ' — FOX PLAYER Reseller')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-16">
    <a href="/resellers" class="text-sm text-zinc-500 hover:text-amber-400 mb-8 inline-block">← All resellers</a>

    <div class="bg-zinc-900 border border-zinc-800 rounded-3xl overflow-hidden">
        <div class="h-40 bg-gradient-to-r from-amber-500/20 via-zinc-900 to-zinc-950 flex items-center justify-center relative">
            @if($reseller->store_image)
            <img src="{{ asset('storage/'.$reseller->store_image) }}" alt="{{ $reseller->store_name }}"
                 class="w-28 h-28 rounded-2xl object-cover border-4 border-zinc-900 shadow-2xl absolute -bottom-14">
            @endif
        </div>

        <div class="pt-20 px-8 pb-8 text-center">
            <h1 class="text-3xl font-black">{{ $reseller->store_name }}</h1>
            @if($reseller->company_name)
            <p class="text-zinc-500 text-sm mt-1">{{ $reseller->company_name }}</p>
            @endif

            @if($reseller->store_description)
            <p class="text-zinc-300 mt-6 leading-relaxed text-left">{{ $reseller->store_description }}</p>
            @endif

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if($reseller->store_url)
                <a href="{{ $reseller->store_url }}" target="_blank" rel="noopener"
                   class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold rounded-xl transition">Visit Website</a>
                @endif
                @if($reseller->store_email)
                <a href="mailto:{{ $reseller->store_email }}"
                   class="px-6 py-3 bg-zinc-800 hover:bg-zinc-700 rounded-xl transition">{{ $reseller->store_email }}</a>
                @endif
                @if($reseller->store_whatsapp)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $reseller->store_whatsapp) }}" target="_blank"
                   class="px-6 py-3 bg-emerald-700 hover:bg-emerald-600 rounded-xl transition">WhatsApp</a>
                @endif
            </div>

            <div class="mt-10 p-5 bg-zinc-800/50 rounded-xl text-left text-sm text-zinc-400">
                <p class="font-semibold text-zinc-300 mb-2">How to activate with this reseller</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Install FOX PLAYER and note your Device ID</li>
                    <li>Contact this reseller to purchase activation</li>
                    <li>They will activate your device from their reseller panel</li>
                    <li>Upload your playlist at <a href="/upload" class="text-amber-400">our upload page</a></li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection
