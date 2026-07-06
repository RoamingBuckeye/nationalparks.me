{{-- Shared shell for the custom HTTP error pages. Fully self-contained (inline
     styles, no bundled assets) so it renders even when Vite/Inertia is down —
     e.g. on a 500. Colors mirror the passport design tokens in
     resources/css/app.css; the serif face falls back to Georgia when the
     self-hosted Fraunces isn't available on a standalone page. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · {{ config('app.name') }}</title>
    <style>
        :root {
            --bg: #f4f5ef;
            --fg: #1e3a2c;
            --card: #fbfbf6;
            --muted: #5c6857;
            --pine: #2f7d46;
            --pine-fg: #f6f7f1;
            --border: #dcded1;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #16211b;
                --fg: #e9ebe1;
                --card: #1c2a22;
                --muted: #9da99a;
                --pine: #4fa96a;
                --pine-fg: #0e1712;
                --border: #2c3e33;
            }
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--bg);
            color: var(--fg);
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .error { width: 100%; max-width: 30rem; text-align: center; }

        .error__brand {
            font-family: Fraunces, Georgia, 'Times New Roman', serif;
            font-weight: 600;
            font-size: 1.05rem;
            letter-spacing: .01em;
            color: var(--pine);
            text-decoration: none;
        }

        .error__card {
            margin-top: 1.5rem;
            padding: 2.5rem 2rem;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: .75rem;
        }

        .error__code {
            margin: 0;
            font-family: Fraunces, Georgia, 'Times New Roman', serif;
            font-weight: 600;
            font-size: 4rem;
            line-height: 1;
            color: var(--pine);
            font-variant-numeric: tabular-nums;
        }

        .error__title {
            margin: .75rem 0 0;
            font-family: Fraunces, Georgia, 'Times New Roman', serif;
            font-weight: 600;
            font-size: 1.5rem;
            color: var(--fg);
            text-wrap: balance;
        }

        .error__message {
            margin: .75rem 0 0;
            color: var(--muted);
            line-height: 1.55;
            text-wrap: pretty;
        }

        .error__home {
            display: inline-block;
            margin-top: 1.75rem;
            padding: .625rem 1.25rem;
            border-radius: .5rem;
            background: var(--pine);
            color: var(--pine-fg);
            font-weight: 500;
            font-size: .95rem;
            text-decoration: none;
        }

        .error__home:hover { opacity: .92; }
        .error__home:focus-visible { outline: 2px solid var(--pine); outline-offset: 2px; }
    </style>
</head>
<body>
    <main class="error">
        <a href="{{ url('/') }}" class="error__brand">NationalParks.me</a>
        <div class="error__card">
            <p class="error__code">@yield('code')</p>
            <h1 class="error__title">@yield('title')</h1>
            <p class="error__message">@yield('message')</p>
            <a href="{{ url('/') }}" class="error__home">Back to home</a>
        </div>
    </main>
</body>
</html>
