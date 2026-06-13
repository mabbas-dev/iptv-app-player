@extends('layouts.site')

@section('title', $title . ' — FOX PLAYER')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-black mb-2">{{ $title }}</h1>
    <p class="text-zinc-500 text-sm mb-8">Last updated: {{ date('F j, Y') }} · {{ $siteName }}</p>

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-8 legal-content text-sm">
        @if(filled($content))
            {!! nl2br(e($content)) !!}
        @else
            <p class="text-zinc-500">This page is being updated. Please contact support for details.</p>
        @endif
    </div>

    <div class="mt-8 flex flex-wrap gap-4 text-sm">
        <a href="{{ route('legal.terms') }}" class="text-zinc-400 hover:text-amber-400">Terms</a>
        <a href="{{ route('legal.privacy') }}" class="text-zinc-400 hover:text-amber-400">Privacy</a>
        <a href="{{ route('legal.refund') }}" class="text-zinc-400 hover:text-amber-400">Refund</a>
        <a href="{{ route('legal.activation') }}" class="text-zinc-400 hover:text-amber-400">Activation</a>
        <a href="{{ route('legal.acceptable-use') }}" class="text-zinc-400 hover:text-amber-400">Acceptable Use</a>
        <a href="{{ route('legal.cookies') }}" class="text-zinc-400 hover:text-amber-400">Cookies</a>
    </div>
</div>
@endsection
