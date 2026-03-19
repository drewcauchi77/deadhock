<div class="min-h-screen bg-gray-950 flex items-start justify-center p-6">
    <div class="w-full max-w-[1200px]">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-100 tracking-wide">Match #{{ $matchId }}</h1>
            <div class="mt-2 flex items-center justify-center gap-3 text-sm text-gray-400">
                <span>{{ floor($matchInfo['duration_s'] / 60) }}m {{ $matchInfo['duration_s'] % 60 }}s</span>
                <span class="text-gray-600">|</span>
                @if ($matchInfo['winning_team'] === 0)
                    <span class="font-semibold" style="color: #f4b008;">The Hidden King Victory</span>
                @else
                    <span class="font-semibold" style="color: #4dd2f7;">The Archmother Victory</span>
                @endif
            </div>
        </div>

        {{-- The Archmother --}}
        <div class="overflow-hidden rounded-xl mb-5" style="border: 1px solid #286bb840;">
            <div class="px-5 py-3 flex items-center gap-3" style="background: linear-gradient(135deg, #0d2844, #0d2844aa);">
                <div class="w-1 h-6 rounded-full" style="background-color: #4dd2f7;"></div>
                <h2 class="text-sm font-bold uppercase tracking-widest" style="color: #4dd2f7;">The Archmother</h2>
            </div>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr style="background-color: #0d284480;">
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider" style="color: #4dd2f7aa;">Player</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #4dd2f7aa;">K</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #4dd2f7aa;">D</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #4dd2f7aa;">A</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #4dd2f7aa;">Damage</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #4dd2f7aa;">Healing</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #4dd2f7aa;">CS</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #4dd2f7aa;">Denies</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamArchmother as $index => $player)
                        @php
                            $hero = $heroes[$player['hero_id']] ?? null;
                        @endphp
                        <tr style="background-color: {{ $index % 2 === 0 ? '#0d284425' : '#0d284440' }}; border-bottom: 1px solid #286bb815;">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($hero && isset($hero['images']['icon_image_small_webp']))
                                        <img src="{{ $hero['images']['icon_image_small_webp'] }}" alt="" class="w-9 h-9 rounded-lg" style="border: 2px solid #286bb860;" />
                                    @else
                                        <div class="w-9 h-9 rounded-lg" style="background-color: #0d2844; border: 2px solid #286bb860;"></div>
                                    @endif
                                    <span class="font-semibold" style="color: {{ $player['is_tracked'] ? '#4dd2f7' : '#5a7a90' }};">
                                        {{ $player['display_name'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #66bb6a;">{{ $player['kills'] }}</td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #ef5350;">{{ $player['deaths'] }}</td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #fdd835;">{{ $player['assists'] }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #ffab91;">{{ number_format($player['player_damage']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #80cbc4;">{{ number_format($player['player_healing']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #b0bec5;">{{ number_format($player['last_hits']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #90a4ae;">{{ $player['denies'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- The Hidden King --}}
        <div class="overflow-hidden rounded-xl" style="border: 1px solid #f4b00840;">
            <div class="px-5 py-3 flex items-center gap-3" style="background: linear-gradient(135deg, #402302, #402302aa);">
                <div class="w-1 h-6 rounded-full" style="background-color: #f4b008;"></div>
                <h2 class="text-sm font-bold uppercase tracking-widest" style="color: #f4b008;">The Hidden King</h2>
            </div>
            <table class="w-full text-sm text-left">
                <thead>
                    <tr style="background-color: #40230280;">
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider" style="color: #f4b008aa;">Player</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #f4b008aa;">K</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #f4b008aa;">D</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-center" style="color: #f4b008aa;">A</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #f4b008aa;">Damage</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #f4b008aa;">Healing</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #f4b008aa;">CS</th>
                        <th class="px-5 py-2.5 font-semibold text-xs uppercase tracking-wider text-right" style="color: #f4b008aa;">Denies</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($teamHiddenKing as $index => $player)
                        @php
                            $hero = $heroes[$player['hero_id']] ?? null;
                        @endphp
                        <tr style="background-color: {{ $index % 2 === 0 ? '#40230225' : '#40230240' }}; border-bottom: 1px solid #f4b00815;">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($hero && isset($hero['images']['icon_image_small_webp']))
                                        <img src="{{ $hero['images']['icon_image_small_webp'] }}" alt="" class="w-9 h-9 rounded-lg" style="border: 2px solid #f4b00860;" />
                                    @else
                                        <div class="w-9 h-9 rounded-lg" style="background-color: #402302; border: 2px solid #f4b00860;"></div>
                                    @endif
                                    <span class="font-semibold" style="color: {{ $player['is_tracked'] ? '#f4b008' : '#8a7a60' }};">
                                        {{ $player['display_name'] }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #66bb6a;">{{ $player['kills'] }}</td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #ef5350;">{{ $player['deaths'] }}</td>
                            <td class="px-5 py-3 text-center font-bold" style="color: #fdd835;">{{ $player['assists'] }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #ffab91;">{{ number_format($player['player_damage']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #80cbc4;">{{ number_format($player['player_healing']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #b0bec5;">{{ number_format($player['last_hits']) }}</td>
                            <td class="px-5 py-3 text-right font-medium" style="color: #90a4ae;">{{ $player['denies'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
