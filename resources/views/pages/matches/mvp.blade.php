@php
    $mvp = collect($mvpPlayers)->firstWhere('mvp_rank', 1);
    $second = collect($mvpPlayers)->firstWhere('mvp_rank', 2);
    $third = collect($mvpPlayers)->firstWhere('mvp_rank', 3);

    $getHeroImage = function (?array $player) use ($heroes): ?string {
        if (!$player) return null;
        $hero = $heroes[$player['hero_id']] ?? null;
        return $hero['images']['icon_hero_card_webp'] ?? null;
    };

    $mvpImage = $getHeroImage($mvp);
    $secondImage = $getHeroImage($second);
    $thirdImage = $getHeroImage($third);

    $mvpWon = $mvp && $winningTeam !== null && (int) $mvp['team'] === $winningTeam;
    $secondWon = $second && $winningTeam !== null && (int) $second['team'] === $winningTeam;
    $thirdWon = $third && $winningTeam !== null && (int) $third['team'] === $winningTeam;
@endphp

<div style="display: flex; justify-content: center; background: #05050a; margin: 0; padding: 0;">
<div style="width: 800px; height: 600px; position: relative; overflow: hidden; background: #05050a; font-family: 'Segoe UI', system-ui, sans-serif;">

    {{-- Deep background layers --}}
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse 120% 80% at 40% 40%, #1a120530 0%, transparent 70%);"></div>
    <div style="position: absolute; inset: 0; background: radial-gradient(ellipse 80% 100% at 40% 60%, #f4b00808 0%, transparent 60%);"></div>

    {{-- Dramatic light burst behind MVP --}}
    <div style="position: absolute; left: 50%; top: 45%; transform: translate(-75%, -50%); width: 700px; height: 700px; background: radial-gradient(circle, #f4b00815 0%, #f4b00808 20%, transparent 55%); pointer-events: none;"></div>

    {{-- Angular accent lines --}}
    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(to right, transparent 5%, #f4b00880 30%, #f4b008 50%, #f4b00880 70%, transparent 95%);"></div>
    <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 1px; background: linear-gradient(to right, transparent, #f4b00830, transparent);"></div>

    {{-- MVP BADGE — top left, cinematic --}}
    <div style="position: absolute; top: 28px; left: 36px; z-index: 30;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="font-size: 13px; font-weight: 900; letter-spacing: 0.35em; text-transform: uppercase; color: #f4b008; text-shadow: 0 0 30px #f4b00860, 0 0 60px #f4b00830;">
                M V P
            </div>
            <div style="width: 60px; height: 2px; background: linear-gradient(to right, #f4b008, transparent);"></div>
        </div>
        <div style="margin-top: 4px; font-size: 10px; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: #f4b00850;">
            Match #{{ $matchId }}
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MVP HERO — LARGE, DOMINANT, CENTER-LEFT --}}
    {{-- ============================================ --}}
    @if ($mvp)
        <div style="position: absolute; left: 20px; top: 0; bottom: 0; width: 380px; z-index: 10;">
            {{-- Hero image — full bleed --}}
            @if ($mvpImage)
                <img src="{{ $mvpImage }}" alt="{{ $mvp['hero_name'] }}"
                     style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: top center;" />
            @endif

            {{-- Cinematic gradient overlays --}}
            <div style="position: absolute; inset: 0; background: linear-gradient(to right, transparent 50%, #05050a 100%);"></div>
            <div style="position: absolute; inset: 0; background: linear-gradient(to top, #05050a 0%, #05050a80 15%, transparent 40%);"></div>
            <div style="position: absolute; inset: 0; background: linear-gradient(to bottom, #05050a 0%, transparent 20%);"></div>

            {{-- Gold edge glow --}}
            <div style="position: absolute; top: 60px; bottom: 60px; left: 0; width: 3px; background: linear-gradient(to bottom, transparent, #f4b00860, #f4b008, #f4b00860, transparent); border-radius: 2px;"></div>

            {{-- Player name + hero — bottom of hero image --}}
            <div style="position: absolute; bottom: 40px; left: 20px; right: 40px; z-index: 15;">
                @if ($mvp['is_tracked'])
                    <div style="font-size: 36px; font-weight: 900; line-height: 1; letter-spacing: -0.02em; color: #ffffff; text-shadow: 0 2px 20px #00000080, 0 0 40px #f4b00825;">
                        {{ $mvp['display_name'] }}
                    </div>
                    <div style="margin-top: 6px; font-size: 14px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: #f4b008; text-shadow: 0 0 20px #f4b00840;">
                        {{ $mvp['hero_name'] }}
                    </div>
                @else
                    <div style="font-size: 36px; font-weight: 900; line-height: 1; letter-spacing: -0.02em; color: #ffffff; text-shadow: 0 2px 20px #00000080, 0 0 40px #f4b00825;">
                        {{ $mvp['hero_name'] }}
                    </div>
                @endif

                {{-- MVP Stats bar --}}
                <div style="margin-top: 16px; display: flex; align-items: center; gap: 20px;">
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 28px; font-weight: 800; color: #66bb6a; text-shadow: 0 0 15px #66bb6a40;">{{ $mvp['kills'] }}</span>
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: #66bb6a80;">K</span>
                    </div>
                    <div style="width: 1px; height: 24px; background: #ffffff15;"></div>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 28px; font-weight: 800; color: #ef5350; text-shadow: 0 0 15px #ef535040;">{{ $mvp['deaths'] }}</span>
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: #ef535080;">D</span>
                    </div>
                    <div style="width: 1px; height: 24px; background: #ffffff15;"></div>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 28px; font-weight: 800; color: #fdd835; text-shadow: 0 0 15px #fdd83540;">{{ $mvp['assists'] }}</span>
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: #fdd83580;">A</span>
                    </div>
                    <div style="width: 1px; height: 24px; background: #ffffff15;"></div>
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 20px; font-weight: 800; color: #ce93d8; text-shadow: 0 0 15px #ce93d840;">{{ number_format($mvp['net_worth']) }}</span>
                        <span style="font-size: 11px; font-weight: 700; letter-spacing: 0.1em; color: #ce93d880;">Souls</span>
                    </div>
                </div>

                {{-- Win/Loss --}}
                <div style="margin-top: 12px; display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase;
                    background: {{ $mvpWon ? '#66bb6a' : '#ef5350' }}18;
                    color: {{ $mvpWon ? '#66bb6a' : '#ef5350' }};
                    border: 1px solid {{ $mvpWon ? '#66bb6a' : '#ef5350' }}30;
                    text-shadow: 0 0 10px {{ $mvpWon ? '#66bb6a' : '#ef5350' }}30;">
                    {{ $mvpWon ? 'VICTORY' : 'DEFEAT' }}
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================ --}}
    {{-- 2ND & 3RD — STACKED ON THE RIGHT --}}
    {{-- ============================================ --}}
    <div style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); display: flex; flex-direction: column; gap: 16px; z-index: 15; width: 320px;">

        {{-- 2nd Place --}}
        @if ($second)
            <div style="position: relative; height: 180px; border-radius: 14px; overflow: hidden; border: 1px solid #c0c0c025; box-shadow: 0 0 30px rgba(192,192,192,0.06);">
                {{-- Background --}}
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #12121a, #0a0a10);"></div>

                {{-- Hero image --}}
                @if ($secondImage)
                    <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 160px; overflow: hidden;">
                        <img src="{{ $secondImage }}" alt="{{ $second['hero_name'] }}"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: top center;" />
                        <div style="position: absolute; inset: 0; background: linear-gradient(to right, #0a0a10 0%, transparent 50%);"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, #0a0a1080 0%, transparent 40%);"></div>
                    </div>
                @endif

                {{-- Rank badge --}}
                <div style="position: absolute; top: 14px; left: 16px; z-index: 5; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: 900; letter-spacing: 0.25em; text-transform: uppercase; background: #c0c0c0; color: #0a0a0f;">
                    2ND
                </div>

                {{-- Win/Loss --}}
                <div style="position: absolute; top: 14px; right: 14px; z-index: 5; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; letter-spacing: 0.15em;
                    background: {{ $secondWon ? '#66bb6a' : '#ef5350' }}15;
                    color: {{ $secondWon ? '#66bb6a' : '#ef5350' }};
                    border: 1px solid {{ $secondWon ? '#66bb6a' : '#ef5350' }}25;">
                    {{ $secondWon ? 'WIN' : 'LOSS' }}
                </div>

                {{-- Player info --}}
                <div style="position: absolute; bottom: 16px; left: 16px; z-index: 5;">
                    @if ($second['is_tracked'])
                        <div style="font-size: 20px; font-weight: 800; color: #ffffff; text-shadow: 0 1px 10px #00000060;">
                            {{ $second['display_name'] }}
                        </div>
                        <div style="margin-top: 2px; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: #c0c0c0a0;">
                            {{ $second['hero_name'] }}
                        </div>
                    @else
                        <div style="font-size: 20px; font-weight: 800; color: #ffffffcc; text-shadow: 0 1px 10px #00000060;">
                            {{ $second['hero_name'] }}
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div style="margin-top: 10px; display: flex; align-items: baseline; gap: 14px;">
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #66bb6a;">{{ $second['kills'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #66bb6a70;">K</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #ef5350;">{{ $second['deaths'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #ef535070;">D</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #fdd835;">{{ $second['assists'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #fdd83570;">A</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 14px; font-weight: 800; color: #ce93d8;">{{ number_format($second['net_worth']) }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #ce93d870;">Souls</span>
                        </div>
                    </div>
                </div>

                {{-- Silver accent line --}}
                <div style="position: absolute; left: 0; top: 30px; bottom: 30px; width: 2px; background: linear-gradient(to bottom, transparent, #c0c0c050, transparent);"></div>
            </div>
        @endif

        {{-- 3rd Place --}}
        @if ($third)
            <div style="position: relative; height: 180px; border-radius: 14px; overflow: hidden; border: 1px solid #cd7f3220; box-shadow: 0 0 30px rgba(205,127,50,0.04);">
                {{-- Background --}}
                <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #11100e, #0a0a0a);"></div>

                {{-- Hero image --}}
                @if ($thirdImage)
                    <div style="position: absolute; right: 0; top: 0; bottom: 0; width: 160px; overflow: hidden;">
                        <img src="{{ $thirdImage }}" alt="{{ $third['hero_name'] }}"
                             style="width: 100%; height: 100%; object-fit: cover; object-position: top center;" />
                        <div style="position: absolute; inset: 0; background: linear-gradient(to right, #0a0a0a 0%, transparent 50%);"></div>
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, #0a0a0a80 0%, transparent 40%);"></div>
                    </div>
                @endif

                {{-- Rank badge --}}
                <div style="position: absolute; top: 14px; left: 16px; z-index: 5; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: 900; letter-spacing: 0.25em; text-transform: uppercase; background: #cd7f32; color: #0a0a0f;">
                    3RD
                </div>

                {{-- Win/Loss --}}
                <div style="position: absolute; top: 14px; right: 14px; z-index: 5; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; letter-spacing: 0.15em;
                    background: {{ $thirdWon ? '#66bb6a' : '#ef5350' }}15;
                    color: {{ $thirdWon ? '#66bb6a' : '#ef5350' }};
                    border: 1px solid {{ $thirdWon ? '#66bb6a' : '#ef5350' }}25;">
                    {{ $thirdWon ? 'WIN' : 'LOSS' }}
                </div>

                {{-- Player info --}}
                <div style="position: absolute; bottom: 16px; left: 16px; z-index: 5;">
                    @if ($third['is_tracked'])
                        <div style="font-size: 20px; font-weight: 800; color: #ffffff; text-shadow: 0 1px 10px #00000060;">
                            {{ $third['display_name'] }}
                        </div>
                        <div style="margin-top: 2px; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: #cd7f32a0;">
                            {{ $third['hero_name'] }}
                        </div>
                    @else
                        <div style="font-size: 20px; font-weight: 800; color: #ffffffcc; text-shadow: 0 1px 10px #00000060;">
                            {{ $third['hero_name'] }}
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div style="margin-top: 10px; display: flex; align-items: baseline; gap: 14px;">
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #66bb6a;">{{ $third['kills'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #66bb6a70;">K</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #ef5350;">{{ $third['deaths'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #ef535070;">D</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 18px; font-weight: 800; color: #fdd835;">{{ $third['assists'] }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #fdd83570;">A</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 3px;">
                            <span style="font-size: 14px; font-weight: 800; color: #ce93d8;">{{ number_format($third['net_worth']) }}</span>
                            <span style="font-size: 9px; font-weight: 700; color: #ce93d870;">Souls</span>
                        </div>
                    </div>
                </div>

                {{-- Bronze accent line --}}
                <div style="position: absolute; left: 0; top: 30px; bottom: 30px; width: 2px; background: linear-gradient(to bottom, transparent, #cd7f3240, transparent);"></div>
            </div>
        @endif
    </div>

    {{-- Bottom branding --}}
    <div style="position: absolute; bottom: 16px; left: 36px; z-index: 20; font-size: 10px; font-weight: 700; letter-spacing: 0.3em; text-transform: uppercase; color: #ffffff15;">
        DEADHOCK
    </div>
</div>
</div>
