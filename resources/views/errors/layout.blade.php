<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0f172a">
        <title>@yield('code') · {{ trans('ui.brand.name') }}</title>
        <style>
            * { box-sizing: border-box; }
            body {
                min-height: 100vh;
                margin: 0;
                display: grid;
                place-items: center;
                overflow: hidden;
                color: #e2e8f0;
                background:
                    radial-gradient(circle at 18% 18%, rgba(37, 99, 235, .26), transparent 32rem),
                    radial-gradient(circle at 82% 82%, rgba(14, 165, 233, .16), transparent 30rem),
                    #08111f;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            }
            body::before {
                position: fixed;
                inset: 0;
                content: "";
                opacity: .16;
                background-image:
                    linear-gradient(rgba(148, 163, 184, .22) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(148, 163, 184, .22) 1px, transparent 1px);
                background-size: 38px 38px;
                mask-image: linear-gradient(to bottom, black, transparent 90%);
            }
            main {
                position: relative;
                width: min(92vw, 760px);
                padding: clamp(2rem, 6vw, 4.5rem);
                overflow: hidden;
                border: 1px solid rgba(148, 163, 184, .2);
                border-radius: 28px;
                background: rgba(15, 23, 42, .78);
                box-shadow: 0 32px 90px rgba(0, 0, 0, .45);
                backdrop-filter: blur(20px);
            }
            .brand {
                display: flex;
                align-items: center;
                gap: .75rem;
                color: #bfdbfe;
                font-size: .72rem;
                font-weight: 800;
                letter-spacing: .18em;
                text-transform: uppercase;
            }
            .book {
                position: relative;
                width: 34px;
                height: 28px;
                perspective: 100px;
            }
            .book::before, .book::after {
                position: absolute;
                inset: 2px 50% 2px 0;
                content: "";
                border: 2px solid #60a5fa;
                border-radius: 4px 1px 1px 4px;
                background: rgba(37, 99, 235, .2);
                transform: skewY(8deg);
            }
            .book::after {
                inset: 2px 0 2px 50%;
                border-radius: 1px 4px 4px 1px;
                transform: skewY(-8deg);
            }
            .code {
                margin: 2.4rem 0 .2rem;
                color: transparent;
                background: linear-gradient(135deg, #fff, #60a5fa 64%, #38bdf8);
                background-clip: text;
                font: 800 clamp(5.2rem, 18vw, 10rem)/.82 Georgia, serif;
                letter-spacing: -.08em;
            }
            h1 {
                max-width: 600px;
                margin: 1.5rem 0 .8rem;
                color: #f8fafc;
                font: 700 clamp(1.5rem, 4vw, 2.4rem)/1.15 Georgia, serif;
            }
            p {
                max-width: 570px;
                margin: 0;
                color: #94a3b8;
                font-size: 1rem;
                line-height: 1.75;
            }
            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: .8rem;
                margin-top: 2rem;
            }
            a, button {
                min-height: 46px;
                padding: .8rem 1.2rem;
                border: 1px solid rgba(148, 163, 184, .22);
                border-radius: 13px;
                color: #dbeafe;
                background: rgba(30, 41, 59, .72);
                font: inherit;
                font-size: .9rem;
                font-weight: 750;
                text-decoration: none;
                cursor: pointer;
                transition: transform .2s ease, border-color .2s ease, background .2s ease;
            }
            a {
                border-color: #2563eb;
                color: #fff;
                background: #2563eb;
                box-shadow: 0 12px 30px rgba(37, 99, 235, .3);
            }
            a:hover, button:hover { transform: translateY(-2px); border-color: #60a5fa; }
            a:hover { background: #1d4ed8; }
            .glow {
                position: absolute;
                right: -6rem;
                bottom: -8rem;
                width: 20rem;
                height: 20rem;
                border: 1px solid rgba(96, 165, 250, .18);
                border-radius: 50%;
                box-shadow: 0 0 0 3rem rgba(59, 130, 246, .025), 0 0 0 6rem rgba(59, 130, 246, .018);
                pointer-events: none;
            }
            @media (max-width: 520px) {
                main { padding: 1.7rem; border-radius: 22px; }
                .actions { display: grid; }
                a, button { width: 100%; text-align: center; }
            }
        </style>
    </head>
    <body>
        <main>
            <div class="brand"><span class="book" aria-hidden="true"></span>{{ trans('ui.brand.name') }}</div>
            <div class="code">@yield('code')</div>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <div class="actions">
                <a href="{{ route('home') }}">{{ trans('ui.http_errors.home') }}</a>
                <button type="button" onclick="history.back()">{{ trans('ui.http_errors.back') }}</button>
            </div>
            <span class="glow" aria-hidden="true"></span>
        </main>
    </body>
</html>
