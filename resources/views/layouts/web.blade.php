<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-950 text-gray-100 font-[Inter] antialiased">
        <nav class="sticky top-0 z-50 border-b border-gray-800/60 bg-gray-950/80 backdrop-blur-lg">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" wire:navigate class="text-lg font-bold tracking-tight text-white">
                    Deadhock
                </a>

                {{-- Desktop nav --}}
                <div class="hidden items-center gap-6 text-sm text-gray-400 sm:flex">
                    <a href="{{ route('terms') }}" wire:navigate class="transition hover:text-white">Terms</a>
                    <a href="{{ route('privacy') }}" wire:navigate class="transition hover:text-white">Privacy</a>
                    <a href="https://github.com/drewcauchi77/deadhock" target="_blank" class="transition hover:text-white">GitHub</a>
                    <a href="https://discord.com/oauth2/authorize?client_id=1483407552074219530&scope=bot+applications.commands&permissions=34816"
                       target="_blank"
                       class="rounded-lg bg-[#5865F2] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#4752C4]">
                        Add to Discord
                    </a>
                </div>

                {{-- Mobile hamburger --}}
                <button type="button"
                        class="sm:hidden rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                        onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>

            {{-- Mobile menu --}}
            <div id="mobile-menu" class="hidden border-t border-gray-800/60 px-6 pb-4 pt-3 sm:hidden">
                <div class="flex flex-col gap-3 text-sm">
                    <a href="{{ route('terms') }}" wire:navigate class="text-gray-400 transition hover:text-white">Terms of Service</a>
                    <a href="{{ route('privacy') }}" wire:navigate class="text-gray-400 transition hover:text-white">Privacy Policy</a>
                    <a href="https://github.com/drewcauchi77/deadhock" target="_blank" class="text-gray-400 transition hover:text-white">GitHub</a>
                    <a href="https://discord.com/oauth2/authorize?client_id=1483407552074219530&scope=bot+applications.commands&permissions=34816"
                       target="_blank"
                       class="mt-1 inline-flex items-center justify-center rounded-lg bg-[#5865F2] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#4752C4]">
                        Add to Discord
                    </a>
                </div>
            </div>
        </nav>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-gray-800/60 py-8 text-center text-sm text-gray-500">
            <div class="mx-auto max-w-5xl px-6">
                <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                    <span>&copy; {{ date('Y') }} Deadhock</span>
                    <div class="flex gap-4">
                        <a href="{{ route('terms') }}" wire:navigate class="transition hover:text-gray-300">Terms of Service</a>
                        <a href="{{ route('privacy') }}" wire:navigate class="transition hover:text-gray-300">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </footer>

        @livewireScripts
    </body>
</html>
