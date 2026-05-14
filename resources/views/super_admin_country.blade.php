<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    @php
        $countryName = app()->getLocale() == 'ar' ? $country->name : $country->e_name;
    @endphp
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0a0a1a">
    <title>{{ config('app.name') }} – {{ $countryName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}

        :root {
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --safe-left: env(safe-area-inset-left, 0px);
            --safe-right: env(safe-area-inset-right, 0px);
            --c-bg: #0a0a1a;
            --c-surface: rgba(255,255,255,0.04);
            --c-surface-hover: rgba(255,255,255,0.07);
            --c-border: rgba(255,255,255,0.06);
            --c-border-hover: rgba(139,92,246,0.3);
            --c-text: #ffffff;
            --c-text-secondary: rgba(255,255,255,0.55);
            --c-text-tertiary: rgba(255,255,255,0.35);
            --c-violet: #8b5cf6;
            --c-pink: #ec4899;
            --c-cyan: #22d3ee;
            --c-gold: #fbbf24;
            --c-emerald: #34d399;
            --radius-sm: 14px;
            --radius-md: 20px;
            --radius-lg: 24px;
            --radius-xl: 28px;
        }

        html {
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Cairo', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            overflow-x: hidden;
            background: var(--c-bg);
            color: var(--c-text);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-top: var(--safe-top);
            padding-bottom: var(--safe-bottom);
        }

        /* ── Accessibility ── */
        .skip-link {
            position: absolute; top: -100%; left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--c-violet), var(--c-pink));
            color: #fff; padding: 12px 24px;
            border-radius: 0 0 12px 12px;
            font-weight: 700; font-size: 14px;
            z-index: 200; text-decoration: none;
            transition: top 0.3s ease;
        }
        .skip-link:focus { top: 0; }
        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }
        :focus-visible {
            outline: 2px solid var(--c-violet);
            outline-offset: 3px;
        }
        :focus:not(:focus-visible) { outline: none; }

        /* ── Ambient Background ── */
        .ambient-bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(139,92,246,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 70% 50% at 80% 80%, rgba(236,72,153,0.12) 0%, transparent 55%),
                radial-gradient(ellipse 60% 70% at 50% 50%, rgba(6,182,212,0.06) 0%, transparent 50%),
                var(--c-bg);
            will-change: filter;
            animation: ambientShift 25s ease-in-out infinite;
        }
        @keyframes ambientShift {
            0%,100% { filter: hue-rotate(0deg) brightness(1); }
            50% { filter: hue-rotate(15deg) brightness(1.05); }
        }
        .ambient-orb {
            position: fixed; border-radius: 50%;
            filter: blur(80px);
            pointer-events: none; z-index: 0;
            will-change: transform;
        }
        .ambient-orb-1 {
            width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(139,92,246,0.2), transparent 70%);
            top: -10%; left: -15%;
            animation: orbFloat 20s ease-in-out infinite;
        }
        .ambient-orb-2 {
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(236,72,153,0.15), transparent 70%);
            bottom: -10%; right: -15%;
            animation: orbFloat 18s ease-in-out infinite reverse;
        }
        @keyframes orbFloat {
            0%,100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(30px,20px) scale(1.15); }
        }

        /* Stars */
        .stars-field { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
        .star {
            position: absolute; border-radius: 50%; background: #fff;
            animation: starPulse 3s ease-in-out infinite;
        }
        @keyframes starPulse {
            0%,100% { opacity: 0.15; }
            50% { opacity: 0.8; box-shadow: 0 0 4px #fff; }
        }

        /* ── Main Container ── */
        .app-container {
            position: relative; z-index: 2;
            max-width: 430px;
            margin: 0 auto;
            padding: 0 16px;
            padding-bottom: calc(40px + var(--safe-bottom));
        }

        /* ── Language Switcher ── */
        .lang-bar {
            display: flex; justify-content: center;
            gap: 4px; padding: 12px 0 4px;
            position: sticky; top: 0; z-index: 50;
        }
        .lang-bar-inner {
            display: flex; gap: 3px; padding: 4px;
            background: rgba(10,10,26,0.85);
            backdrop-filter: blur(24px) saturate(1.4);
            -webkit-backdrop-filter: blur(24px) saturate(1.4);
            border-radius: 50px;
            border: 1px solid var(--c-border);
        }
        .lang-btn {
            padding: 8px 20px; border: none; border-radius: 50px;
            background: transparent; color: var(--c-text-secondary);
            font-family: inherit; font-size: 13px; font-weight: 700;
            cursor: pointer; transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
            min-height: 36px;
        }
        .lang-btn:active:not(.active) { transform: scale(0.95); }
        .lang-btn.active {
            background: linear-gradient(135deg, var(--c-violet), var(--c-pink));
            color: #fff;
            box-shadow: 0 4px 16px rgba(139,92,246,0.4);
        }

        /* ── Hero Section ── */
        .hero {
            text-align: center;
            padding: 32px 0 28px;
            animation: fadeInUp 0.8s cubic-bezier(0.22,1,0.36,1) both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .flag-container {
            position: relative;
            display: inline-flex;
            align-items: center; justify-content: center;
            margin-bottom: 20px;
        }
        .flag-ring {
            position: absolute;
            width: 120px; height: 120px;
            border-radius: 50%;
            border: 2px solid rgba(139,92,246,0.15);
            animation: ringPulse 3s ease-in-out infinite;
        }
        .flag-ring:nth-child(2) {
            width: 140px; height: 140px;
            border-color: rgba(236,72,153,0.1);
            animation-delay: 1s;
        }
        @keyframes ringPulse {
            0%,100% { transform: scale(1); opacity: 0.6; }
            50% { transform: scale(1.08); opacity: 1; }
        }
        .flag-emoji {
            font-size: 72px;
            filter: drop-shadow(0 0 30px rgba(139,92,246,0.3));
            animation: flagFloat 4s ease-in-out infinite;
            position: relative; z-index: 2;
        }
        @keyframes flagFloat {
            0%,100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-8px) scale(1.05); }
        }
        .hero-title {
            font-size: 32px; font-weight: 900;
            line-height: 1.2;
            background: linear-gradient(135deg, #fff 0%, #c4b5fd 50%, #f0abfc 100%);
            background-size: 300% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 5s ease infinite;
            margin-bottom: 6px;
        }
        @keyframes shimmer {
            0%,100% { background-position: 0% center; }
            50% { background-position: 300% center; }
        }
        .hero-subtitle {
            font-size: 12px; font-weight: 700;
            letter-spacing: 0.2em; text-transform: uppercase;
            color: var(--c-text-tertiary);
        }

        /* ── Glass Card Base ── */
        .glass-card {
            position: relative;
            background: var(--c-surface);
            backdrop-filter: blur(16px) saturate(1.2);
            -webkit-backdrop-filter: blur(16px) saturate(1.2);
            border: 1px solid var(--c-border);
            border-radius: var(--radius-xl);
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.22,1,0.36,1), border-color 0.4s ease;
        }
        .glass-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, transparent 60%);
            pointer-events: none;
        }

        /* ── Super Admin Card ── */
        .sa-section {
            margin-bottom: 16px;
            animation: fadeInUp 0.8s cubic-bezier(0.22,1,0.36,1) 0.15s both;
        }
        .sa-card {
            padding: 24px 20px;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
        }
        .sa-card:active {
            transform: scale(0.98);
        }
        .sa-card-glow {
            position: absolute; top: -1px; left: -1px; right: -1px;
            height: 3px;
            background: linear-gradient(90deg, var(--c-violet), var(--c-pink), var(--c-gold), var(--c-violet));
            background-size: 300% auto;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            animation: gradientSlide 3s linear infinite;
        }
        @keyframes gradientSlide {
            0% { background-position: 0% center; }
            100% { background-position: 300% center; }
        }
        .sa-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 14px;
            background: linear-gradient(135deg, rgba(251,191,36,0.12), rgba(236,72,153,0.08));
            border: 1px solid rgba(251,191,36,0.18);
            border-radius: 50px;
            font-size: 12px; font-weight: 800; color: var(--c-gold);
            margin-bottom: 18px;
        }
        .sa-crown {
            position: absolute; top: -6px;
            font-size: 28px; z-index: 5;
            animation: crownBounce 3s ease-in-out infinite;
            filter: drop-shadow(0 0 12px rgba(251,191,36,0.5));
        }
        [dir="rtl"] .sa-crown { right: 20px; }
        [dir="ltr"] .sa-crown { left: 20px; }
        @keyframes crownBounce {
            0%,100% { transform: rotate(-8deg) translateY(0); }
            50% { transform: rotate(5deg) translateY(-6px); }
        }
        .sa-profile {
            display: flex; align-items: center; gap: 16px;
            margin-bottom: 20px;
        }
        .sa-avatar-wrap {
            position: relative; flex-shrink: 0;
        }
        .sa-avatar {
            width: 72px; height: 72px;
            border-radius: 20px;
            object-fit: cover; display: block;
            border: 2.5px solid rgba(139,92,246,0.35);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        .sa-avatar-glow {
            position: absolute; inset: -6px;
            border-radius: 24px;
            border: 1.5px solid rgba(139,92,246,0.1);
            animation: auraGlow 3s ease-in-out infinite;
        }
        .sa-avatar-glow:nth-child(3) { animation-delay: 1.5s; }
        @keyframes auraGlow {
            0%,100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.06); opacity: 0.9; }
        }
        .sa-info { min-width: 0; flex: 1; }
        .sa-name {
            font-size: 22px; font-weight: 900;
            background: linear-gradient(135deg, #fff, #c4b5fd);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            line-height: 1.3;
        }
        .sa-id {
            font-size: 12px; color: var(--c-text-secondary);
            font-weight: 600; margin-top: 2px;
        }
        .sa-stats {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        }
        .sa-stat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: var(--radius-sm);
            padding: 14px 6px;
            text-align: center;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .sa-stat-card:active {
            transform: scale(0.96);
            background: rgba(139,92,246,0.08);
        }
        .sa-stat-label {
            font-size: 10px; font-weight: 700;
            color: var(--c-text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .sa-stat-value {
            font-size: 26px; font-weight: 900;
            background: linear-gradient(135deg, var(--c-gold), var(--c-pink), var(--c-violet));
            background-size: 200% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 4s ease infinite;
            line-height: 1;
        }
        .sa-stat-card:nth-child(2) .sa-stat-value { animation-delay: 0.4s; }
        .sa-stat-card:nth-child(3) .sa-stat-value { animation-delay: 0.8s; }

        /* ── Live Users Card ── */
        .live-section {
            margin-bottom: 16px;
            animation: fadeInUp 0.8s cubic-bezier(0.22,1,0.36,1) 0.25s both;
        }
        .live-card {
            padding: 24px 20px;
            text-align: center;
        }
        .live-indicator {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 12px; font-weight: 700; color: var(--c-emerald);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .live-dot {
            width: 8px; height: 8px;
            background: var(--c-emerald); border-radius: 50%;
            box-shadow: 0 0 8px var(--c-emerald);
            animation: livePing 1.5s ease-in-out infinite;
        }
        @keyframes livePing {
            0%,100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.6; }
        }
        .live-count {
            font-size: 56px; font-weight: 900; line-height: 1;
            background: linear-gradient(135deg, var(--c-emerald), var(--c-cyan), var(--c-violet));
            background-size: 300% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 4s ease infinite;
        }
        .live-label {
            font-size: 13px; color: var(--c-text-secondary);
            font-weight: 600; margin-top: 6px;
        }

        /* ── Ranking Sections ── */
        .rankings-wrap {
            display: flex; flex-direction: column; gap: 16px;
        }
        .ranking-section {
            animation: fadeInUp 0.6s cubic-bezier(0.22,1,0.36,1) both;
        }
        .ranking-section:nth-child(1) { animation-delay: 0.3s; }
        .ranking-section:nth-child(2) { animation-delay: 0.38s; }
        .ranking-section:nth-child(3) { animation-delay: 0.46s; }
        .ranking-section:nth-child(4) { animation-delay: 0.54s; }
        .ranking-section:nth-child(5) { animation-delay: 0.62s; }
        .ranking-section:nth-child(6) { animation-delay: 0.70s; }
        .ranking-section:nth-child(7) { animation-delay: 0.78s; }

        .ranking-header {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px;
            background: var(--c-surface);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--c-border);
            border-radius: var(--radius-md);
            font-size: 14px; font-weight: 800;
            margin-bottom: 10px;
            position: relative; overflow: hidden;
        }
        .ranking-header::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.02), transparent);
            animation: sweepShine 6s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes sweepShine {
            0%,100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }
        .rh-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; flex-shrink: 0;
        }
        .rh-icon.v1 { background: linear-gradient(135deg, rgba(139,92,246,0.25), rgba(99,102,241,0.25)); }
        .rh-icon.v2 { background: linear-gradient(135deg, rgba(236,72,153,0.25), rgba(244,114,182,0.25)); }
        .rh-icon.v3 { background: linear-gradient(135deg, rgba(6,182,212,0.25), rgba(34,211,238,0.25)); }
        .rh-icon.v4 { background: linear-gradient(135deg, rgba(16,185,129,0.25), rgba(52,211,153,0.25)); }
        .rh-icon.v5 { background: linear-gradient(135deg, rgba(245,158,11,0.25), rgba(251,191,36,0.25)); }
        .rh-icon.v6 { background: linear-gradient(135deg, rgba(99,102,241,0.25), rgba(139,92,246,0.25)); }
        .rh-icon.v7 { background: linear-gradient(135deg, rgba(239,68,68,0.25), rgba(248,113,113,0.25)); }

        .ranking-cards {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        }

        .rank-card {
            background: var(--c-surface);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--c-border);
            border-radius: var(--radius-md);
            padding: 28px 8px 16px;
            text-align: center;
            position: relative; overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease, border-color 0.3s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .rank-card:active {
            transform: scale(0.96);
            border-color: var(--c-border-hover);
        }

        /* Medal badges */
        .rank-badge {
            position: absolute; top: 6px; left: 50%; transform: translateX(-50%);
            width: 26px; height: 26px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 900; color: #fff; z-index: 3;
        }
        .badge-gold {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 3px 12px rgba(245,158,11,0.5);
        }
        .badge-silver {
            background: linear-gradient(135deg, #9ca3af, #6b7280);
            box-shadow: 0 3px 10px rgba(156,163,175,0.3);
        }
        .badge-bronze {
            background: linear-gradient(135deg, #cd7f32, #a0522d);
            box-shadow: 0 3px 10px rgba(205,127,50,0.3);
        }

        .rank-avatar {
            width: 56px; height: 56px;
            border-radius: 16px;
            margin: 8px auto 10px;
            background-size: cover; background-position: center;
            border: 2px solid rgba(255,255,255,0.06);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .rank-name {
            font-size: 12px; font-weight: 700;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            padding: 0 4px; margin-bottom: 4px;
            color: var(--c-text);
        }
        .rank-val {
            font-size: 11px; font-weight: 700;
            background: linear-gradient(135deg, var(--c-gold), var(--c-pink));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        /* ── Empty / Error States ── */
        .ranking-empty, .ranking-error {
            grid-column: 1 / -1;
            text-align: center;
            padding: 28px 16px;
        }
        .ranking-empty-icon, .ranking-error-icon {
            font-size: 28px; margin-bottom: 6px;
        }
        .ranking-empty-text, .ranking-error-text {
            color: var(--c-text-secondary);
            font-size: 12px; font-weight: 600;
        }
        .ranking-retry-btn {
            margin-top: 10px;
            padding: 8px 20px;
            background: rgba(139,92,246,0.15);
            border: 1px solid rgba(139,92,246,0.25);
            border-radius: 50px;
            color: var(--c-violet);
            font-family: inherit;
            font-size: 12px; font-weight: 700;
            cursor: pointer;
            min-height: 36px;
            transition: all 0.3s ease;
            -webkit-tap-highlight-color: transparent;
        }
        .ranking-retry-btn:active {
            transform: scale(0.95);
            background: rgba(139,92,246,0.25);
        }

        /* ── Skeleton Loading ── */
        .skeleton-pulse {
            background: linear-gradient(90deg, rgba(255,255,255,0.03) 25%, rgba(255,255,255,0.07) 50%, rgba(255,255,255,0.03) 75%);
            background-size: 400% 100%;
            animation: skeletonMove 1.5s ease-in-out infinite;
        }
        @keyframes skeletonMove {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-card .rank-avatar {
            background-image: none !important;
            background-color: rgba(255,255,255,0.04);
        }
        .skeleton-line { display: block; }
        .rank-card.loaded {
            animation: cardReveal 0.4s ease both;
        }
        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(12px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Epic Message ── */
        .epic-section {
            margin-top: 16px;
            margin-bottom: 8px;
            animation: fadeInUp 0.8s cubic-bezier(0.22,1,0.36,1) 1s both;
        }
        .epic-card {
            padding: 28px 20px;
        }
        .epic-glow-bar {
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            background: linear-gradient(90deg, var(--c-violet), var(--c-pink), var(--c-gold), var(--c-emerald), var(--c-cyan), var(--c-violet));
            background-size: 300% auto;
            animation: gradientSlide 3s linear infinite;
        }
        .epic-title {
            font-size: 22px; font-weight: 900;
            text-align: center;
            margin-bottom: 18px;
            background: linear-gradient(135deg, var(--c-gold), var(--c-pink), var(--c-violet), var(--c-cyan));
            background-size: 300% auto;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            animation: shimmer 5s ease infinite;
            line-height: 1.4;
        }
        .epic-text {
            font-size: 15px; line-height: 2;
            text-align: center;
            color: var(--c-text-secondary);
        }
        .epic-text strong {
            color: var(--c-gold);
            -webkit-text-fill-color: unset;
        }
        .epic-highlight {
            display: block;
            font-size: 18px; font-weight: 900;
            color: var(--c-gold);
            margin: 14px 0;
            text-shadow: 0 0 16px rgba(251,191,36,0.25);
        }
        .bounce-emoji {
            display: inline-block;
            animation: emojiBounce 2s ease-in-out infinite;
        }
        .bounce-emoji:nth-child(even) { animation-delay: 0.3s; }
        @keyframes emojiBounce {
            0%,100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* ── Ripple Effect ── */
        @keyframes rippleFx {
            to { transform: scale(3); opacity: 0; }
        }

        /* ── Responsive Fine-tuning ── */
        @media (min-width: 430px) {
            .app-container { padding: 0 20px; padding-bottom: calc(40px + var(--safe-bottom)); }
        }

        @media (max-width: 360px) {
            .hero-title { font-size: 26px; }
            .flag-emoji { font-size: 60px; }
            .sa-name { font-size: 18px; }
            .sa-avatar { width: 60px; height: 60px; border-radius: 16px; }
            .sa-avatar-glow { border-radius: 20px; }
            .sa-stat-value { font-size: 22px; }
            .sa-stat-label { font-size: 9px; }
            .live-count { font-size: 46px; }
            .rank-avatar { width: 46px; height: 46px; border-radius: 14px; }
            .rank-name { font-size: 11px; }
            .rank-val { font-size: 10px; }
            .rank-badge { width: 22px; height: 22px; font-size: 10px; border-radius: 7px; }
            .ranking-header { font-size: 13px; padding: 12px 14px; }
            .rh-icon { width: 36px; height: 36px; font-size: 18px; }
            .epic-title { font-size: 18px; }
            .epic-text { font-size: 14px; line-height: 1.9; }
            .epic-highlight { font-size: 16px; }
        }

        @media (max-width: 320px) {
            .app-container { padding: 0 12px; }
            .hero-title { font-size: 24px; }
            .flag-emoji { font-size: 54px; }
            .sa-card { padding: 20px 16px; }
            .sa-stat-card { padding: 10px 4px; }
            .sa-stat-value { font-size: 20px; }
            .live-count { font-size: 40px; }
            .live-card { padding: 20px 16px; }
            .rank-card { padding: 24px 4px 12px; border-radius: 14px; }
            .rank-avatar { width: 40px; height: 40px; border-radius: 12px; }
            .epic-card { padding: 22px 14px; }
        }

        /* Large phones */
        @media (min-width: 390px) {
            .sa-stat-value { font-size: 28px; }
            .live-count { font-size: 60px; }
            .rank-avatar { width: 60px; height: 60px; }
        }

        /* ── Reduced Motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .ambient-bg, .ambient-orb, .stars-field { display: none !important; }
        }
    </style>
</head>
<body>

<a href="#main-content" class="skip-link">{{ __('Skip to main content') }}</a>

<!-- ── Ambient Background ── -->
<div class="ambient-bg" aria-hidden="true"></div>
<div class="ambient-orb ambient-orb-1" aria-hidden="true"></div>
<div class="ambient-orb ambient-orb-2" aria-hidden="true"></div>
<div class="stars-field" id="starsField" aria-hidden="true"></div>

<!-- ── Language Switcher ── -->
<nav class="lang-bar" aria-label="{{ __('Language switcher') }}">
    <div class="lang-bar-inner">
        @php $languages = \App\Models\Language::where('is_enabled', 1)->pluck('name', 'code'); @endphp
        @foreach($languages as $key => $language)
            <button type="button"
                    class="language lang-btn {{ app()->getLocale() === $key ? 'active' : '' }}"
                    data-id="{{ $key }}"
                    aria-label="{{ __('Switch language to :lang', ['lang' => $language]) }}"
                    {{ app()->getLocale() === $key ? 'aria-current=true' : '' }}>
                {{ $language }}
            </button>
        @endforeach
    </div>
</nav>

<main id="main-content">
<div class="app-container">

    <!-- ── Hero ── -->
    <header class="hero">
        <div class="flag-container">
            <div class="flag-ring" aria-hidden="true"></div>
            <div class="flag-ring" aria-hidden="true"></div>
            <span class="flag-emoji" role="img" aria-label="{{ $countryName }} {{ __('flag') }}">{{ $country->iso }}</span>
        </div>
        <h1 class="hero-title">{{ $countryName }}</h1>
        <p class="hero-subtitle">{{ config('app.name') }}</p>
    </header>

    <!-- ── Super Admin ── -->
    @if($superAdmin)
    <section class="sa-section" aria-label="{{ __('Super Admin') }}">
        <div class="glass-card sa-card"
             role="button"
             tabindex="0"
             aria-label="{{ __('View Super Admin profile') }}: {{ $superAdmin->name ?? __('Unknown') }}"
             data-user-id="{{ $superAdmin->appUser?->id ?? '' }}">
            <div class="sa-card-glow" aria-hidden="true"></div>
            <div class="sa-crown" aria-hidden="true">👑</div>
            <div class="sa-badge" aria-hidden="true">🌟 {{ __('Super Admin') }}</div>
            <div class="sa-profile">
                <div class="sa-avatar-wrap">
                    @php
                        $avatarUrl = getImagePath($superAdmin->appUser?->profile?->avatar)
                            ?? "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='72' height='72'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%238b5cf6'/%3E%3Cstop offset='1' stop-color='%23ec4899'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='72' height='72' rx='20' fill='url(%23g)'/%3E%3C/svg%3E";
                    @endphp
                    <img src="{{ $avatarUrl }}" alt="{{ $superAdmin->name ?? __('Admin') }}" class="sa-avatar" loading="eager"/>
                    <div class="sa-avatar-glow" aria-hidden="true"></div>
                    <div class="sa-avatar-glow" aria-hidden="true"></div>
                </div>
                <div class="sa-info">
                    <div class="sa-name">{{ $superAdmin->name ?? '' }}</div>
                    <div class="sa-id">ID: {{ $superAdmin->id ?? '' }}</div>
                </div>
            </div>
            <div class="sa-stats">
                <div class="sa-stat-card">
                    <div class="sa-stat-label">{{ __('Sending Level') }}</div>
                    <div class="sa-stat-value">75</div>
                </div>
                <div class="sa-stat-card">
                    <div class="sa-stat-label">{{ __('Receiving Level') }}</div>
                    <div class="sa-stat-value">82</div>
                </div>
                <div class="sa-stat-card">
                    <div class="sa-stat-label">{{ __('Recharge Level') }}</div>
                    <div class="sa-stat-value">90</div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- ── Live Users ── -->
    <section class="live-section" aria-label="{{ __('Active Users') }}">
        <div class="glass-card live-card" role="status" aria-live="polite" aria-atomic="true">
            <div class="live-indicator">
                <span class="live-dot" aria-hidden="true"></span>
                {{ __('LIVE NOW') }}
            </div>
            <div class="live-count" id="liveNum" aria-hidden="true">{{ $onlineUsers }}</div>
            <span class="sr-only" id="liveNumA11y">{{ number_format($onlineUsers) }} {{ __('Active Users') }}</span>
            <div class="live-label">{{ __('Active Users') }}</div>
        </div>
    </section>

    <!-- ── Rankings ── -->
    <div id="rankings-container" class="rankings-wrap">
        @php
            $sections = [
                ['key' => 'topRooms',          'icon' => '🏠', 'class' => 'v1', 'title' => __('Top 3 Entertainment Rooms') . ' 🎉',    'unit' => __('members'),  'emoji' => ''],
                ['key' => 'topSenders',        'icon' => '💎', 'class' => 'v2', 'title' => __('Top 3 Generous Supporters') . ' 💰',     'unit' => '',             'emoji' => '💎'],
                ['key' => 'topReceivers',      'icon' => '🎤', 'class' => 'v3', 'title' => __('Top 3 Star Hosts') . ' ⭐',               'unit' => '',             'emoji' => '💎'],
                ['key' => 'topAgencies',       'icon' => '🏢', 'class' => 'v4', 'title' => __('Top 3 Host Agencies') . ' 🚀',           'unit' => __('hosts'),    'emoji' => ''],
                ['key' => 'topChargeAgencies', 'icon' => '💰', 'class' => 'v5', 'title' => __('Top 3 Recharge Agencies') . ' 💵',       'unit' => '',             'emoji' => '💵'],
                ['key' => 'topBds',            'icon' => '👥', 'class' => 'v6', 'title' => __('Top 3 Most Active BD') . ' 🎯',          'unit' => __('agency'),   'emoji' => ''],
                ['key' => 'topGamers',         'icon' => '🎮', 'class' => 'v7', 'title' => __('Top 3 Gamers') . ' 🏅',                  'unit' => '',             'emoji' => '⚡'],
            ];
        @endphp

        @foreach($sections as $sec)
        <section class="ranking-section" data-section="{{ $sec['key'] }}" aria-labelledby="heading-{{ $sec['key'] }}">
            <h3 class="ranking-header" id="heading-{{ $sec['key'] }}">
                <span class="rh-icon {{ $sec['class'] }}" aria-hidden="true">{{ $sec['icon'] }}</span>
                {{ $sec['title'] }}
            </h3>
            <div class="ranking-cards" id="cards-{{ $sec['key'] }}" data-unit="{{ $sec['unit'] }}" data-emoji="{{ $sec['emoji'] }}" data-rank-label="{{ __('Rank') }}" aria-busy="true" aria-label="{{ __('Loading rankings') }}">
                @for($i = 0; $i < 3; $i++)
                <div class="rank-card skeleton-card" aria-hidden="true">
                    <div class="rank-badge {{ $i===0?'badge-gold':($i===1?'badge-silver':'badge-bronze') }}">{{ $i+1 }}</div>
                    <div class="rank-avatar skeleton-pulse"></div>
                    <div class="skeleton-line skeleton-pulse" style="width:65%;height:12px;margin:5px auto;border-radius:6px;"></div>
                    <div class="skeleton-line skeleton-pulse" style="width:45%;height:10px;margin:3px auto;border-radius:6px;"></div>
                </div>
                @endfor
            </div>
        </section>
        @endforeach
    </div>

    <!-- ── Epic Message ── -->
    <section class="epic-section" aria-labelledby="epic-heading">
        <div class="glass-card epic-card">
            <div class="epic-glow-bar" aria-hidden="true"></div>
            <h2 class="epic-title" id="epic-heading">
                {{ $country->iso }} {{ __('Epic Message to Heroes of :country', ['country' => $countryName]) }} {{ $country->iso }}
            </h2>
            <p class="epic-text">
                <span class="bounce-emoji" aria-hidden="true">🔥</span> {{ __(':app LIFE Legends', ['app' => config('app.name')]) }} <span class="bounce-emoji" aria-hidden="true">🔥</span><br/><br/>
                {{ __('You are not just players... You are the Entertainment Army!') }} <span class="bounce-emoji" aria-hidden="true">🎮</span><br/>
                {{ __('Every room you open becomes an arena of joy and laughter!') }} <span class="bounce-emoji" aria-hidden="true">🎉</span><br/>
                {{ __('Every gift you send plants smiles on faces!') }} <span class="bounce-emoji" aria-hidden="true">💝</span><br/>
                {{ __('Every game you play writes :country\'s name in golden letters!', ['country' => $countryName]) }} <span class="bounce-emoji" aria-hidden="true">⚡</span><br/><br/>
                <strong class="epic-highlight"><span class="bounce-emoji" aria-hidden="true">🏆</span> {{ __('Make the World Dance to :country\'s rhythm', ['country' => $countryName]) }} <span class="bounce-emoji" aria-hidden="true">🏆</span></strong>
                {{ __('Play... Dance... Sing... Laugh... Spread Happiness!') }} <span class="bounce-emoji" aria-hidden="true">🎊</span><br/>
                {{ __('Make every minute in :app LIFE an authentic celebration!', ['app' => config('app.name')]) }} <span class="bounce-emoji" aria-hidden="true">🎪</span><br/><br/>
                <strong>{{ __(':country is strong with you... First place awaits!', ['country' => $countryName]) }} <span class="bounce-emoji" aria-hidden="true">🦅</span></strong>
            </p>
        </div>
    </section>

</div>
</main>

<script>
(function(){
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Language Switcher ──
    document.querySelectorAll('.language').forEach(function(btn) {
        btn.addEventListener('click', function() {
            fetch("{{ url('/locale') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: new URLSearchParams({ locale: this.dataset.id })
            }).then(function() {
                location.reload();
            });
        });
    });

    function sendMessage(userId) {
        window.postMessage('open_profile:' + userId, '*');
    }

    // ── Stars ──
    if (!prefersReducedMotion) {
        var field = document.getElementById('starsField');
        if (field) {
            var count = 50;
            for (var i = 0; i < count; i++) {
                var s = document.createElement('div');
                s.className = 'star';
                var sz = 1 + Math.random() * 2;
                s.style.cssText = 'width:' + sz + 'px;height:' + sz + 'px;left:' + Math.random() * 100 + '%;top:' + Math.random() * 100 + '%;animation-delay:' + Math.random() * 5 + 's;animation-duration:' + (2 + Math.random() * 4) + 's';
                field.appendChild(s);
            }
        }
    }

    // ── Counter Animation ──
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('liveNum');
        if (el) {
            var target = parseInt(el.textContent.replace(/,/g, ''));
            if (!isNaN(target)) {
                if (prefersReducedMotion) {
                    el.textContent = target.toLocaleString();
                } else {
                    var current = 0;
                    var step = Math.ceil(target / 50);
                    var timer = setInterval(function() {
                        current += step;
                        if (current >= target) { current = target; clearInterval(timer); }
                        el.textContent = current.toLocaleString();
                    }, 25);
                }
            }
        }

        // ── SA Card Interaction ──
        var saCard = document.querySelector('.sa-card');
        if (saCard) {
            var userId = saCard.dataset.userId;
            function handleActivate() {
                if (userId) sendMessage(userId);
            }
            saCard.addEventListener('click', handleActivate);
            saCard.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    handleActivate();
                }
            });
        }

        // ── Ripple on Tap ──
        document.querySelectorAll('.rank-card, .sa-stat-card').forEach(function(el) {
            el.addEventListener('click', function(e) {
                var r = document.createElement('div');
                var rect = this.getBoundingClientRect();
                var sz = Math.max(rect.width, rect.height) * 2.5;
                Object.assign(r.style, {
                    position: 'absolute', width: sz + 'px', height: sz + 'px', borderRadius: '50%',
                    background: 'radial-gradient(circle,rgba(139,92,246,0.2),transparent)',
                    left: (e.clientX - rect.left - sz / 2) + 'px',
                    top: (e.clientY - rect.top - sz / 2) + 'px',
                    transform: 'scale(0)', animation: 'rippleFx 0.6s ease-out',
                    pointerEvents: 'none', zIndex: '10'
                });
                this.appendChild(r);
                setTimeout(function() { r.remove(); }, 600);
            });
        });

        // ── Scroll Reveal ──
        if ('IntersectionObserver' in window) {
            var obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.style.animationPlayState = 'running';
                        obs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.ranking-section').forEach(function(s) { obs.observe(s); });
        }

        // ── AJAX Lazy Load Rankings ──
        var statsUrl = "{{ url('country/' . $country->id . '/stats') }}";
        var badges = ['badge-gold', 'badge-silver', 'badge-bronze'];
        var defaultAvatar = "{{ asset('images/businessman-icon.jpg') }}";
        var defaultBdBg = 'linear-gradient(135deg,rgba(99,102,241,0.3),rgba(168,85,247,0.3))';
        var noDataText = "{{ __('No data available yet') }}";
        var errorText = "{{ __('Could not load data. Please refresh the page.') }}";
        var retryText = "{{ __('Retry') }}";

        fetch(statsUrl, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var sectionKeys = ['topRooms', 'topSenders', 'topReceivers', 'topAgencies', 'topChargeAgencies', 'topBds', 'topGamers'];
            sectionKeys.forEach(function(key, si) {
                var container = document.getElementById('cards-' + key);
                if (!container) return;
                var items = data[key] || [];
                var unit = container.dataset.unit || '';
                var emoji = container.dataset.emoji || '';
                var rankLabel = container.dataset.rankLabel || 'Rank';

                container.setAttribute('aria-busy', 'false');
                container.removeAttribute('aria-label');
                container.innerHTML = '';

                if (items.length === 0) {
                    container.innerHTML = '<div class="ranking-empty" role="status">' +
                        '<div class="ranking-empty-icon">📭</div>' +
                        '<div class="ranking-empty-text">' + noDataText + '</div>' +
                        '</div>';
                    return;
                }

                items.forEach(function(item, i) {
                    var card = document.createElement('div');
                    card.className = 'rank-card loaded';
                    card.style.animationDelay = (si * 0.08 + i * 0.1) + 's';
                    card.setAttribute('aria-label', (item.name || '-') + ', ' + rankLabel + ' ' + (i + 1));

                    var avatarStyle = (key === 'topBds' && !item.image)
                        ? 'background:' + defaultBdBg
                        : "background-image:url('" + (item.image || defaultAvatar) + "');background-size:cover;background-position:center";

                    var valText = unit ? item.value + ' ' + unit : item.value + (emoji ? ' ' + emoji : '');

                    card.innerHTML =
                        '<div class="rank-badge ' + badges[i] + '" aria-hidden="true">' + (i + 1) + '</div>' +
                        '<div class="rank-avatar" style="' + avatarStyle + '" role="img" aria-label="' + (item.name || '') + '"></div>' +
                        '<div class="rank-name">' + (item.name || '-') + '</div>' +
                        '<div class="rank-val">' + valText + '</div>';

                    container.appendChild(card);
                });
            });
        })
        .catch(function(err) {
            console.error('Stats load error:', err);
            document.querySelectorAll('.ranking-cards').forEach(function(container) {
                container.setAttribute('aria-busy', 'false');
                container.removeAttribute('aria-label');
                container.innerHTML = '<div class="ranking-error" role="alert">' +
                    '<div class="ranking-error-icon">⚠️</div>' +
                    '<div class="ranking-error-text">' + errorText + '</div>' +
                    '<button type="button" class="ranking-retry-btn" onclick="location.reload()">' + retryText + '</button>' +
                    '</div>';
            });
        });
    });
})();
</script>
</body>
</html>
