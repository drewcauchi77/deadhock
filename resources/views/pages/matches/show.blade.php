@php
    $isHiddenKingWin = $matchInfo['winning_team'] === 0;
    $duration = floor($matchInfo['duration_s'] / 60) . 'm ' . ($matchInfo['duration_s'] % 60) . 's';
@endphp

<div style="display: flex; justify-content: center; background: #05050a; margin: 0; padding: 0;">
<div style="width: 1248px; min-height: 1056px; position: relative; overflow: hidden; background: #05050a; font-family: 'Segoe UI', system-ui, sans-serif; padding: 32px 36px;">

    {{-- Background atmosphere --}}
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse 100% 60% at 50% 0%, #0d1520 0%, transparent 70%);"></div>
    <div style="position: absolute; inset: 0; background: radial-gradient(circle at 20% 30%, {{ $isHiddenKingWin ? '#f4b00806' : '#4dd2f706' }} 0%, transparent 40%);"></div>
    <div style="position: absolute; inset: 0; background: radial-gradient(circle at 80% 70%, {{ $isHiddenKingWin ? '#4dd2f704' : '#f4b00804' }} 0%, transparent 40%);"></div>

    {{-- Top accent line --}}
    <div style="position: absolute; top: 0; left: 0; right: 0; height: 2px; background: linear-gradient(to right, transparent, {{ $isHiddenKingWin ? '#f4b00850' : '#4dd2f750' }}, transparent);"></div>

    {{-- ============ HEADER ============ --}}
    <div style="position: relative; z-index: 10; text-align: center; margin-bottom: 28px;">
        <div style="display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; margin-bottom: 10px;
            background: {{ $isHiddenKingWin ? '#f4b00810' : '#4dd2f710' }};
            color: {{ $isHiddenKingWin ? '#f4b00880' : '#4dd2f780' }};
            border: 1px solid {{ $isHiddenKingWin ? '#f4b00820' : '#4dd2f720' }};">
            Match #{{ $matchId }}
        </div>
        <div style="display: flex; align-items: center; justify-content: center; gap: 16px;">
            <div style="width: 80px; height: 1px; background: linear-gradient(to right, transparent, {{ $isHiddenKingWin ? '#f4b00840' : '#4dd2f740' }});"></div>
            <div style="font-size: 22px; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase;
                background: linear-gradient(to right, {{ $isHiddenKingWin ? '#f4b008, #f7c948' : '#4dd2f7, #7de0f7' }});
                -webkit-background-clip: text; -webkit-text-fill-color: transparent;
                text-shadow: none;">
                {{ $isHiddenKingWin ? 'The Hidden King' : 'The Archmother' }} Victory
            </div>
            <div style="width: 80px; height: 1px; background: linear-gradient(to left, transparent, {{ $isHiddenKingWin ? '#f4b00840' : '#4dd2f740' }});"></div>
        </div>
        <div style="margin-top: 6px; font-size: 11px; font-weight: 600; color: #ffffff30; letter-spacing: 0.1em;">
            {{ $duration }}
        </div>
    </div>

    {{-- ============ THE ARCHMOTHER TABLE ============ --}}
    <div style="position: relative; z-index: 10; margin-bottom: 20px; border-radius: 14px; overflow: hidden; border: 1px solid #4dd2f718;">

        {{-- Team header --}}
        <div style="display: flex; align-items: center; gap: 12px; padding: 12px 20px;
            background: linear-gradient(135deg, #0a1e35, #0a1a2d);
            border-bottom: 1px solid #4dd2f715;">
            <div style="width: 3px; height: 20px; border-radius: 2px; background: linear-gradient(to bottom, #4dd2f7, #4dd2f740);"></div>
            <div style="font-size: 12px; font-weight: 900; letter-spacing: 0.25em; text-transform: uppercase; color: #4dd2f7;
                text-shadow: 0 0 20px #4dd2f730;">
                The Archmother
            </div>
            @if (!$isHiddenKingWin)
                <div style="margin-left: 8px; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 800; letter-spacing: 0.15em;
                    background: #66bb6a18; color: #66bb6a; border: 1px solid #66bb6a25;">
                    WINNER
                </div>
            @endif
        </div>

        {{-- Column headers --}}
        <div style="display: flex; align-items: center; padding: 8px 20px;
            background: #0a1a2d80; border-bottom: 1px solid #4dd2f710;">
            <div style="flex: 1; min-width: 220px; font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #4dd2f750;">Player</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">K</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">D</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">A</div>
            <div style="width: 90px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">Souls</div>
            <div style="width: 110px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">Player DMG</div>
            <div style="width: 90px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">Healing</div>
            <div style="width: 70px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">CS</div>
            <div style="width: 60px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #4dd2f750;">Denies</div>
        </div>

        {{-- Player rows --}}
        @foreach ($teamArchmother as $index => $player)
            @php
                $hero = $heroes[$player['hero_id']] ?? null;
                $heroImg = $hero['images']['icon_image_small_webp'] ?? null;
                $isTracked = $player['is_tracked'];
            @endphp
            <div style="display: flex; align-items: center; padding: 10px 20px; position: relative;
                background: {{ $index % 2 === 0 ? '#0a1a2d20' : '#0a1a2d38' }};
                border-bottom: 1px solid #4dd2f708;">

                {{-- Tracked player left glow --}}
                @if ($isTracked)
                    <div style="position: absolute; left: 0; top: 4px; bottom: 4px; width: 2px; background: #4dd2f7; border-radius: 1px; box-shadow: 0 0 8px #4dd2f760;"></div>
                @endif

                {{-- Player cell --}}
                <div style="flex: 1; min-width: 220px; display: flex; align-items: center; gap: 12px;">
                    @if ($heroImg)
                        <img src="{{ $heroImg }}" alt="" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover;
                            border: 2px solid {{ $isTracked ? '#4dd2f750' : '#4dd2f720' }};
                            {{ $isTracked ? 'box-shadow: 0 0 10px #4dd2f720;' : '' }}" />
                    @else
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #0a1a2d; border: 2px solid #4dd2f720;"></div>
                    @endif
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: {{ $isTracked ? '#4dd2f7' : '#5a7a90' }};
                            {{ $isTracked ? 'text-shadow: 0 0 12px #4dd2f725;' : '' }}">
                            {{ $player['display_name'] }}
                        </div>
                        @if ($isTracked && $hero)
                            <div style="font-size: 10px; font-weight: 600; color: #4dd2f740; margin-top: 1px;">{{ $hero['name'] }}</div>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #66bb6a; text-shadow: 0 0 8px #66bb6a20;">{{ $player['kills'] }}</div>
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #ef5350; text-shadow: 0 0 8px #ef535020;">{{ $player['deaths'] }}</div>
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #fdd835; text-shadow: 0 0 8px #fdd83520;">{{ $player['assists'] }}</div>
                <div style="width: 90px; text-align: right; font-size: 13px; font-weight: 700; color: #ce93d8;">{{ number_format($player['net_worth']) }}</div>
                <div style="width: 110px; text-align: right; font-size: 13px; font-weight: 700; color: #ffab91;">{{ number_format($player['player_damage']) }}</div>
                <div style="width: 90px; text-align: right; font-size: 13px; font-weight: 700; color: #80cbc4;">{{ number_format($player['player_healing']) }}</div>
                <div style="width: 70px; text-align: right; font-size: 13px; font-weight: 700; color: #b0bec5;">{{ number_format($player['last_hits']) }}</div>
                <div style="width: 60px; text-align: right; font-size: 13px; font-weight: 700; color: #90a4ae;">{{ $player['denies'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- ============ THE HIDDEN KING TABLE ============ --}}
    <div style="position: relative; z-index: 10; border-radius: 14px; overflow: hidden; border: 1px solid #f4b00818;">

        {{-- Team header --}}
        <div style="display: flex; align-items: center; gap: 12px; padding: 12px 20px;
            background: linear-gradient(135deg, #2a1805, #201305);
            border-bottom: 1px solid #f4b00815;">
            <div style="width: 3px; height: 20px; border-radius: 2px; background: linear-gradient(to bottom, #f4b008, #f4b00840);"></div>
            <div style="font-size: 12px; font-weight: 900; letter-spacing: 0.25em; text-transform: uppercase; color: #f4b008;
                text-shadow: 0 0 20px #f4b00830;">
                The Hidden King
            </div>
            @if ($isHiddenKingWin)
                <div style="margin-left: 8px; padding: 2px 8px; border-radius: 3px; font-size: 9px; font-weight: 800; letter-spacing: 0.15em;
                    background: #66bb6a18; color: #66bb6a; border: 1px solid #66bb6a25;">
                    WINNER
                </div>
            @endif
        </div>

        {{-- Column headers --}}
        <div style="display: flex; align-items: center; padding: 8px 20px;
            background: #20130580; border-bottom: 1px solid #f4b00810;">
            <div style="flex: 1; min-width: 220px; font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #f4b00850;">Player</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">K</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">D</div>
            <div style="width: 52px; text-align: center; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">A</div>
            <div style="width: 90px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">Souls</div>
            <div style="width: 110px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">Player DMG</div>
            <div style="width: 90px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">Healing</div>
            <div style="width: 70px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">CS</div>
            <div style="width: 60px; text-align: right; font-size: 10px; font-weight: 700; letter-spacing: 0.15em; color: #f4b00850;">Denies</div>
        </div>

        {{-- Player rows --}}
        @foreach ($teamHiddenKing as $index => $player)
            @php
                $hero = $heroes[$player['hero_id']] ?? null;
                $heroImg = $hero['images']['icon_image_small_webp'] ?? null;
                $isTracked = $player['is_tracked'];
            @endphp
            <div style="display: flex; align-items: center; padding: 10px 20px; position: relative;
                background: {{ $index % 2 === 0 ? '#20130520' : '#20130538' }};
                border-bottom: 1px solid #f4b00808;">

                {{-- Tracked player left glow --}}
                @if ($isTracked)
                    <div style="position: absolute; left: 0; top: 4px; bottom: 4px; width: 2px; background: #f4b008; border-radius: 1px; box-shadow: 0 0 8px #f4b00860;"></div>
                @endif

                {{-- Player cell --}}
                <div style="flex: 1; min-width: 220px; display: flex; align-items: center; gap: 12px;">
                    @if ($heroImg)
                        <img src="{{ $heroImg }}" alt="" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover;
                            border: 2px solid {{ $isTracked ? '#f4b00850' : '#f4b00820' }};
                            {{ $isTracked ? 'box-shadow: 0 0 10px #f4b00820;' : '' }}" />
                    @else
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #201305; border: 2px solid #f4b00820;"></div>
                    @endif
                    <div>
                        <div style="font-size: 14px; font-weight: 700; color: {{ $isTracked ? '#f4b008' : '#8a7a60' }};
                            {{ $isTracked ? 'text-shadow: 0 0 12px #f4b00825;' : '' }}">
                            {{ $player['display_name'] }}
                        </div>
                        @if ($isTracked && $hero)
                            <div style="font-size: 10px; font-weight: 600; color: #f4b00840; margin-top: 1px;">{{ $hero['name'] }}</div>
                        @endif
                    </div>
                </div>

                {{-- Stats --}}
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #66bb6a; text-shadow: 0 0 8px #66bb6a20;">{{ $player['kills'] }}</div>
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #ef5350; text-shadow: 0 0 8px #ef535020;">{{ $player['deaths'] }}</div>
                <div style="width: 52px; text-align: center; font-size: 15px; font-weight: 800; color: #fdd835; text-shadow: 0 0 8px #fdd83520;">{{ $player['assists'] }}</div>
                <div style="width: 90px; text-align: right; font-size: 13px; font-weight: 700; color: #ce93d8;">{{ number_format($player['net_worth']) }}</div>
                <div style="width: 110px; text-align: right; font-size: 13px; font-weight: 700; color: #ffab91;">{{ number_format($player['player_damage']) }}</div>
                <div style="width: 90px; text-align: right; font-size: 13px; font-weight: 700; color: #80cbc4;">{{ number_format($player['player_healing']) }}</div>
                <div style="width: 70px; text-align: right; font-size: 13px; font-weight: 700; color: #b0bec5;">{{ number_format($player['last_hits']) }}</div>
                <div style="width: 60px; text-align: right; font-size: 13px; font-weight: 700; color: #90a4ae;">{{ $player['denies'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Bottom branding --}}
    <div style="position: relative; z-index: 10; text-align: center; margin-top: 20px; font-size: 10px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; color: #ffffff12;">
        DEADHOCK
    </div>
</div>
</div>
