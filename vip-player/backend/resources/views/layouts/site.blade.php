<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FOX PLAYER')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gold-gradient { background: linear-gradient(135deg, #FF8C00, #FFB347, #FF8C00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-glow:hover { box-shadow: 0 0 40px rgba(255, 140, 0, 0.15); }
        .legal-content h2 { font-size: 1.125rem; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.5rem; color: #fafafa; }
        .legal-content p { margin-bottom: 0.75rem; line-height: 1.7; color: #a1a1aa; }
        .legal-content ul { list-style: disc; padding-left: 1.25rem; margin-bottom: 0.75rem; color: #a1a1aa; }
        .legal-content li { margin-bottom: 0.35rem; }
    </style>
    @stack('head')
</head>
<body class="bg-zinc-950 text-zinc-100 antialiased min-h-screen flex flex-col">
    <nav class="border-b border-zinc-800/60">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between flex-wrap gap-3">
            <a href="/" class="flex items-center gap-3">
                <img src="/images/fox-brand.png" alt="Fox Player" class="h-10 object-contain">
            </a>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="/download/app" class="px-4 py-2 text-sm font-semibold bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg transition">Download App</a>
                <a href="/upload" class="px-4 py-2 text-sm font-semibold text-zinc-300 hover:text-white transition">Upload</a>
                <a href="/activation" class="px-4 py-2 text-sm font-semibold text-zinc-300 hover:text-white transition">Activate</a>
                <a href="/resellers" class="px-4 py-2 text-sm font-semibold text-zinc-300 hover:text-white transition">Resellers</a>
                <a href="/reseller/register" class="px-4 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-400 text-zinc-950 rounded-lg transition">Become a Reseller</a>
            </div>
        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-zinc-800/60 mt-16">
        <div class="max-w-6xl mx-auto px-6 py-10">
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <p class="text-xs text-zinc-500 leading-relaxed">
                        <strong class="text-zinc-400">Legal disclaimer:</strong>
                        FOX PLAYER is a media player only. We do not provide, host, sell, or distribute any TV channels,
                        movies, playlists, or media content. Users must add their own legally authorized content.
                    </p>
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs">
                    <a href="{{ route('legal.terms') }}" class="text-zinc-400 hover:text-amber-400">Terms & Conditions</a>
                    <a href="{{ route('legal.privacy') }}" class="text-zinc-400 hover:text-amber-400">Privacy Policy</a>
                    <a href="{{ route('legal.refund') }}" class="text-zinc-400 hover:text-amber-400">Refund Policy</a>
                    <a href="{{ route('legal.activation') }}" class="text-zinc-400 hover:text-amber-400">Activation Policy</a>
                    <a href="{{ route('legal.acceptable-use') }}" class="text-zinc-400 hover:text-amber-400">Acceptable Use</a>
                    <a href="{{ route('legal.cookies') }}" class="text-zinc-400 hover:text-amber-400">Cookies</a>
                </div>
            </div>
            <p class="text-xs text-zinc-600 mt-6">© {{ date('Y') }} FOX PLAYER. All rights reserved.</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
