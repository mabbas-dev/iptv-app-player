@extends('layouts.site')

@section('title', 'FOX PLAYER — Premium IPTV Media Player')

@section('content')
    <header class="max-w-6xl mx-auto px-6 pt-20 pb-12 text-center">
        <h1 class="text-5xl md:text-7xl font-black tracking-tight leading-tight">
            The premium media player<br>for <span class="gold-gradient">your own content</span>
        </h1>
        <p class="mt-6 text-lg text-zinc-400 max-w-2xl mx-auto">
            FOX PLAYER is a beautiful, fast IPTV-style media player for Android mobile and Android TV.
            Add your own legally authorized playlists — Xtream, M3U, M3U8 or direct streams.
        </p>
        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ $apkUrl }}" class="px-8 py-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-lg transition flex items-center gap-2">
                <span>⬇</span> Download Android App
            </a>
            <a href="/upload" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold rounded-xl text-lg transition">Upload Playlist</a>
            <a href="/activation" class="px-8 py-4 bg-zinc-800 hover:bg-zinc-700 font-bold rounded-xl text-lg transition">Activate Device</a>
        </div>
        <p class="text-xs text-zinc-500 mt-4">Works on Android phones, tablets & Android TV — one APK.</p>
    </header>

    <section class="max-w-6xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-6">
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 card-glow transition">
            <div class="text-3xl mb-4">📺</div>
            <h3 class="text-xl font-bold mb-2">Live TV, Movies & Series</h3>
            <p class="text-zinc-400 text-sm">Organized categories with search, favorites and recently watched. Remote-friendly UI built for the big screen.</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 card-glow transition">
            <div class="text-3xl mb-4">🔑</div>
            <h3 class="text-xl font-bold mb-2">Device Activation</h3>
            <p class="text-zinc-400 text-sm">Every install gets a unique Device ID. Activate by trial, official reseller, or direct purchase when enabled.</p>
        </div>
        <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 card-glow transition">
            <div class="text-3xl mb-4">🛡️</div>
            <h3 class="text-xl font-bold mb-2">Parental Lock</h3>
            <p class="text-zinc-400 text-sm">PIN-protected adult category locking with securely hashed PINs. Family-safe by default.</p>
        </div>
    </section>

    @if($resellers->isNotEmpty())
    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-10">
            <h2 class="text-3xl font-black">Official <span class="gold-gradient">Resellers</span></h2>
            <p class="text-zinc-400 mt-2 max-w-xl mx-auto">Purchase activation from verified partners. Each reseller runs their own store and support.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($resellers->take(6) as $reseller)
            <a href="{{ route('resellers.show', $reseller->store_slug) }}"
               class="group bg-zinc-900 border border-zinc-800 hover:border-amber-500/50 rounded-2xl p-6 transition card-glow flex flex-col items-center text-center">
                @if($reseller->store_image)
                <img src="{{ asset('storage/'.$reseller->store_image) }}" alt="{{ $reseller->store_name }}"
                     class="w-20 h-20 rounded-2xl object-cover border border-zinc-700 mb-4">
                @else
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500/20 to-zinc-800 border border-zinc-700 flex items-center justify-center text-3xl mb-4">🏪</div>
                @endif
                <h3 class="font-bold text-lg group-hover:text-amber-400 transition">{{ $reseller->store_name }}</h3>
                <p class="text-zinc-500 text-sm mt-2 line-clamp-2">{{ Str::limit($reseller->store_description, 100) }}</p>
                <span class="mt-4 text-xs text-amber-500 font-semibold">Visit store →</span>
            </a>
            @endforeach
        </div>
        @if($resellers->count() > 6)
        <div class="text-center mt-8">
            <a href="/resellers" class="text-amber-400 hover:text-amber-300 font-semibold">View all resellers →</a>
        </div>
        @endif
    </section>
    @endif

    <section class="max-w-6xl mx-auto px-6 py-16">
        <div class="bg-gradient-to-br from-zinc-900 to-zinc-900/40 border border-amber-500/20 rounded-3xl p-10 text-center">
            <h2 class="text-3xl font-black mb-4">Reseller program with <span class="gold-gradient">credits</span></h2>
            <p class="text-zinc-400 max-w-xl mx-auto mb-8">Buy credits, activate customer devices, and automate everything with our reseller API. 1 credit = 1 month.</p>
            <a href="/reseller/register" class="inline-block px-8 py-3 bg-amber-500 hover:bg-amber-400 text-zinc-950 font-bold rounded-xl transition">Become a Reseller</a>
        </div>
    </section>
@endsection
