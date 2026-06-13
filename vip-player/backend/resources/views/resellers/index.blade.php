@extends('layouts.site')

@section('title', 'Official Resellers — FOX PLAYER')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-black">Official <span class="gold-gradient">Resellers</span></h1>
        <p class="text-zinc-400 mt-3 max-w-2xl mx-auto">
            Purchase FOX PLAYER activation from verified partners. Each store is independently operated.
        </p>
    </div>

    @if($resellers->isEmpty())
    <div class="text-center bg-zinc-900 border border-zinc-800 rounded-2xl p-12">
        <p class="text-zinc-400">No official resellers are listed yet.</p>
        <p class="text-zinc-500 text-sm mt-2">Contact <a href="mailto:support@foxplayer.app" class="text-amber-400">support</a> or <a href="/activation" class="text-amber-400">activate directly</a> when available.</p>
        <a href="/reseller/register" class="inline-block mt-6 px-6 py-3 bg-amber-500 text-zinc-950 font-bold rounded-xl">Become a Reseller</a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($resellers as $reseller)
        <a href="{{ route('resellers.show', $reseller->store_slug) }}"
           class="group bg-zinc-900 border border-zinc-800 hover:border-amber-500/60 rounded-2xl overflow-hidden transition card-glow">
            <div class="h-32 bg-gradient-to-br from-amber-500/10 via-zinc-900 to-zinc-950 flex items-center justify-center">
                @if($reseller->store_image)
                <img src="{{ asset('storage/'.$reseller->store_image) }}" alt="{{ $reseller->store_name }}"
                     class="w-24 h-24 rounded-2xl object-cover border-2 border-zinc-700 shadow-lg group-hover:scale-105 transition">
                @else
                <div class="w-24 h-24 rounded-2xl bg-zinc-800 border border-zinc-700 flex items-center justify-center text-4xl">🏪</div>
                @endif
            </div>
            <div class="p-6">
                <h2 class="text-xl font-bold group-hover:text-amber-400 transition">{{ $reseller->store_name }}</h2>
                @if($reseller->company_name)
                <p class="text-xs text-zinc-500 mt-1">{{ $reseller->company_name }}</p>
                @endif
                <p class="text-zinc-400 text-sm mt-3 line-clamp-3">{{ $reseller->store_description }}</p>
                <span class="inline-block mt-4 text-sm font-semibold text-amber-500">Visit store →</span>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</div>
@endsection
